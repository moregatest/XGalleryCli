<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\OAuth\Flickr\Traits;

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
    abstract public function get($parameters, $options = []);

    /**
     * flickrProfileGetProfile
     *
     * @param string $userId
     * @return mixed
     */
    public function flickrProfileGetProfile($userId)
    {
        return $this->get(['method' => 'flickr.profile.getProfile', 'user_id' => $userId]);
    }
}
