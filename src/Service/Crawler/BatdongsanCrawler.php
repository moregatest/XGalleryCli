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
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use stdClass;

/**
 * Class BdsCrawler
 * @package App\Service\Crawler
 */
final class BatdongsanCrawler extends AbstractCrawler
{
    /**
     * @var string
     */
    protected $indexUrl = 'https://batdongsan.com.vn/nha-dat-ban';

    /**
     * @return boolean|integer
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexPages()
    {
        if (!$crawler = $this->getCrawler('GET', $this->getIndexUrl(1))) {
            return false;
        }

        $pages = $crawler->filter('.background-pager-right-controls a')->last()->attr('href');
        $pages = explode('/', $pages);
        $page  = str_replace('p', '', end($pages));

        return (int)$page;
    }

    /**
     * @param null $page
     * @return string
     */
    protected function getIndexUrl($page = null)
    {
        return $this->indexUrl . '/p' . $page;
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

        $result = [];

        $nodes = $crawler->filter('.search-productItem .p-title h3 a');

        if ($nodes->count() === 0) {
            return $result;
        }

        foreach ($crawler->filter('.search-productItem .p-title h3 a') as $node) {
            $result[] = 'http://batdongsan.com.vn' . $node->getAttribute('href');
        }

        return $result;
    }

    /**
     * Extract object of item in a page
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
            $item          = new stdClass;
            $item->name    = trim($crawler->filter('.pm-title h1')->text());
            $item->price   = trim($crawler->filter('.gia-title.mar-right-15 strong')->text());
            $item->size    = trim($crawler->filter('.gia-title')->nextAll()->filter('strong')->text());
            $item->content = $crawler->filter('.pm-content .pm-desc')->html();
            $fields        = $crawler->filter('.table-detail')->each(
                function ($row) {
                    $label = $row->filter('.left')->text();
                    if (strpos($label, 'Loại tin rao') !== false) {
                        return ['type' => $row->filter('.right')->text()];
                    }

                    if (strpos($label, 'Địa chỉ') !== false) {
                        return ['address' => $row->filter('.right')->text()];
                    }

                    if (strpos($label, 'Tên dự án') !== false) {
                        return ['project' => $row->filter('.right')->text()];
                    }

                    if (strpos($label, 'Quy mô') !== false) {
                        return ['scope' => $row->filter('.right')->text()];
                    }
                }
            );
            $item          = $this->assignFields($fields, $item);
            $contact       = $crawler->filter('#divCustomerInfo .right-content')->each(
                function ($el) {
                    $label = $el->filter('div')->text();

                    if (strpos($label, 'Tên liên lạc') !== false) {
                        return ['contact_name' => str_replace('Tên liên lạc', '', $label)];
                    }

                    if (strpos($label, 'Mobile') !== false) {
                        return ['phone' => str_replace('Mobile', '', $label)];
                    }

                    if (strpos($label, 'Email') !== false) {
                        return ['email' => str_replace('Email', '', $label)];
                    }
                }
            );

            return $this->assignFields($contact, $item);
        } catch (Exception $exception) {
            return false;
        }
    }
}
