<?php
/**
 * Created by PhpStorm.
 * User: soulevilx
 * Date: 3/29/18
 * Time: 4:21 PM
 */

namespace XGallery\Service;


use GuzzleHttp\Client;

class Nct
{
	public function getFlashLink($url)
	{
		$client  = new Client;
		$respond = $client->request('GET', $url);
		$html    = $respond->getBody()->getContents();

		$start = strpos($html, 'https://www.nhaccuatui.com/flash/xml?html5=true&key1=');
		$end   = strpos($html, '"', $start);

		$flashLink = substr($html, $start, $end - $start);

		return $flashLink;
	}

	public function getDownloadLink($url)
	{
		$client  = new Client;
		$respond = $client->request('GET', $url);
		$xml     = $respond->getBody()->getContents();
		$dom     = simplexml_load_string($xml);

		return (string) $dom->track->location;
	}
}