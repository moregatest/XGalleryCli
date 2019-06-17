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

use App\Service\HttpClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Nct
 * @package App\Service\Crawler
 */
class NctCrawler extends HttpClient
{
    /**
     * Endpoint
     *
     * @var string
     */
    private $endpoint = 'https://www.nhaccuatui.com';

    /**
     * @param $conditions
     * @return array
     * @throws GuzzleException
     */
    public function search($conditions)
    {
        $crawler = $this->getCrawler('GET', $this->endpoint . '/tim-nang-cao?' . http_build_query($conditions));

        $pagesUrl = $crawler->filter('div.box_pageview a')->last()->attr('href');
        parse_str(parse_url($pagesUrl)['query'], $queries);

        $songs = $this->extractSongsInSearchView($crawler);

        for ($page = 2; $page < $queries['page']; $page++) {
            $crawler = $this->getCrawler(
                'GET',
                $this->endpoint . '/tim-nang-cao?' . http_build_query($conditions) . '&page=' . $page
            );
            $songs   = array_merge($songs, $this->extractSongsInSearchView($crawler));
        }

        return $songs;
    }

    /**
     * @param $url
     * @return array
     * @throws GuzzleException
     */
    public function getTop20($url)
    {
        $crawler = $this->getCrawler('GET', $url);

        $songs = [];

        foreach ($crawler->filter('.box_info_field h3 a') as $index => $node) {
            $songs[$index]['href']  = $node->getAttribute('href');
            $songs[$index]['title'] = $node->nodeValue;
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
     * Get song detail information for downloading
     *
     * @param $url
     * @return array
     */
    public function extractItem($url)
    {
        try {
            $crawler = $this->getCrawler('GET', $url);
            $text    = $crawler->text();

            $start = strpos($text, 'https://www.nhaccuatui.com/flash/xml?html5=true&key1=');
            $end   = strpos($text, '"', $start);
            $url   = substr($text, $start, $end - $start);

            $xml = simplexml_load_string(
                $this->client->request('GET', $url)->getBody()->getContents()
            );

            return [
                'url' => $url,
                'title' => trim((string)$xml->track->title),
                'creator' => trim((string)$xml->track->creator),
                'download' => trim((string)$xml->track->location),
            ];
        } catch (GuzzleException $exception) {
            $this->logError($exception->getMessage());
        }
    }
}
