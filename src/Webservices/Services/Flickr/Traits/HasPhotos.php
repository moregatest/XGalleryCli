<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasPhotos
 *
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasPhotos
{

    /**
     * @param       $parameters
     * @param array $options
     *
     * @return mixed
     */
    abstract public function rest($parameters, $options = []);

    /**
     * @param string $keyword
     * @param array  $parameters
     *
     * @return mixed
     * @uses  https://www.flickr.com/services/api/flickr.photos.search.html
     */
    public function flickrPhotosSearch($keyword = '', $parameters = [])
    {
        return $this->rest(
            array_merge(
                [
                    'method' => 'flickr.photos.search',
                    'text' => $keyword,
                    'safe_search' => 3,
                    'per_page' => 500,
                    'page' => 1,
                ],
                $parameters
            )
        );
    }

    /**
     * @param       $photoId
     * @param array $parameters
     * @return mixed
     */
    public function flickrPhotosSizes($photoId, $parameters = [])
    {
        return $this->rest(
            array_merge(
                [
                    'method' => 'flickr.photos.getSizes',
                    'photo_id' => $photoId,
                ],
                $parameters
            )
        );
    }

    /**
     * @param       $photoId
     * @param array $parameters
     * @return mixed
     */
    public function flickrPhotosGetInfo($photoId, $parameters = [])
    {
        return $this->rest(
            array_merge(
                [
                    'method' => 'flickr.photos.getInfo',
                    'photo_id' => $photoId,
                ],
                $parameters
            )
        );
    }
}
