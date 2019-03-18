<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Utilities;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
     * @throws Exception
     */
    public static function download($url, $saveTo)
    {
        $client = new Client();
        $logger = Factory::getLogger(get_called_class());
        chmod($saveTo, 644);

        try {
            $response           = $client->request('GET', $url, ['sink' => $saveTo]);
            $orgFileSize        = $response->getHeader('Content-Length')[0];
            $downloadedFileSize = filesize($saveTo);

            if ($orgFileSize != $downloadedFileSize) {
                $logger->notice('Download file error: Filesize does not match');

                return false;
            }

            if ($response->getStatusCode() !== 200) {
                $logger->notice($response->getReasonPhrase());

                return false;
            }

            return true;
        } catch (GuzzleException $exception) {
            $logger->error($exception->getMessage());
        }

        return false;
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