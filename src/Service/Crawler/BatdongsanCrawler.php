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

use App\Service\Crawler;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;

/**
 * Class BdsCrawler
 * @package App\Service\Crawler
 */
class BatdongsanCrawler extends Crawler
{
    /**
     * Return number of pages
     *
     * @param $url
     * @return integer
     * @throws GuzzleException
     */
    public function getPages($url)
    {
        $crawler = $this->request('GET', $url);
        $pages = $crawler->filter('.background-pager-right-controls a')->last()->attr('href');
        $pages = explode('/', $pages);
        $page = str_replace('p', '', end($pages));

        return (int)$page;
    }

    /**
     * Extract all items on page
     *
     * @param $url
     * @return array
     * @throws GuzzleException
     */
    public function extractItems($url)
    {
        $crawler = $this->request('GET', $url);
        $result = [];

        foreach ($crawler->filter('.search-productItem .p-title h3 a') as $node) {
            $result[] = $node->getAttribute('href');
        }

        return $result;
    }

    /**
     * Extract object of item in a page
     *
     * @param $url
     * @return boolean|stdClass
     * @throws GuzzleException
     */
    public function extractItem($url)
    {
        $crawler = $this->request('GET', $url);

        if (!$crawler) {
            return false;
        }

        $item = new stdClass;

        $item->name = trim($crawler->filter('.pm-title h1')->text());
        $item->price = trim($crawler->filter('.gia-title.mar-right-15 strong')->text());
        $item->size = trim($crawler->filter('.gia-title')->nextAll()->filter('strong')->text());
        $item->content = $crawler->filter('.pm-content .pm-desc')->html();

        $fields = $crawler->filter('.table-detail')->each(
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

        $this->setItemData($fields, $item);

        $contact = $crawler->filter('#divCustomerInfo .right-content')->each(
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

        $this->setItemData($contact, $item);

        return $item;
    }

    /**
     * @param $fields
     * @param $item
     */
    private function setItemData($fields, &$item)
    {
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
    }
}
