<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Tests\Service\OAuth;

use App\Service\OAuth\Flickr\FlickrClient;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Class FlickrClientTestSuccessful
 * @package App\Tests\Service\OAuth
 */
class FlickrClientTestSuccessful extends TestCase
{

    private $nsid = '94529704@N02';
    private $userUrl = 'https://www.flickr.com/photos/soulevilx/';
    private $email = 'soulevilx@gmail.com';

    private $photoId = '35472684005';

    private $albumId = '72157674594210788';
    private $albumUrl = 'https://www.flickr.com/photos/flickr/albums/72157707851154934';

    private $galleryUrl = 'https://www.flickr.com/photos/flickr/galleries/72157708807299412/';
    private $galleryId = '72157708807299412';

    private $groupUrl = 'https://www.flickr.com/groups/api/';
    private $groupId = '51035612836@N01';

    /**
     * @return FlickrClient
     */
    private function getClient()
    {
        static $instance;

        if (isset($instance)) {
            return $instance;
        }

        $instance = new FlickrClient;

        return $instance;
    }

    /**
     * @param $func
     * @param array $args
     */
    protected function isObject($func, $args = [])
    {
        $func = str_replace('testFlickr', 'flickr', $func);

        $this->assertIsObject(call_user_func_array([$this->getClient(), $func], $args));
    }

    /**
     * @param $func
     * @param array $args
     */
    protected function isArray($func, $args = [])
    {
        $func = str_replace('testFlickr', 'flickr', $func);

        $this->assertIsArray(call_user_func_array([$this->getClient(), $func], $args));
    }

    public function testFlickrPhotosSearch()
    {
        $this->isObject(__FUNCTION__);
    }

    public function testFlickrActivityUserPhotos()
    {
        $this->isObject(__FUNCTION__);
    }

    public function testFlickrFavoritesGetList()
    {
        $this->isObject(__FUNCTION__, ['user_id' => $this->nsid]);
    }

    /**
     * @TODO If no NSID provided API will return false
     */
    public function testFlickrPeopleGetAllPhotos()
    {
        $this->assertIsArray($this->getClient()->flickrPeopleGetAllPhotos($this->nsid));
    }

    public function testFlickrPhotoSetsGetPhotos()
    {
        $this->isObject(__FUNCTION__, [$this->albumId, $this->nsid]);
    }

    public function testFlickrUrlsLookupGallery()
    {
        $this->isObject(__FUNCTION__, [$this->galleryUrl]);
    }

    public function testFlickrUrlsGetUserProfile()
    {
        $this->isObject(__FUNCTION__);
    }

    public function testFlickrPhotoSetsGetAllPhotos()
    {
        $this->isArray(__FUNCTION__, [$this->albumId, $this->nsid]);
    }

    public function testFlickrUrlsGetUserPhotos()
    {
        $this->isObject(__FUNCTION__, [$this->nsid]);
    }

    public function testFlickrGalleriesGetPhotos()
    {
        $this->isObject(__FUNCTION__, [$this->galleryId]);
    }

    public function testFlickrActivityUserComments()
    {
        $this->isObject(__FUNCTION__);
    }

    public function testFlickrContactsGetList()
    {
        $this->isObject(__FUNCTION__);
    }

    public function testFlickrPeopleGetPhotos()
    {
        $this->isObject(__FUNCTION__, [$this->nsid]);
    }

    public function testFlickrUrlsLookupGroup()
    {
        $this->isObject(__FUNCTION__, [$this->groupUrl]);
    }

    public function testFlickrPhotosSizes()
    {
        $this->isObject(__FUNCTION__, ['32921496767']);
    }

    public function testFlickrPhotosGetInfo()
    {
        $this->isObject(__FUNCTION__, [$this->photoId]);
    }

    public function testGetNsidFromID()
    {
        $this->assertIsString($this->getClient()->getNsid($this->nsid));
    }

    public function testGetNsidFromUrl()
    {
        $this->assertIsString($this->getClient()->getNsid($this->userUrl));
    }

    public function testFlickrContactsGetAll()
    {
        $this->isObject(__FUNCTION__);
    }

    public function testGetAllFavorities()
    {
        $this->isArray('getAllFavorities', [$this->nsid]);
    }

    public function testFlickrUrlsGetGroup()
    {
        $this->isObject(__FUNCTION__, [$this->groupId]);
    }

    public function testFlickrFavoritesGetAllList()
    {
        $this->isArray(__FUNCTION__, [$this->nsid]);
    }

    public function testFlickrGalleriesGetAllPhotos()
    {
        $this->isArray(__FUNCTION__, [$this->galleryId]);
    }

    public function testGetAlbumPhotos()
    {
        $this->isArray('getAlbumPhotos', [$this->albumUrl]);
    }

    public function testFlickrProfileGetProfile()
    {
        $this->isObject(__FUNCTION__, [$this->nsid]);
    }

    public function testFlickrPeopleFindByEmail()
    {
        $this->isObject(__FUNCTION__, [$this->email]);
    }

    public function testFlickrPeopleGetInfo()
    {
        $this->isObject(__FUNCTION__, [$this->nsid]);
    }

    public function testFlickrUrlsLookupUser()
    {
        $this->isObject(__FUNCTION__, [$this->userUrl]);
    }

    public function testFlickrContactsGetListRecentlyUploaded()
    {
        $this->isObject(__FUNCTION__);
    }
}
