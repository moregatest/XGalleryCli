<?php

namespace XGallery\Utilities;

use GuzzleHttp\Client;
use XGallery\Factory;

/**
 * Class DownloadHelper
 * @package XGallery\Utilities
 */
class DownloadHelper
{
    /**
     * @param $url
     * @param $saveTo
     * @return boolean
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function download($url, $saveTo)
    {
        $client = new Client();
        $response = $client->request('GET', $url, ['sink' => $saveTo]);
        $orgFileSize = $response->getHeader('Content-Length')[0];
        $downloadedFileSize = filesize($saveTo);

        if ($orgFileSize != $downloadedFileSize) {
            Factory::getLogger(get_called_class())->notice('Download file error: Filesize does not match');

            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        return true;
    }

    /**
     * @param $url
     * @return int
     */
    public static function getFilesize($url)
    {
        $client = new Client();

        return (int)$client->head($url)->getHeader('Content-Length')[0];
    }
}