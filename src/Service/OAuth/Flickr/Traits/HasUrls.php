<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\OAuth\Flickr\Traits;

/**
 * Trait HasUrls
 *
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasUrls
{
    /**
     * flickrUrlsGetGroup
     *
     * @param string $groupId
     * @return mixed
     */
    public function flickrUrlsGetGroup($groupId)
    {
        return $this->get(['method' => 'flickr.urls.getGroup', 'group_id' => $groupId]);
    }

    /**
     * Call RESTful
     *
     * @param array $parameters
     * @param array $options
     *
     * @return mixed
     */
    abstract public function get($parameters, $options = []);

    /**
     * flickrUrlsGetUserPhotos
     *
     * @param string $userId
     * @return mixed
     */
    public function flickrUrlsGetUserPhotos($userId)
    {
        return $this->get(['method' => 'flickr.urls.getUserPhotos', 'user_id' => $userId]);
    }

    /**
     * flickrUrlsGetUserProfile
     *
     * @param null|string $userId
     * @return mixed
     */
    public function flickrUrlsGetUserProfile($userId = null)
    {
        return $this->get(['method' => 'flickr.urls.getUserProfile', 'user_id' => $userId]);
    }

    /**
     * Get gallery info from URL
     *
     * @param string $url
     * @return mixed|string
     */
    public function flickrUrlsLookupGallery($url)
    {
        return $this->get(['method' => 'flickr.urls.lookupGallery', 'url' => $url]);
    }

    /**
     * Get group NSID from URL
     *
     * @param string $url
     * @return mixed|string
     */
    public function flickrUrlsLookupGroup($url)
    {
        return $this->get(['method' => 'flickr.urls.lookupGroup', 'url' => $url]);
    }

    /**
     * Get user NSID from URL
     *
     * @param string $url
     * @return mixed|string
     */
    public function flickrUrlsLookupUser($url)
    {
        return $this->get(['method' => 'flickr.urls.lookupUser', 'url' => $url]);
    }
}
