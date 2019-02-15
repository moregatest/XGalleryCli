<?php

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasUrls
 *
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasUrls
{

    /**
     * @param       $parameters
     * @param array $options
     *
     * @return mixed
     */
    abstract function rest($parameters, $options = []);

    /**
     * @param $groupId
     *
     * @return mixed
     */
    public function flickrUrlsGetGroup($groupId)
    {
        return $this->rest(
            [
                'method' => 'flickr.urls.getGroup',
                'group_id' => $groupId,
            ]
        );
    }

    /**
     * @param $userId
     *
     * @return mixed
     */
    public function flickrUrlsGetUserPhotos($userId)
    {
        return $this->rest(
            [
                'method' => 'flickr.urls.getUserPhotos',
                'user_id' => $userId,
            ]
        );
    }

    /**
     * @param null $userId
     *
     * @return mixed
     */
    public function flickrUrlsGetUserProfile($userId = null)
    {
        return $this->rest(
            [
                'method' => 'flickr.urls.getUserProfile',
                'user_id' => $userId,
            ]
        );
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public function flickrUrlsLookupGallery($url)
    {
        return $this->rest(
            [
                'method' => 'flickr.urls.lookupGallery',
                'url' => $url,
            ]
        );
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public function flickrUrlsLookupGroup($url)
    {
        return $this->rest(
            [
                'method' => 'flickr.urls.lookupGroup',
                'url' => $url,
            ]
        );
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public function flickrUrlsLookupUser($url)
    {
        return $this->rest(
            [
                'method' => 'flickr.urls.lookupUser',
                'url' => $url,
            ]
        );
    }
}