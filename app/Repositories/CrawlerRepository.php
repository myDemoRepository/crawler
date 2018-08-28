<?php

namespace App\Repositories;

use App\Interfaces\ICrawler;
use ArrayIterator;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerRepository implements ICrawler
{
    /**
     * Url protocol first part symbols
     */
    const URL_ANY_PROTOCOL_FIRST_PART = 'http';

    /**
     * Url separator
     */
    const URL_SEPARATOR = '/';

    /**
     * Url http or http protocol url identifier
     */
    const URL_ANY_PROTOCOL_IDENTIFIER = '//';

    /**
     * Link anchor symbol
     */
    const LINK_ANCHOR_SYMBOL = '#';

    /**
     * Link javascript custom string
     */
    const LINK_JAVASCRIPT_CUSTOM_STRING = 'javascript:';

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var int
     */
    protected $depth;

    /**
     * @var int
     */
    protected $maxPages;

    /**
     * @var ParserRepository $parserRepository
     */
    protected $parserRepository;

    /**
     * @var array
     */
    protected $parseResults = [];

    /**
     * @var int Count current processed page
     */
    protected $processedPages = 0;

    /**
     * CrawlerRepository constructor.
     */
    public function __construct()
    {
        $this->parserRepository = app(ParserRepository::class);
    }

    /**
     * @param array $params
     */
    public function setup(array $params)
    {
        if ($this->validateParams($params)) {
            $this->setDomain($params['domain']);
            $this->setDepth($params['depth']);
            $this->setMaxPages($params['max_pages']);
        } else {
            die('bad params');
        }
    }

    /**
     * Run crawler
     */
    public function run()
    {
        $this->parsePage($this->getDomain(), $this->getDepth());
        $this->saveResults();
        echo 'done';
    }

    /**
     * Save parse results
     */
    protected function saveResults()
    {
        $results = $this->getParseResults();
        $iterator = new ArrayIterator($results);

        foreach ($iterator as $value) {
            $this->parserRepository->saveData($value);
        }
    }

    /**
     * Parse page
     *
     * @param string $url
     * @param int $depth
     */
    protected function parsePage(string $url, int $depth)
    {
        if ($depth > 0 && $this->getProcessedPages() < $this->getMaxPages()) {
            $timeStart = microtime(1);
            try {
                $html = file_get_contents($url);
                $crawler = new Crawler($html);
                $links = $this->getPageLinks($crawler);
                $pageAllImgList = $this->getPageImgList($crawler);
                $pageTimeProcessing = microtime(1) - $timeStart;

                $links = $this->filterLinksList($links);
                $pageFilteredImgTagCount = count($this->filterImgList($pageAllImgList));

                $iterator = new ArrayIterator($links);

                $this->setProcessedPages($this->getProcessedPages() + 1);

                foreach ($iterator as $link) {
                    if ($this->getProcessedPages() < $this->getMaxPages()) {
                        $this->parsePage($link, $depth - 1);
                    }
                }

                $parseResults = $this->getParseResults();
                $parseResults[] = [
                    'url' => $url,
                    'img_tag_count' => $pageFilteredImgTagCount,
                    'page_processing_time' => $pageTimeProcessing,
                ];
                $this->setParseResults($parseResults);


            } catch (\Exception $e) {
                echo 'skip bad link - ' . $url . PHP_EOL;
            }
        }
    }

    /**
     * @return int
     */
    public function getProcessedPages(): int
    {
        return $this->processedPages;
    }

    /**
     * @param int $processedPages
     */
    public function setProcessedPages(int $processedPages)
    {
        $this->processedPages = $processedPages;
    }

    /**
     * Filter img list, remove img from another domain or self sub-domain
     *
     * @param array $imgList
     *
     * @return array
     */
    protected function filterImgList(array $imgList): array
    {
        $context = $this;
        $collection = collect($imgList);

        $filteredList = $collection->filter(function ($value) use ($context) {
            $res = false;
            if ($value) {
                $res = $context->isSrcValid($value);
            }
            return $res;
        });

        return $filteredList->all();
    }

    /**
     * Is valid image src, allow relative and absolute for this domain source links
     *
     * @param string $src
     * @return bool
     */
    protected function isSrcValid(string $src): bool
    {
        $result = true;
        $domain = $this->getDomain();

        if (mb_strpos($src, self::URL_ANY_PROTOCOL_FIRST_PART) !== false) {
            if (mb_strpos($src, $domain) !== 0) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get page img list
     *
     * @param Crawler $crawler
     *
     * @return array
     */
    protected function getPageImgList(Crawler $crawler): array
    {
        $context = $this;
        $imgList = $crawler->filter('img')
            ->each(function (Crawler $node, $i) use ($context) {
                return $node->attr('src');
            });

        return $imgList;
    }

    /**
     * Get page links
     *
     * @param Crawler $crawler
     *
     * @return array
     */
    public function getPageLinks(Crawler $crawler): array
    {
        $linksList = $crawler->filter('a')
            ->each(function (Crawler $node, $i) {
                $result = $node->attr('href');
            return $result;
        });

        return $linksList;
    }

    /**
     * Filter link list to remove not valid links
     *
     * @param array $links
     *
     * @return array
     */
    protected function filterLinksList(array $links): array
    {
        $collection = collect($links);

        $collection = $collection->map(function ($value) {
            return trim($value, self::URL_SEPARATOR);
        });

        $unique = $collection->unique();
        $uniqueLinks = $unique->values();

        $context = $this;
        $result = $uniqueLinks->filter(function ($value) use ($context) {
            $res = false;
            if ($value) {
                $res = $context->isLinkValid($value);
            }
            return $res;
        })->map(function ($value) use ($context) {
            $link = $value;
            if (mb_strpos($value, $context->getDomain()) === false) {
                $link = trim($context->getDomain(), self::URL_SEPARATOR) . self::URL_SEPARATOR . $value;
            }

            return $link;
        });

        return $result->all();
    }

    /**
     * Is link valid
     *
     * @param string $link
     *
     * @return bool
     */
    protected function isLinkValid(string $link): bool
    {
        $result = false;

        $domain = $this->getDomain();
        $domain = trim($domain, self::URL_SEPARATOR);

        if (mb_strpos($link, $domain) !== false) {
            $result = true;
        }

        if (mb_strpos($link, self::URL_ANY_PROTOCOL_IDENTIFIER) === false) {
            $result = true;
        }

        if (mb_strpos($link, self::LINK_ANCHOR_SYMBOL) !== false) {
            $result = false;
        }

        if (mb_strpos($link, self::LINK_JAVASCRIPT_CUSTOM_STRING) !== false) {
            $result = false;
        }

        if ($this->isLinkImage($link)) {
            $result = false;
        }

        return $result;
    }

    /**
     * Is link an image
     *
     * @param string $url
     * @return bool
     */
    protected function isLinkImage(string $url): bool
    {
        $result = false;
        $imgExtensions = ["gif", "jpg", "jpeg", "png", "tiff", "tif"];
        $urlExtension = pathinfo($url, PATHINFO_EXTENSION);
        if (in_array($urlExtension, $imgExtensions)) {
            $result = true;
        }
        /* TODO additional checking is to use headers (get_headers($url)) to determine on response content-type image or not */

        return $result;
    }

    /**
     * Validate params
     *
     * @param array $params
     *
     * @return bool
     */
    protected function validateParams($params): bool
    {
        $result = Validator::make(
            $params,
            [
                'domain' => 'required|url',
                'depth' => 'required|integer',
                'max_pages' => 'required|integer',
            ]
        );

        return $result->passes();
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth(int $depth)
    {
        $this->depth = $depth;
    }

    /**
     * @return int
     */
    public function getMaxPages(): int
    {
        return $this->maxPages;
    }

    /**
     * @param int $maxPages
     */
    public function setMaxPages(int $maxPages)
    {
        $this->maxPages = $maxPages;
    }

    /**
     * @return array
     */
    public function getParseResults(): array
    {
        return $this->parseResults;
    }

    /**
     * @param array $parseResults
     */
    public function setParseResults(array $parseResults)
    {
        $this->parseResults = $parseResults;
    }
}