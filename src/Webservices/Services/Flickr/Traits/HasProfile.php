<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasProfile
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasProfile
{
    /**
     * Call RESTful
     *
     * @param array $parameters
     * @param array $options
     *
     * @return mixed
     */
    abstract public function rest($parameters, $options = []);

    /**
     * flickrProfileGetProfile
     *
     * @param string $userId
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
