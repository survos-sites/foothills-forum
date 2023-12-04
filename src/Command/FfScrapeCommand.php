<?php

namespace App\Command;

use App\Entity\Article;
use App\Entity\Author;
use App\Repository\ArticleRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpseclib3\File\ASN1\Maps\UniqueIdentifier;
use Psr\Log\LoggerInterface;
use Survos\Scraper\Service\ScraperService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\ConfigureWithAttributes;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;
use function Symfony\Component\String\u;

#[AsCommand('ff:scrape', 'Scrape the foothills forum articles')]
final class FfScrapeCommand extends InvokableServiceCommand
{
    use ConfigureWithAttributes;
    use RunsCommands;
    use RunsProcesses;

    public function __construct(
        private ScraperService $scraperService,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ArticleRepository $articleRepository,
        private AuthorRepository $authorRepository,
        private array $articles = [],
        private array $authors = [],
        string $name = null)
    {
        parent::__construct($name);
    }

    public function __invoke(
        IO   $io,

        #[Option(description: 'reset the scrape cache')]
        bool $reset = false,
    ): void
    {

        foreach ($this->articleRepository->findAll() as $article) {
            $this->articles[$article->getUuid()] = $article;
        }

        foreach ($this->authorRepository->findAll() as $author) {
            $this->authors[$author->getUuid()] = $author;
        }

//        https://www.rappnews.com/search/?f=html&q=%22foothills+forum%22&s=start_time&sd=desc&l=10&t=article&nsa=eedition&app%5B0%5D=editorial&o=100
//        $url = https://www.rappnews.com/search/?f=html&q=%22foothills+forum%22&s=start_time&sd=desc&l=10&t=article&nsa=eedition&f=json
        $perPage = 10;
        $startingAt=0;
        $base = 'https://www.rappnews.com/search/';
        do {
            $parameters = [
                'f' => 'json',
                's' => 'start_time',
                'nsa' => 'eedition',
                'q' => 'foothills forum',
                't' => 'article',
                'l' => 100,
                'o' => $startingAt
            ];

//        $base = 'https://www.rappnews.com/search/?f=html&q=%22foothills+forum%22&s=start_time&sd=desc&t=article&nsa=eedition&app%5B0%5D=editorial';
//        $url = $base . sprintf("&l=%d&o=%d", $perPage, $startingAt);
            $data = $this->scraperService->fetchUrlUsingCache($base, $parameters);
            $total = $data['data']['total'];
            $next = $data['data']['next'];
            foreach ($data['data']['rows'] as $row) {
                $uuid = $row['uuid'];
                if (!$article = $articles[$uuid]??null) {
                    $article = (new Article())
                        ->setUuid($uuid);
                    $this->entityManager->persist($article);
                    $this->articles[$uuid] = $article; // in case of dups.
                }
                $article
                    ->setHeadline($row['title'])
                    ->setSubheadline($row['subheadline'])
                    ->setByline($row['byline'])
                    ->setUrl($row['url'])
                    ->setSections($row['sections'])
                ;
                foreach ($row['authors'] as $author) {
                    $this->addAuthor($author, $article);
                }
            }
            $this->entityManager->flush();
            $startingAt = $next;
        } while ($next);

        $io->success('ff:scrape success.');
    }

    private function addAuthor(array $authorData, Article $article)
    {
        $uuid = $authorData['uuid'];
        if (!$author = $this->authors[$uuid]??null) {
            $author = (new Author())
                ->setUuid($uuid);
            $this->entityManager->persist($author);
        }
        $author
            ->setAvatar($authorData['avatar'])
            ->setProfile($authorData['profile'])
            ->setFullName($authorData['full_name']);
        $author->addArticle($article);
//        dd($authorData, $article);
    }

}
