<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use XGallery\Webservices\Restful;

/**
 * Class Nct
 * @package XGallery\Webservices\Services
 */
class Nct extends Restful
{
    private $endpoint = 'https://www.nhaccuatui.com';

    /**
     * @param $conditions
     * @return array|bool
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function search($conditions)
    {
        $url      = $this->endpoint.'/tim-nang-cao?'.http_build_query($conditions);
        $response = $this->fetch('GET', $url);

        if (!$response) {
            return false;
        }

        $crawler = new Crawler($response);

        $uri = $crawler->filter('div.box_pageview a')->last()->attr('href');
        parse_str(parse_url($uri)['query'], $queries);
        $songs = $this->getSongsFromSearchView('', $response);

        for ($index = 2; $index <= (int)$queries['page']; $index++) {
            $songs = array_merge($songs, $this->getSongsFromSearchView($url.'&page='.$index));
        }

        return [
            'pages' => (int)$queries['page'],
            'songs' => $songs,
        ];
    }

    /**
     * @param string $url
     * @param string $html
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getSongsFromSearchView($url = '', $html = '')
    {
        $songs    = array();
        $response = false;

        if ($url) {
            $response = $this->fetch('GET', $url);
        } elseif ($html) {
            $response = $html;
        }

        if (!$response) {
            return [];
        }

        $crawler = new Crawler($response);

        foreach ($crawler->filter('ul.search_returns_list li.list_song div.item_content a.name_song') as $node) {
            $song['name'] = $node->nodeValue;
            $song['href'] = $node->getAttribute('href');
            $songs[]      = $song;
        }

        return $songs;
    }

    public function getSong($url)
    {
        $response = $this->fetch('GET', $url);

        if (!$response) {
            return false;
        }

        $start     = strpos($response, 'https://www.nhaccuatui.com/flash/xml?html5=true&key1=');
        $end       = strpos($response, '"', $start);
        $flashLink = substr($response, $start, $end - $start);

        $response = $this->fetch('GET', $flashLink);

        if (!$response) {
            return false;
        }

        $xml = simplexml_load_string(trim($response));

        return [
            'title' => (string)$xml->track->title,
            'creator' => (string)$xml->track->creator,
            'download' => (string)$xml->track->location,
        ];
    }
}