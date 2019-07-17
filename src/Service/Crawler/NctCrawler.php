<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler;

use App\Service\AbstractCrawler;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;

/**
 * Class Nct
 * @package App\Service\Crawler
 */
class NctCrawler extends AbstractCrawler
{
    /**
     * @var string
     */
    protected $indexUrl = 'https://www.nhaccuatui.com';

    /**
     * This method is not used
     *
     * @return integer|void
     */
    public function getIndexPages()
    {
        return;
    }

    /**
     * Return array of links on index URL
     *
     * @param string $url
     * @return array|void
     */
    public function getIndexDetailLinks($url)
    {
        return;
    }

    /**
     * @param $conditions
     * @return array|boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function search($conditions)
    {
        if (!$crawler = $this->getCrawler('GET', $this->indexUrl . '/tim-nang-cao?' . http_build_query($conditions))) {
            return false;
        }

        $pagesUrl = $crawler->filter('div.box_pageview a')->last()->attr('href');
        parse_str(parse_url($pagesUrl)['query'], $queries);

        $songs = $this->extractSongsInSearchView($crawler);

        for ($page = 2; $page < $queries['page']; $page++) {
            $crawler = $this->getCrawler(
                'GET',
                $this->indexUrl . '/tim-nang-cao?' . http_build_query($conditions) . '&page=' . $page
            );
            $songs   = array_merge($songs, $this->extractSongsInSearchView($crawler));
        }

        return $songs;
    }

    /**
     * @param $crawler
     * @return array
     */
    private function extractSongsInSearchView($crawler)
    {
        $result = [];

        foreach ($crawler->filter('ul.search_returns_list li.list_song div.item_content a.name_song') as $node) {
            $song['title'] = $node->nodeValue;
            $song['href']  = $node->getAttribute('href');
            $result[]      = $song;
        }

        return $result;
    }

    /**
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getTop20()
    {
        $top20 = [
            'https://www.nhaccuatui.com/bai-hat/top-20.nhac-viet.html',
            'https://www.nhaccuatui.com/bai-hat/top-20.au-my.html',
            'https://www.nhaccuatui.com/bai-hat/top-20.nhac-han.html',
        ];

        $songs = [];

        foreach ($top20 as $url) {
            $crawler = $this->getCrawler('GET', $url);

            foreach ($crawler->filter('.box_info_field h3 a') as $index => $node) {
                $songs[$index]['href']  = $node->getAttribute('href');
                $songs[$index]['title'] = $node->nodeValue;
            }
        }

        return $songs;
    }

    /**
     * @param string $url
     * @return array|boolean|mixed
     * @throws InvalidArgumentException
     */
    public function getDetail($url)
    {
        try {
            if (!$crawler = $this->getCrawler('GET', $url)) {
                return false;
            }

            $text = $crawler->text();

            $start = strpos($text, 'https://www.nhaccuatui.com/flash/xml?html5=true&key1=');
            $end   = strpos($text, '"', $start);
            $url   = substr($text, $start, $end - $start);

            $xml = simplexml_load_string($this->get($url));

            return [
                'url' => $url,
                'title' => trim((string)$xml->track->title),
                'creator' => trim((string)$xml->track->creator),
                'download' => trim((string)$xml->track->location),
            ];
        } catch (GuzzleException $exception) {
            $this->logError($exception->getMessage());

            return false;
        }
    }

    /**
     * @param null $page
     * @return string|void
     */
    protected function getIndexUrl($page = null)
    {
        return;
    }
}
