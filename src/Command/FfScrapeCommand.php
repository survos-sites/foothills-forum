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
                $this->logger->warning("scraping " . $uri);
                $stories = $this->scraperService->fetchUrlUsingCache($uri, asData: false);
                $storyCrawler = new Crawler($stories['content']);
                $storyCrawler->filter('.meta')
                    ->each(function (Crawler $node) use ($key, $stories) {
                        try {
                            $headline = $node->filter('a')->text();
                        } catch (\Exception $exception) {
                            $this->logger->error("No a links in node");
                            return;
                            dd($node->html());
                        }
//                            $date = $node->filter('time')->text();
                        try {
                            $byline = $node->filter('.tnt-byline')?->text();
                            $byline = u($byline)->after('By ')->toString();
                        } catch (\Exception $exception) {
                            $this->logger->error("No a links in node");
                            $byline = 'missing tnt-byline';
                        }
                            try {
                                $articleUri = $node->filter('a')->link()->getUri();
                            } catch (\Exception $exception) {
//                            dump($node->html());
                                $this->logger->error("No a links in node ");
//                                dd($stories['content']);
                                return;
                            }
                            $this->logger->warning("parsing " . $articleUri);
                            preg_match('|/news/(.*?)/ar|', $articleUri, $m);
                            $mm =  explode('/', $articleUri);
                            $slug = $mm[5];

//                            $slug = str_replace('project/', '', $articleUri);
                            if (!$article = $this->articles[$slug]??null) {
                                $article = (new Article())
                                    ->setSlug($slug);
                                $this->entityManager->persist($article);
                            }
                            $article
                                ->setSection($key)
                                ->setUrl($articleUri)
                                ->setHeadline($headline)
                                ->setByline($byline);

//                            $this->articles[$key][] = [
//                                'key' => $key,
//                                'date' => $date,
//                                'headline' => $headline,
//                                'url' => $uri,
//                                'byline' => $byline
//                            ];
                        try {
                        } catch (\Exception $exception) {
                            $this->logger->error($exception->getMessage());
                        }
                    });
            });
        $this->entityManager->flush();

        $io->success('ff:scrape success.');
    }

}
