<?php

namespace App\Command;

use App\Entity\Event;
use App\Repository\EventRepository;
use Bakame\HtmlTable\Parser;
use Doctrine\ORM\EntityManagerInterface;
use Survos\Scraper\Service\ScraperService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\ConfigureWithAttributes;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('app:rschools', 'Scrape the rschools pages')]
final class AppRschoolsCommand extends InvokableServiceCommand
{
    use ConfigureWithAttributes;
    use RunsCommands;
    use RunsProcesses;

    const BASE_URL = 'https://rappahannockcountyhs.rschoolteams.com/';

    private array $links = [];
    public function __construct(
        private ScraperService $scraperService,
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private array $existing = []

    )
    {
        parent::__construct();
    }

    public function __invoke(

IO $io,
): void {

        $this->loadExisting();
        $html = $this->scraperService->fetchUrl(self::BASE_URL)['content'];
        $crawler = new Crawler($html, self::BASE_URL);

        $links = [];
        $crawler->filter('.section_subitem a')->each(function(Crawler $node, $j) use ($links) {
            // Print the text content of the column
            $this->addTopPage($node->attr('href'), $node->text());
        });
        dd($this->links);

        $io->success('app:rschools success.');
    }

    private function addTopPage($url, $text)
    {
        // get the Quick Links
        $page = $this->scraperService->fetchUrl(self::BASE_URL . $url)['content'];
        $crawler = new Crawler($page, self::BASE_URL);
        $quickLinksHtml = $crawler->filter('.grid-stack')->first();
        $quickLinksHtml->filter('.nav-list')->each(function(Crawler $node, $j) use ($url, $text) {
            // Print the text content of the column
            $node->filter('li a')->each(
                fn(Crawler $child) =>
                    $this->addPage($child->attr('href'), $child->text(), $text)
            );
        });
    }
    private function addPage($url, $section, $sport)
    {
        $page = $this->scraperService->fetchUrl(self::BASE_URL  . $url)['content'];
        $crawler = new Crawler($page, self::BASE_URL);
        $tableNode = $crawler->filter('.tbl_score')->first();
        $parser = Parser::new();
        try {
            $table = $parser->parseHtml($tableHtml = $tableNode->outerHtml());
        } catch (\Exception $exception) {
            return;
        }
        foreach ($table->getIterator() as $row) {
            $id = $row[''];
            $dateTimeString = $row['Date'] . ' ' .  $row['Time'];
            try {
                $dt = new \DateTime($dateTimeString);
            } catch (\Exception $exception) {
                $dt = new \DateTime($row['Date']);
            }

            if (!$event = $this->existing[$id]??null) {
                $event = new Event($id);
                $this->entityManager->persist($event);
                $this->existing[$event->getId()] = $event;

            }
            $event
                ->setSport($sport)
                ->setSection($section)
                ->setEventDate($dt)
                ->setType($row['Event'])
                ->setOpponent($row['Opponent'])
                ->setLocation($row['Location'])
                ->setScore($row['Score'])
                ->setSummary($row['Game Summary'])
                ;
        }
        $this->entityManager->flush();

        $this->io()->warning($url . ' ' . $section);

    }

    private function loadExisting()
    {
        foreach ($this->eventRepository->findAll() as $event) {
            $this->existing[$event->getId()] = $event;
        }


    }

}
