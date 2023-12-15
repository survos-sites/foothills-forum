<?php

declare(strict_types=1);

// src/DataCollector/SeoCollector.php

namespace App\DataCollector;

use App\Twig\Extension\SeoExtension;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

use function Symfony\Component\String\u;

final class SeoCollector extends DataCollector
{
    private const MAX_PANEL_WIDTH = 50;
    private const CLASS_ERROR = 'red';
    private const CLASS_WARNING = 'yellow';
    private const CLASS_OK = 'green';

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $crawler = new Crawler((string) $response->getContent());

        // Title ———————————————————————————————————————————————————————————————
        $titleTag = $crawler->filter('title');
        if ($titleTag->count() > 0) {
            $titleStr = u($titleTag->text());
            $titleSize = $titleStr->length();
            $titleInfo = [
                'value' => $titleStr->wordwrap(self::MAX_PANEL_WIDTH)->toString(),
                'size' => (string) $titleSize,
                'status' => $this->getTitleClass($titleSize),
            ];
            $this->data['title'] = $titleInfo;
        }

        // Description —————————————————————————————————————————————————————————
        $meta = $crawler->filterXPath('//meta[@name="description"]');
        if ($meta->count() > 0) {
            $descriptionStr = u((string) $meta->attr('content'));
            $descriptionLength = $descriptionStr->length();
            $descriptionInfo = [
                'value' => $descriptionStr->wordwrap(self::MAX_PANEL_WIDTH)->toString(),
                'size' => (string) $descriptionLength,
                'status' => $this->getDescriptionClass($descriptionLength),
            ];
            $this->data['description'] = $descriptionInfo;
        }
    }

    private function getTitleClass(int $size): string
    {
        if ($size === 0) {
            return self::CLASS_ERROR;
        }

        return $size >= SeoExtension::MIN_TITLE_LENGTH && $size <= SeoExtension::MAX_TITLE_LENGTH ? self::CLASS_OK : self::CLASS_WARNING;
    }

    private function getDescriptionClass(int $size): string
    {
        if ($size === 0) {
            return self::CLASS_ERROR;
        }

        return $size >= SeoExtension::MIN_DESCRITION_LENGTH && $size <= SeoExtension::MAX_DESCRITION_LENGTH ? self::CLASS_OK : self::CLASS_WARNING;
    }

    /**
     * @return array<string,string>
     */
    public function getTitle(): array
    {
        return $this->data['title'] ?? [];
    }

    /**
     * @return array<string,string>
     */
    public function getDescription(): array
    {
        return $this->data['description'] ?? [];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getName(): string
    {
        return self::class;
    }
}
