<?php

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasActivity
 *
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasActivity
{

    /**
     * @param       $parameters
     * @param array $options
     *
     * @return mixed
     */
    abstract function rest($parameters, $options = []);

    /**
     * @param $params
     *
     * @return mixed
     * @see https://www.flickr.com/services/api/flickr.activity.userComments.html
     */
    public function flickrActivityUserComments($params = [])
    {
        return $this->rest(
            array_merge(
                [
                    'method' => 'flickr.activity.userComments',
                    'per_page' => 50,
                    'page' => 1,
                ],
                $params
            )
        );
    }

    /**
     * @param $params
     *
     * @return mixed
     * @see https://www.flickr.com/services/api/flickr.activity.userPhotos.html
     */
    public function flickrActivityUserPhotos($params = [])
    {
        return $this->rest(
            'GET',
            array_merge(
                [
                    'method' => 'flickr.activity.userPhotos',
                    'per_page' => 50,
                    'page' => 1,
                ],
                $params
            )
        );
    }
}