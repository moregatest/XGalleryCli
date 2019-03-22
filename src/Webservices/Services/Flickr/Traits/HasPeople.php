<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasPeople
 *
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasPeople
{

    /**
     * @param       $parameters
     * @param array $options
     *
     * @return mixed
     */
    abstract public function rest($parameters, $options = []);

    /**
     * @param $email
     *
     * @return mixed
     */
    public function flickrPeopleFindByEmail($email)
    {
        return $this->rest(
            [
                'method' => 'flickr.people.findByEmail',
                'find_email' => $email,
            ]
        );
    }

    /**
     * @param       $nsid
     * @param array $params
     * @return mixed
     */
    public function flickrPeopleGetPhotos($nsid, $params = [])
    {
        return $this->rest(
            array_merge(
                [
                    'method' => 'flickr.people.getPhotos',
                    'per_page' => 200,
                ],
                ['user_id' => $nsid],
                $params
            )
        );
    }

    /**
     * @param $nsid
     * @return array|bool
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
            $response = $this->flickrPeopleGetPhotos(
                $nsid,
                [
                    'page' => $page,
                ]
            );

            if (!$response) {
                continue;
            }

            $photos = array_merge($photos, $response->photos->photo);
        }

        return $photos;
    }

    /**
     * @param $nsid
     * @return mixed
     */
    public function flickrPeopleGetInfo($nsid)
    {
        return $this->rest(
            [
                'method' => 'flickr.people.getInfo',
                'user_id' => $nsid,
            ]
        );
    }
}
