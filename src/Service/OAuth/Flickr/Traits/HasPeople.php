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
 * Trait HasPeople
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasPeople
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
     * Search people by email
     *
     * @param string $email
     * @return object|mixed
     */
    public function flickrPeopleFindByEmail($email)
    {
        return $this->get(['method' => 'flickr.people.findByEmail', 'find_email' => $email]);
    }

    /**
     * Get photos of request people
     *
     * @param string $nsid
     * @param array $params
     * @return mixed
     */
    public function flickrPeopleGetPhotos($nsid, $params = [])
    {
        return $this->get(
            array_merge(
                ['method' => 'flickr.people.getPhotos', 'per_page' => 500, 'user_id' => $nsid],
                $params
            )
        );
    }

    /**
     * Recursive to get all photos of people
     *
     * @param string $nsid
     * @return array|boolean
     */
    public function flickrPeopleGetAllPhotos($nsid)
    {
        if (!$response = $this->flickrPeopleGetPhotos($nsid)) {
            return false;
        }

        $photos = $response->photos->photo;
        $pages  = $response->photos->pages;

        if ($pages === 1) {
            return $photos;
        }

        for ($page = 2; $page <= $pages; $page++) {
            $response = $this->flickrPeopleGetPhotos($nsid, ['page' => $page]);

            if (!$response) {
                continue;
            }

            $photos = array_merge($photos, $response->photos->photo);
        }

        return $photos;
    }

    /**
     * Get people information
     *
     * @param $nsid
     * @return mixed
     */
    public function flickrPeopleGetInfo($nsid)
    {
        return $this->get(['method' => 'flickr.people.getInfo', 'user_id' => $nsid]);
    }
}
