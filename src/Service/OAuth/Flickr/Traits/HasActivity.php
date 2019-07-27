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
 * Trait HasActivity
 *
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasActivity
{
    /**
     * flickrActivityUserComments
     *
     * @param array $params
     * @return mixed
     * @see   https://www.flickr.com/services/api/flickr.activity.userComments.html
     */
    public function flickrActivityUserComments(array $params = [])
    {
        return $this->get(
            array_merge(
                ['method' => 'flickr.activity.userComments', 'per_page' => 50],
                $params
            )
        );
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
     * flickrActivityUserPhotos
     *
     * @param array $params
     * @return mixed
     * @see https://www.flickr.com/services/api/flickr.activity.userPhotos.html
     */
    public function flickrActivityUserPhotos(array $params = [])
    {
        return $this->get(
            array_merge(
                ['method' => 'flickr.activity.userPhotos', 'per_page' => 50],
                $params
            )
        );
    }
}
