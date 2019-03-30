<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasActivity
 *
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasFavorites
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
     * Get favorites photos
     *
     * @param       $userId
     * @param array $params
     * @return mixed|array
     * @see https://www.flickr.com/services/api/flickr.favorites.getList.html
     */
    public function flickrFavoritesGetList($userId, $params = [])
    {
        return $this->rest(
            array_merge(
                [
                    'method' => 'flickr.favorites.getList',
                    'user_id' => $userId,
                    'per_page ' => 500,
                ],
                $params
            )
        );
    }

    /**
     * Get all photos in favorites
     *
     * @param string $userId
     * @return array|boolean
     */
    public function flickrFavoritesGetAllList($userId)
    {
        if (!$response = $this->flickrFavoritesGetList($userId)) {
            return false;
        }

        $photos = $response->photos->photo;
        $pages  = $response->photos->pages;

        if ($pages === 1) {
            return $photos;
        }

        for ($page = 2; $page <= $pages; $page++) {
            $response = $this->flickrFavoritesGetList($userId, ['page' => $page]);

            if (!$response) {
                continue;
            }

            $photos = array_merge($photos, $response->photos->photo);
        }

        return $photos;
    }
}
