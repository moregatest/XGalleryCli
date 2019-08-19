<?php

/**
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
use RuntimeException;
use stdClass;

/**
 * Class XCityCrawler
 * @package App\Service\Crawler
 */
final class XCityCrawler extends AbstractCrawler
{
    /**
     * Endpoint
     *
     * @var string
     */
    const IDOL_URL = 'https://xxx.xcity.jp/idol/';

    /**
     * @var string
     */
    protected $indexUrl = '';

    /**
     * @var XCityProfileCrawler
     */
    private $profileCrawler;

    /**
     * XCityCrawler constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->profileCrawler = new XCityProfileCrawler;

        parent::__construct($config);
    }

    /**
     * @param $profile
     */
    public function setProfile($profile)
    {
        $this->indexUrl = self::IDOL_URL . $profile;
    }

    /**
     * @return integer
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexPages()
    {
        if (!$crawler = $this->getCrawler('GET', $this->getIndexUrl(1))) {
            return 1;
        }

        $nodes = $crawler->filter('ul.pageScrl li.next');

        if ($nodes->count() === 0 || $nodes->previousAll()->filter('li a')->count() === 0) {
            return 1;
        }

        return (int)$crawler->filter('ul.pageScrl li.next')->previousAll()->filter('li a')->text();
    }

    /**
     * @param string $page
     * @return string
     * @uses $page </detail/511/>?page=<pageNumber>
     */
    protected function getIndexUrl($page = null)
    {
        return $this->indexUrl . '/?page=' . $page;
    }

    /**
     * @param string $url
     * @return array|boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexDetailLinks($url)
    {
        if (!$crawler = $this->getCrawler('GET', $url)) {
            return false;
        }

        $domElement = $crawler->filter('.x-itemBox .x-itemBox-title a');

        if ($domElement->count() === 0) {
            return false;
        }

        $list = [];

        foreach ($domElement as $domProfile) {
            $list [] = $domProfile->attributes[0]->value;
        }

        return $list;
    }

    /**
     * @param string $url
     * @return boolean|mixed|stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDetail($url)
    {
        if (!$crawler = $this->getCrawler('GET', $url)) {
            return false;
        }

        try {
            $film = new stdClass;

            $film->name = $crawler->filter('#program_detail_title')->text();
            $film->url  = $url;
            $filmXId    = explode('=', $url);
            $film->id   = (int)end($filmXId);

            // Get all fields
            $fields = $crawler->filter('.bodyCol ul li')->each(
                function ($li) {
                    if (strpos($li->text(), '★Favorite') !== false) {
                        return ['favorite' => (int)str_replace('★Favorite', '', $li->text())];
                    }
                    if (strpos($li->text(), 'Sales Date') !== false) {
                        return [
                            'sales_date' => trim(
                                str_replace('/', '-', str_replace('Sales Date', '', $li->text()))
                            ),
                        ];
                    }
                    if (strpos($li->text(), 'Label/Maker') !== false) {
                        return [
                            'label' => $li->filter('#program_detail_maker_name')->text(),
                            'marker' => $li->filter('#program_detail_label_name')->text(),
                        ];
                    }

                    if (strpos($li->text(), 'Genres') !== false) {
                        $genres = $li->filter('a.genre')->each(
                            function ($a) {
                                return trim($a->text());
                            }
                        );

                        return ['genres' => $genres];
                    }

                    if (strpos($li->text(), 'Series') !== false) {
                        return ['series' => str_replace('Series', '', $li->text())];
                    }

                    if (strpos($li->text(), 'Director') !== false) {
                        return ['director' => str_replace('Director', '', $li->text())];
                    }
                    if (strpos($li->text(), 'Item Number') !== false) {
                        return ['item_number' => str_replace('Item Number', '', $li->text())];
                    }

                    if (strpos($li->text(), 'Running Time') !== false) {
                        return [
                            'time' => (int)trim(str_replace(['Running Time', 'min', '.'], ['', '', ''], $li->text())),
                        ];
                    }

                    if (strpos($li->text(), 'Release Date') !== false) {
                        $releaseDate = trim(str_replace('Release Date', '', $li->text()));
                        if (!empty($releaseDate) && strpos($releaseDate, 'undelivered now') === false) {
                            return ['release_date' => str_replace('/', '-', $releaseDate)];
                        }
                    }

                    if (strpos($li->text(), 'Description') !== false) {
                        return ['description' => str_replace('Description', '', $li->text())];
                    }
                }
            );
            $film   = $this->assignFields($fields, $film);
        } catch (RuntimeException $exception) {
            $this->logError($exception->getMessage());

            return false;
        }

        return $film;
    }

    /**
     * @param string $url
     * @return boolean|mixed|stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getProfileDetail($url)
    {
        return $this->profileCrawler->getDetail($url);
    }

    /**
     * @param null $callbackPagesCount
     * @param null $callback
     * @return array
     * @throws GuzzleException
     */
    public function getAllProfileLinks($callbackPagesCount = null, $callback = null)
    {
        return $this->profileCrawler->getAllDetailLinks($callbackPagesCount, $callback);
    }
}
