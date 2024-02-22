<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\School;
use App\Entity\Sport;
use App\Entity\Team;
use App\Repository\EventRepository;
use App\Repository\SchoolRepository;
use App\Repository\SportRepository;
use App\Repository\TeamRepository;
use Bakame\HtmlTable\Parser;
use Doctrine\ORM\EntityManagerInterface;
use Survos\Scraper\Service\ScraperService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\ConfigureWithAttributes;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;
use function Symfony\Component\String\u;

#[AsCommand('app:rschools', 'Scrape the rschools pages')]
final class AppRschoolsCommand extends InvokableServiceCommand
{
    use ConfigureWithAttributes;
    use RunsCommands;
    use RunsProcesses;

    const BASE_URL = 'https://rappahannockcountyhs.rschoolteams.com/';

    private array $links = [];
    public function __construct(
        private ScraperService         $scraperService,
        private EntityManagerInterface $entityManager,
        private EventRepository        $eventRepository,
        private SportRepository        $sportRepository,
        private TeamRepository         $teamRepository,
        private SchoolRepository       $schoolRepository,
        private SluggerInterface       $asciiSlugger,
        private array                  $existing = []

    )
    {
        parent::__construct();
    }

    public function __invoke(
        IO $io,
        #[Option(description: 'reset the database')] bool $reset = false,
): void {
        $this->loadExisting($reset);
        $school = $this->getEntity(School::class,  'rappahannockcountyhs');
        $html = $this->scraperService->fetchUrl(self::BASE_URL)['content'];
        $crawler = new Crawler($html, self::BASE_URL);

        // highest level is the sports dropdown under 'Athletics'
        $crawler->filter('.section_subitem a')->each(fn(Crawler $node) =>
            // Print the text content of the column
            $this->addTopPage($school, $node->attr('href'), $node->text())
        );

        foreach ($this->existing as $class=>$entities) {
            $io->success($class . ": " . count($entities));
        }

    }

    private function addTopPage(School $school, $url, $sportName): Sport
    {
        $sport = $this->getEntity(Sport::class, $sportName);
        $school->addSport($sport);
        // get the Quick Links, Varsity, JV
        $page = $this->scraperService->fetchUrl(self::BASE_URL . $url)['content'];
        $crawler = new Crawler($page, self::BASE_URL);
        $quickLinksHtml = $crawler->filter('.grid-stack')->first();
        $quickLinksHtml->filter('.nav-list')->each(function(Crawler $node, $j) use ($sport, $url, $sportName) {
            // Print the text content of the column
            $node->filter('li a')->each(
                fn(Crawler $sectionNode) =>
                    $this->addPage($sectionNode->attr('href'), $sectionNode->text(), $sport)
            );
        });
        return $sport;
    }

    private function addPage($url, $sectionName, Sport $sport)
    {
        $teamName = $sport->getSchool()->getCode() . '-' . $sport->getName() . ' ' . $sectionName;
        $team = $this->getEntity(Team::class, $teamName);
        $pageId = u($url)->after('/page/')->toString();
        $team->setRSchoolId((int)$pageId);
        $team->setSection($sectionName);
        $sport->getSchool()->addTeam($team);
        $sport->addTeam($team);
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
            if (empty($row['Date'])) {
                continue; // e.g. https://rappahannockcountyhs.rschoolteams.com/page/5416
            }
            $dateTimeString = $row['Date'] . ' ' .  $row['Time'];
            try {
                $dt = new \DateTime($dateTimeString);
            } catch (\Exception $exception) {
                $dt = new \DateTime($row['Date']);
            }

            $current_date = new \DateTime('yesterday');

            if ($dt < $current_date)
            {
                continue;
            }


            // only load if > now


            $locationName = $row['Location'] . ' ' . $team->getSport()->getName();
            $location = $this->getEntity(Location::class, $locationName);
            assert($location->getCode(), $locationName);

            assert($team->getSection());
            $event = $this->getEntity(Event::class, 'Event '.$id);
            $event
                ->setRSchoolId($id) // since we have a unique id, but really rSchoolId
                ->setSport($sport)
                ->setSection($team->getSection())
                ->setEventDate($dt)
                ->setType($row['Event'])
                ->setOpponent($row['Opponent'])
                ->setLocation($location)
                ->setScore($row['Score'])
                ->setSummary($row['Game Summary'])
                ;
            $team->addEvent($event);
        }
        $this->entityManager->flush();

        $this->io()->warning($url . ' ' . $teamName);

    }

    private function loadExisting(bool $reset = false)
    {
        foreach ([Event::class, Team::class,  Sport::class, School::class, Location::class, ] as $entityClass) {
            $repo = $this->entityManager->getRepository($entityClass);
            foreach ($repo->findAll() as $entity) {
                if ($reset) {
                    $this->entityManager->remove($entity);
                } else {
                    $this->existing[$entityClass][$entity->getCode()] = $entity;
                }
            }
        }
    }

    public function getEntity(string $entityClass, string $text, ?string $code=null): Team|School|Sport|Event|Location
    {
        $code = $code ?: $this->asciiSlugger->slug($text)->lower()->toString();
        if (!$entity = $this->existing[$entityClass][$code]??null) {
            $entity = (new $entityClass($code))->setName($text);
            $this->existing[$entityClass][$code] = $entity;
            $this->entityManager->persist($entity);
        }
        return $entity;
    }

}
