<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service;

use App\Traits\HasLogger;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class BaseCrawler
 * @package App\Service\Crawler
 */
abstract class AbstractCrawler extends HttpClient implements CrawlerInterface
{
    use HasLogger;

    protected $indexUrl = '';

    /**
     * @param null $offset
     * @param null $limit
     * @return array
     */
    public function getAllDetail($offset = null, $limit = null)
    {
        $detailLinks = $this->getAllDetailLinks();

        if ($offset || $limit) {
            $detailLinks = array_splice($detailLinks, $offset ?? 0, $limit);
        }

        $details = [];

        foreach ($detailLinks as $detailLink) {
            $details[] = $this->getDetail($detailLink);
        }

        return $details;
    }

    /**
     * @param null $callbackPagesCount
     * @param null $callback
     * @return array
     */
    public function getAllDetailLinks($callbackPagesCount = null, $callback = null)
    {
        $pages      = $this->getIndexPages();
        $totalLinks = [];

        if (is_callable($callbackPagesCount)) {
            call_user_func($callbackPagesCount, $pages);
        }

        /**
         * @TODO Support callback to skip a paging
         */
        for ($page = 1; $page <= $pages; $page++) {
            $links = $this->getIndexDetailLinks($this->getIndexUrl($page));

            if (is_callable($callback)) {
                call_user_func($callback, $links);
            }

            $totalLinks = array_merge($totalLinks, $this->getIndexDetailLinks($this->getIndexUrl($page)));
        }

        return $totalLinks;
    }

    /**
     * Return index URL based on page
     *
     * @param mixed $page
     * @return string
     */
    abstract protected function getIndexUrl($page = null);

    /**
     * @param string $url
     * @param string $filter
     * @return array|boolean
     * @throws GuzzleException
     */
    protected function extractLinks($url, $filter)
    {
        $crawler = $this->getCrawler('GET', $url);

        if (!$crawler) {
            return false;
        }

        $items = [];

        $nodes = $crawler->filter($filter);

        if ($nodes->count() === 0) {
            return false;
        }

        foreach ($nodes as $item) {
            $items [] = $item->childNodes[1]->getAttribute('href');
        }

        return $items;
    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @return boolean|Crawler
     * @throws GuzzleException
     */
    protected function getCrawler($method, $uri, array $options = [])
    {
        $response = $this->request($method, $uri, $options);

        if (!$response) {
            return false;
        }

        return new Crawler($response);
    }

    /**
     * @param array $fields
     * @param stdClass $item
     * @return stdClass
     */
    protected function assignFields($fields, $item)
    {
        if (empty($fields)) {
            return $item;
        }

        // Assign fields to object
        foreach ($fields as $field) {
            if (!$field) {
                continue;
            }
            foreach ($field as $key => $value) {
                if (empty($value)) {
                    $item->{$key} = null;
                    continue;
                }

                if (is_array($value)) {
                    $item->{$key} = $value;
                    continue;
                }

                $item->{$key} = trim($value);
            }
        }

        return $item;
    }
}
