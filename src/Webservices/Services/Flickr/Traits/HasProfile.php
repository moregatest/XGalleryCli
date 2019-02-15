<?php

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasProfile
 *
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasProfile
{

    /**
     * @param       $parameters
     * @param array $options
     *
     * @return mixed
     */
    abstract function rest($parameters, $options = []);

    /**
     * @param $userId
     *
     * @return mixed
     */
    public function flickrProfileGetProfile($userId)
    {
        return $this->rest(
            [
                'method' => 'flickr.profile.getProfile',
                'user_id' => $userId,
            ]
        );
    }
}