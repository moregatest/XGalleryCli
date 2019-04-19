<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services\Flickr\Traits;

/**
 * Trait HasContacts
 * @package XGallery\Webservices\Services\Flickr\Traits
 */
trait HasContacts
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
     * flickrContactsGetListRecentlyUploaded
     *
     * @param array $params
     * @return mixed
     * @see https://www.flickr.com/services/api/flickr.contacts.getListRecentlyUploaded.html
     */
    public function flickrContactsGetListRecentlyUploaded(array $params = [])
    {
        return $this->rest(
            array_merge(['method' => 'flickr.contacts.getListRecentlyUploaded',], $params)
        );
    }

    /**
     * flickrContactsGetList
     *
     * @param array $params
     * @return mixed
     * @see https://www.flickr.com/services/api/flickr.contacts.getList.html
     */
    public function flickrContactsGetList(array $params = [])
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
     * flickrContactsGetAll
     *
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
            $response = $this->flickrContactsGetList(['page' => $page]);

            if (!$response) {
                continue;
            }

            $contacts = array_merge($contacts, $response->contacts->contact);
        }

        return $contacts;
    }
}
