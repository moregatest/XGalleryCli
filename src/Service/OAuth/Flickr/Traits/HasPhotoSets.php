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
 * Trait HasPhotoSets
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasPhotoSets
{
    /**
     * Get all photos in an album
     *
     * @param string $photoSetId
     * @param string $userId
     * @return array|boolean
     */
    public function flickrPhotoSetsGetAllPhotos($photoSetId, $userId)
    {
        if (!$response = $this->flickrPhotoSetsGetPhotos($photoSetId, $userId)) {
            return false;
        }

        $photos = $response->photoset->photo;
        $pages  = $response->photoset->pages;

        if ($pages === 1) {
            return $photos;
        }

        for ($page = 2; $page <= $pages; $page++) {
            $response = $this->flickrPhotoSetsGetPhotos($photoSetId, $userId, ['page' => $page]);

            if (!$response) {
                continue;
            }

            $photos = array_merge($photos, $response->photoset->photo);
        }

        return $photos;
    }

    /**
     * Get photos in an album
     *
     * @param       $photoSetId
     * @param       $userId
     * @param array $parameters
     * @return mixed
     */
    public function flickrPhotoSetsGetPhotos($photoSetId, $userId, $parameters = [])
    {
        if (!$photoSetId || !$userId) {
            return false;
        }

        return $this->get(
            array_merge(
                [
                    'method' => 'flickr.photosets.getPhotos',
                    'photoset_id' => $photoSetId,
                    'user_id' => $userId,
                    'per_page' => 500,
                ],
                $parameters
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
}
