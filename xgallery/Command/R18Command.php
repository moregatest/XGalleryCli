<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Command;

use App\Service\Crawler\Jav\R18Crawler;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use XGallery\BaseCommand;

/**
 * Class R18Command
 * @package XGallery\Command
 */
class R18Command extends BaseCommand
{
    /**
     * @var R18Crawler
     */
    protected $client;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * R18Command constructor.
     * @param R18Crawler $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(R18Crawler $client, EntityManagerInterface $entityManager)
    {
        $this->client        = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * @param $id
     * @return array|bool
     * @throws GuzzleException
     */
    protected function extractDownload($id)
    {
        $client = new Client(['allow_redirects' => ['track_redirects' => true]]);

        $url = 'http://www.r18.com/playerapi/playinfo?cid=' . $id . '&part=1';

        $domain = '.r18.com';
        $values = [
            'rid' => '4VoGk10lL1%2FS7iZpN0ccHji2FTCriX%2B32jeyo%2FIx9EFq7yQx59nR8IsCwzHJW2jrZvN9AiIlmrDHjenqB14eoVxuNSREyoHKxn9sxiISpJHcGvqPd9Aiy%2BJoBVZj3sByJKNElkfuABWJXwiQedzY3XVNmPeGfj6MTo4EA1A%2F8%2Bx%2BGAS8TMg4abuFT%2FbdJVUmR8W%2Fbg%3D%3D',
        ];

        $cookieJar = CookieJar::fromArray($values, $domain);

        $respond = $client->get($url, ['cookies' => $cookieJar]);
        $content = json_decode($respond->getBody()->getContents());

        if (empty($content->list)) {
            return false;
        }

        $baseUrl = reset($content->list);
        $baseUrl = $baseUrl->url;
        $strPos  = strpos($baseUrl, '/-/');

        $baseUrl = substr($baseUrl, 0, $strPos);
        $baseUrl = (urldecode($baseUrl));
        $baseUrl = explode('/', $baseUrl);
        array_pop($baseUrl);
        $baseUrl = implode('/', $baseUrl);
        $baseUrl = str_replace('http://str.dmm.com:80', 'http://limstcr18.hs.llnwd.net', $baseUrl);

        $item = end($content->list);
        // http://str.dmm.com:80
        $url = $item->url;

        // Redirected to http://limstcr18.hs.llnwd.net
        $respond  = $client->request('GET', $url, ['cookies' => $cookieJar]);
        $content  = $respond->getBody()->getContents();
        $chunkPos = strpos($content, 'chunklist_');
        $chunkUrl = $baseUrl . '/-/cdn/' . substr($content, $chunkPos);

        $respond = $client->request('GET', $chunkUrl, ['cookies' => $cookieJar]);
        $content = $respond->getBody()->getContents();
        $content = explode(PHP_EOL, $content);

        $endMedia = count($content) - 3;
        $endMedia = $content[$endMedia];
        $parts    = explode('_', str_replace('.ts', '', $endMedia));

        return [
            'fileNumbers' => $parts[2],
            'fileName' => $parts[0] . '_' . $parts[1],
            'downloadUrl' => $baseUrl . '/-/cdn/',
        ];
    }
}
