<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasGalleries
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasGalleries
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
     * flickrGalleriesGetPhotos
     *
     * @param string $galleryId
     * @param array  $parameters
     * @return mixed
     */
    public function flickrGalleriesGetPhotos($galleryId, $parameters = [])
    {
        return $this->rest(
            array_merge(
                [
                    'method' => 'flickr.galleries.getPhotos',
                    'gallery_id' => $galleryId,
                    'per_page' => 500,
                ],
                $parameters
            )
        );
    }

    /**
     * flickrGalleriesGetAllPhotos
     *
     * @param string $galleryId
     * @return array|boolean
     */
    public function flickrGalleriesGetAllPhotos($galleryId)
    {
        if (!$response = $this->flickrGalleriesGetPhotos($galleryId)) {
            return false;
        }

        $photos = $response->photos->photo;
        $pages  = $response->photos->pages;

        if ($pages === 1) {
            return $photos;
        }

        for ($page = 2; $page <= $pages; $page++) {
            $response = $this->flickrGalleriesGetPhotos($galleryId, ['page' => $page]);

            if (!$response) {
                continue;
            }

            $photos = array_merge($photos, $response->photos->photo);
        }

        return $photos;
    }
}
