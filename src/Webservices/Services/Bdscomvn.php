<?php


namespace XGallery\Webservices\Services;

use stdClass;
use Symfony\Component\DomCrawler\Crawler;
use XGallery\Webservices\Restful;

class Bdscomvn extends Restful
{
    public function getItem($url)
    {
        $response = $this->fetch('GET', $url);

        if (!$response) {
            return false;
        }

        $crawler = new Crawler($response);
        $item    = new stdClass;

        $item->name    = trim($crawler->filter('.pm-title h1')->text());
        $item->price   = trim($crawler->filter('.gia-title.mar-right-15 strong')->text());
        $item->size    = trim($crawler->filter('.gia-title')->nextAll()->filter('strong')->text());
        $item->content = $crawler->filter('.pm-content .pm-desc')->html();

        $fields = $crawler->filter('.table-detail')->each(function ($row) {
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
        });

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

        $contact = $crawler->filter('#divCustomerInfo .right-content')->each(function ($el) {
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
        });

        foreach ($contact as $field) {
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

        var_dump($item);
        exit;
    }
}
