<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\OAuth\Flickr;

use App\Service\OAuth\Flickr\Traits\HasActivity;
use App\Service\OAuth\Flickr\Traits\HasContacts;
use App\Service\OAuth\Flickr\Traits\HasFavorites;
use App\Service\OAuth\Flickr\Traits\HasGalleries;
use App\Service\OAuth\Flickr\Traits\HasPeople;
use App\Service\OAuth\Flickr\Traits\HasPhotos;
use App\Service\OAuth\Flickr\Traits\HasPhotoSets;
use App\Service\OAuth\Flickr\Traits\HasProfile;
use App\Service\OAuth\Flickr\Traits\HasUrls;
use App\Service\OAuth\OAuthClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Flickr
 * @package XGallery\Webservices\Services
 */
class FlickrClient extends OAuthClient
{
    use HasActivity;
    use HasUrls;
    use HasProfile;
    use HasPeople;
    use HasPhotos;
    use HasPhotoSets;
    use HasGalleries;
    use HasFavorites;
    use HasContacts;

    const OAUTH_REQUEST_TOKEN_ENDPOINT = 'https://www.flickr.com/services/oauth/request_token';

    const OAUTH_AUTHORIZE_ENDPOINT = 'https://www.flickr.com/services/oauth/authorize';

    const OAUTH_GET_ACCESS_TOKEN_ENDPOINT = 'https://www.flickr.com/services/oauth/access_token';

    const REST_ENDPOINT = 'https://api.flickr.com/services/rest';

    const UPLOAD_ENDPOINT = 'https://up.flickr.com/services/upload';

    const UPLOAD_REPLACE_ENDPOINT = 'https://up.flickr.com/services/replace';

    const REST_METHOD = 'GET';

    const UPLOAD_METHOD = 'POST';

    /**
     * FlickrClient constructor.
     */
    public function __construct()
    {
        $this->setCredential(
            getenv('flickr_oauth_consumer_key'),
            getenv('flickr_oauth_consumer_secret'),
            getenv('flickr_oauth_token'),
            getenv('flickr_oauth_token_secret')
        );

        parent::__construct();
    }

    /**
     * @param $parameters
     * @param array $options
     * @return bool|mixed|string
     * @throws GuzzleException
     */
    public function get($parameters, $options = [])
    {
        $response = $this->request(
            static::REST_METHOD,
            static::REST_ENDPOINT,
            array_merge($this->getDefaultFlickrParameters(), $parameters),
            $options
        );

        if (!$response) {
            return false;
        }

        if (isset($response->stat) && $response->stat !== 'fail') {
            return $response;
        }

        $this->logNotice($response->message, [$parameters, get_object_vars($response)]);

        return false;
    }

    /**
     * Default parameters for all requests
     *
     * @return array
     */
    private function getDefaultFlickrParameters()
    {
        return ['format' => 'json', 'nojsoncallback' => 1];
    }

    /**
     * Get all photos in Album via URL
     *
     * @param string $albumUrl
     * @return array
     */
    public function getAlbumPhotos($albumUrl)
    {
        $parts   = explode('/', $albumUrl);
        $nsid    = $this->getNsid($albumUrl);
        $albumId = end($parts);

        return [
            'nsid'   => $nsid,
            'album'  => $albumId,
            'photos' => $this->flickrPhotoSetsGetAllPhotos($albumId, $nsid),
        ];
    }

    /**
     * Get by NSID by URL or ID
     *
     * @param string $id
     * @return boolean|string
     */
    public function getNsid($id)
    {
        if (filter_var($id, FILTER_VALIDATE_URL)) {
            $user = $this->flickrUrlsLookupUser($id);

            if (!$user) {
                return false;
            }

            return $user->user->id;
        }

        return $id;
    }

    /**
     * Get all favorite photos
     *
     * @param string $nsid
     * @return mixed|array
     */
    public function getAllFavorities($nsid)
    {
        return $this->flickrFavoritesGetAllList($this->getNsid($nsid));
    }
}
