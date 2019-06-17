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
 * Class YoutubeCrawler
 * @package App\Service\Crawler
 */
class YoutubeCrawler extends HttpClient
{
    /**
     * @param $url
     * @throws GuzzleException
     */
    public function getItemDetail($url)
    {
        $crawler = $this->request('GET', $url);
        $html    = strip_tags($crawler->html());

        $data = explode('&amp;', urldecode(urldecode($html)));

        var_dump($data);
        exit;
        $fields = [];
        foreach ($data as $item) {
            $item = explode('=', $item);

            if ($item[0] === 'player_response') {
                //$item[1] = json_decode($item[1]);
            }

            $fields[$item[0]] = $item[1];
        }

        var_dump($fields);
        exit;
    }
}
