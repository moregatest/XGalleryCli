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

class PornhubCrawler extends AbstractCrawler
{

    public function getIndexPages()
    {

    }

    public function getIndexDetailLinks($url)
    {

    }

    public function getDetail($url)
    {
        if (!$response = $this->get($url)) {
            return false;
        }

        $varPos    = strpos($response, 'var flashvars_');
        $varEnd    = strpos($response, ';', $varPos);
        $subString = substr($response, $varPos, $varEnd - $varPos);
        $subPos    = strpos($subString, '{');
        $json      = json_decode(substr($subString, $subPos - 1));

        return $json;
    }

    public function getAllDetailLinks($callbackPagesCount = null, $callback = null)
    {

    }

    public function getAllDetail($offset = null, $limit = null)
    {

    }

    protected function getIndexUrl($page = null)
    {

    }
}
