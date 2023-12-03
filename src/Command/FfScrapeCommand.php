<?php

namespace App\Command;

use App\Entity\Article;
use App\Repository\ArticleRepository;
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
        private array $articles = [],
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
        $html = $this->scraperService->fetchUrlUsingCache('https://foothills-forum.org/reporting-projects/?', asData: false);
        $crawler = new Crawler($html['content']);
        foreach ($this->articleRepository->findAll() as $article) {
            $articles[$article->getSlug()] = $article;
        }

        $crawler
            ->filter('.type-project a')
            ->each(function (Crawler $node, $i): void {
                $link = $node->link();
                $uri = $link->getUri();
                // the key is the path on rappnews
                $path =  trim(parse_url($uri, PHP_URL_PATH), '/');
                $key = str_replace('project/', '', $path);

                $stories = $this->scraperService->fetchUrlUsingCache($uri, asData: false);
                $storyCrawler = new Crawler($stories['content']);
                $storyCrawler->filter('.meta')
                    ->each(function (Crawler $node) use ($key) {
                        try {
                            $headline = $node->filter('a')->text();
                            $date = $node->filter('time')->text();
                            $byline = $node->filter('.tnt-byline')?->text();
                            $byline = u($byline)->after('By ')->toString();
                            $uri = $node->filter('a')->link()->getUri();
                            preg_match('|/news/(.*?)/ar|', $uri, $m);
                            $mm =  explode('/', $uri);
                            $slug = $mm[5];
                            $slug = str_replace('project/', '', $uri);
                            if (!$article = $this->articles[$slug]??null) {
                                $article = (new Article())
                                    ->setSlug($slug);
                                $this->entityManager->persist($article);
                            }
                            $article
                                ->setSection($key)
                                ->setUrl($uri)
                                ->setHeadline($headline)
                                ->setByline($byline);

//                            $this->articles[$key][] = [
//                                'key' => $key,
//                                'date' => $date,
//                                'headline' => $headline,
//                                'url' => $uri,
//                                'byline' => $byline
//                            ];
                        } catch (\Exception $exception) {
                            $this->logger->error($exception->getMessage());
                        }
                    });
            });
        $this->entityManager->flush();

        $io->success('ff:scrape success.');
    }

}
