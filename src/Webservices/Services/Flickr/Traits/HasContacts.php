<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasContacts
 *
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasContacts
{

    /**
     * @param       $parameters
     * @param array $options
     *
     * @return mixed
     */
    abstract function rest($parameters, $options = []);

    /**
     * @param $params
     *
     * @return mixed
     * @see https://www.flickr.com/services/api/flickr.contacts.getListRecentlyUploaded.html
     */
    public function flickrContactsGetListRecentlyUploaded($params = [])
    {
        return $this->rest(
            array_merge(
                [
                    'method' => 'flickr.contacts.getListRecentlyUploaded',
                ],
                $params
            )
        );
    }

    /**
     * @param $params
     *
     * @return mixed
     * @see https://www.flickr.com/services/api/flickr.contacts.getList.html
     */
    public function flickrContactsGetList($params = [])
    {
        return $this->rest(
            array_merge(
                [
                    'method' => 'flickr.contacts.getList',
                    'per_page' => 1000,
                    'page' => 1,
                ],
                $params
            )
        );
    }

    /**
     * @return array|boolean
     */
    public function flickrContactsGetAll()
    {
        if (!$response = $this->flickrContactsGetList()) {
            return false;
        }

        $contacts = $response->contacts->contact;
        $pages    = $response->contacts->pages;

        if ($pages === 1) {
            return $contacts;
        }

        for ($page = 2; $page <= $pages; $page++) {
            $response = $this->flickrContactsGetList(
                [
                    'page' => $page,
                ]
            );

            if (!$response) {
                continue;
            }

            $contacts = array_merge($contacts, $response->contacts->contact);
        }

        return $contacts;
    }
}