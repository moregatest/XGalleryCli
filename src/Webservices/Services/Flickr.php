<?php

namespace XGallery\Webservices\Services;

use XGallery\Webservices\Oauth\Oauth1\Client;
use XGallery\Webservices\Services\Flickr\Traits\HasActivity;
use XGallery\Webservices\Services\Flickr\Traits\HasContacts;
use XGallery\Webservices\Services\Flickr\Traits\HasPeople;
use XGallery\Webservices\Services\Flickr\Traits\HasPhotos;
use XGallery\Webservices\Services\Flickr\Traits\HasProfile;
use XGallery\Webservices\Services\Flickr\Traits\HasUrls;

/**
 * Class Flickr
 *
 * @package XGallery\Webservices\Services
 */
class Flickr extends Client
{

    use HasActivity;
    use HasUrls;
    use HasProfile;
    use HasPeople;
    use HasPhotos;
    use HasContacts;

    const OAUTH_REQUEST_TOKEN_ENDPOINT = 'https://www.flickr.com/services/oauth/request_token';

    const OAUTH_AUTHORIZE_ENDPOINT = 'https://www.flickr.com/services/oauth/authorize';

    const OAUTH_GET_ACCESS_TOKEN_ENDPOINT = 'https://www.flickr.com/services/oauth/access_token';

    const REST_ENDPOINT = 'https://api.flickr.com/services/rest';

    const UPLOAD_ENDPOINT = 'https://up.flickr.com/services/upload';

    const UPLOAD_REPLACE_ENDPOINT = 'https://up.flickr.com/services/replace';

    const REST_METHOD = 'GET';

    const UPLOAD_METHOD = 'POST';

    private $responseFormat = 'json';

    /**
     * @param $format
     */
    private function setResponseFormat($format)
    {
        $this->responseFormat = $format;
    }

    /**
     * @return array
     */
    private function getDefaultFlickrParameters()
    {
        return [
            'format' => $this->responseFormat,
            'nojsoncallback' => 1,
        ];
    }

    /**
     * @param       $parameters
     * @param array $options
     *
     * @return boolean|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function rest($parameters, $options = [])
    {
        $response = parent::api(
            static::REST_METHOD,
            static::REST_ENDPOINT,
            array_merge($this->getDefaultFlickrParameters(), $parameters),
            $options
        );

        if (!$response) {
            return false;
        }

        if ($this->responseFormat === 'json') {
            $response = json_decode($response);

            if (isset($response->stat) && $response->stat == 'fail') {
                $this->logger->notice(
                    $response->message,
                    [
                        $parameters,
                        get_object_vars($response),
                    ]
                );

                return false;
            }
        }

        return $response;
    }

    /**
     * @param       $imageFile
     * @param array $options
     *
     * @return bool|\SimpleXMLElement
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function upload($imageFile, $options = [])
    {
        if (!file_exists($imageFile) || !is_file($imageFile)) {
            return false;
        }

        $response = parent::api(
            static::UPLOAD_METHOD,
            static::UPLOAD_ENDPOINT,
            $options,
            [
                'multipart' => [
                    [
                        'name' => 'photo',
                        'contents' => fopen($imageFile, 'r'),
                    ],
                ],
            ]
        );

        if (!$response) {
            return false;
        }

        return simplexml_load_string($response);
    }

    /**
     * @param $imageFile
     * @param $photoId
     * @param array $options
     * @return bool|\SimpleXMLElement
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function replace($imageFile, $photoId, $options = [])
    {
        if (!file_exists($imageFile) || !is_file($imageFile)) {
            return false;
        }

        $response = parent::api(
            static::UPLOAD_METHOD,
            static::UPLOAD_REPLACE_ENDPOINT,
            array_merge(['photo_id' => $photoId], $options),
            [
                'multipart' => [
                    [
                        'name' => 'photo',
                        'contents' => fopen($imageFile, 'r'),
                    ],
                ],
            ]
        );

        if (!$response) {
            return false;
        }

        return simplexml_load_string($response);
    }
}