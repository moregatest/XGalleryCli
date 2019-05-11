<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Model\Flickr;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use XGallery\Model\BaseModel;

/**
 * Class ModelFlickr
 *
 * @package XGallery\Model\Flickr
 */
class ModelFlickr extends BaseModel
{
    /**
     * Insert multi contacts
     *
     * @param $contacts
     * @return boolean
     * @throws \Exception
     */
    public function insertContacts($contacts)
    {
        if (empty($contacts)) {
            return false;
        }

        foreach ($contacts as $contact) {
            if (is_object($contact)) {
                $contact = get_object_vars($contact);
            }

            try {
                $this->connection->insert('xgallery_flickr_contacts', $contact);
            } catch (DBALException $exception) {
                $this->errors[] = $exception->getMessage();

                continue;
            }
        }

        return true;
    }

    /**
     * Insert multi photos
     *
     * @param $photos
     * @return boolean
     * @throws \Exception
     */
    public function insertPhotos($photos)
    {
        if (empty($photos)) {
            return false;
        }

        foreach ($photos as $photo) {
            if (is_object($photo)) {
                $photo = get_object_vars($photo);
            }

            unset($photo['is_primary'], $photo['isprimary'], $photo['date_faved']);

            try {
                $this->connection->insert('xgallery_flickr_photos', $photo);
            } catch (DBALException $exception) {
                $this->errors[] = $exception->getMessage();

                continue;
            }
        }

        return true;
    }

    /**
     * Update photo record
     *
     * @param integer $id
     * @param array   $data
     * @return boolean|integer
     */
    public function updatePhoto($id, $data)
    {
        try {
            $this->reset();

            return $this->connection->update('`xgallery_flickr_photos`', $data, ['id' => $id]);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }

    /**
     * Get photo ids by status
     *
     * @param integer $status
     * @param string  $nsid
     * @param integer $limit
     * @return boolean|mixed[]
     */
    public function getPhotoIds($status, $nsid = null, $limit = null)
    {
        $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE `status` = '.(int)$status;

        if ($nsid) {
            $query .= ' AND `owner` = ?';
        }

        if ($limit !== null) {
            $query .= ' LIMIT '.(int)$limit;
        }

        try {
            $this->reset();

            if ($nsid) {
                return $this->connection->executeQuery($query, [$nsid])->fetchAll(FetchMode::COLUMN);
            }

            return $this->connection->executeQuery($query)->fetchAll(FetchMode::COLUMN);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }

    /**
     * Get unsized photo ids
     *
     * @param null $nsid
     * @param null $limit
     * @return boolean|mixed[]
     */
    public function getPhotoIdsUnsized($nsid = null, $limit = null)
    {
        $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL) AND `params` IS NULL';

        if ($nsid) {
            $query .= ' AND `owner` = ?';
        }

        if ($limit !== null) {
            $query .= ' LIMIT '.(int)$limit;
        }

        try {
            $this->reset();

            if ($nsid) {
                return $this->connection->executeQuery($query, [$nsid])->fetchAll(FetchMode::COLUMN);
            }

            return $this->connection->executeQuery($query)->fetchAll(FetchMode::COLUMN);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }

    /**
     * Get photo for download
     *
     * @param $id
     * @return mixed
     */
    public function getPhotoForDownload($id = null)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('*')
            ->from('`xgallery_flickr_photos`');
        if ($id) {
            $queryBuilder->where('id = ?');
        } else {
            $queryBuilder->where('(`status` = 0 OR `status` IS NULL OR `status` = 4)');
        }

        $query = $queryBuilder->getSQL().' LIMIT 1 FOR UPDATE';

        try {
            $this->reset();

            return $this->connection->executeQuery($query, [$id])->fetch(FetchMode::STANDARD_OBJECT);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }

    /**
     * Get NSID
     *
     * @return boolean|string
     */
    public function getContactNsid()
    {
        try {
            $this->reset();

            return $this->connection->executeQuery(
                'SELECT `nsid` FROM `xgallery_flickr_contacts` ORDER BY `modified` ASC LIMIT 1 FOR UPDATE'
            )->fetch(FetchMode::COLUMN);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }

    /**
     * Update contact record
     *
     * @param integer $nsid
     * @param array   $data
     * @return boolean|integer
     */
    public function updateContact($nsid, $data)
    {
        try {
            $this->reset();

            return $this->connection->update('`xgallery_flickr_contacts`', $data, ['nsid' => $nsid]);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }

    /**
     * Get photo by ID
     *
     * @param $id
     * @return mixed
     */
    public function getPhotoById($id)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('*')
            ->from('`xgallery_flickr_photos`')
            ->where('`id` = ?');

        try {
            $this->reset();

            return $this->connection->executeQuery($queryBuilder, [$id])->fetch(FetchMode::STANDARD_OBJECT);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }

    /**
     * Get photo by ID
     *
     * @param $ids
     * @return mixed
     */
    public function getPhotoByIds($ids)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('*')
            ->from('`xgallery_flickr_photos`')
            ->where('`id` IN ('.implode(',', $ids).')');

        try {
            $this->reset();

            return $this->connection->executeQuery($queryBuilder)->fetchAll(FetchMode::STANDARD_OBJECT);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }
    }
}
