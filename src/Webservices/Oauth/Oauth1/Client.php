<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Oauth\Oauth1;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use XGallery\Factory;
use XGallery\Webservices\Oauth\Oauth1\Traits\HasAuthorize;
use XGallery\Webservices\Restful;

/**
 * Class Client
 * @package XGallery\Webservices\Oauth\Oauth1
 */
class Client extends Restful
{
    use HasAuthorize;

    const SIGNATURE_METHOD = 'HMAC-SHA1';

    const TOKEN_REQUEST_METHOD = 'GET';

    const GET_ACCESS_TOKEN_METHOD = 'GET';

    const VERSION = '1.0';

    const OAUTH_REQUEST_TOKEN_ENDPOINT = '';

    const OAUTH_AUTHORIZE_ENDPOINT = '';

    const OAUTH_GET_ACCESS_TOKEN_ENDPOINT = '';

    const REST_ENDPOINT = '';

    /**
     * Request Oauth API
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $options
     * @return boolean|mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function oauth($method, $uri, $parameters, $options = [])
    {
        $id    = md5(serialize(func_get_args()));
        $cache = Factory::getCache();
        $item  = $cache->getItem($id);

        if ($item->isHit()) {
            $this->logNotice('Request have cached', func_get_args());

            return $item->get();
        }

        $parameters = $this->sign($method, $uri, $parameters);

        if ($method === 'GET') {
            $uri .= '?'.http_build_query($parameters);
        } else {
            $options['headers']['Authorization'] = $this->getOauthHeader();
        }

        $response = $this->fetch($method, $uri, $options);

        if ($response === false) {
            return false;
        }

        $item->set($response);
        $cache->save($item);

        return $item->get();
    }

    /**
     * Call Oauth to get request token
     *
     * @param string $callback
     * @return boolean|mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getRequestToken($callback)
    {
        $this->credential->token       = '';
        $this->credential->tokenSecret = '';

        return $this->oauth(
            static::TOKEN_REQUEST_METHOD,
            static::OAUTH_REQUEST_TOKEN_ENDPOINT,
            ['oauth_callback' => $callback]
        );
    }

    /**
     * Build request token API
     *
     * @param string $callback
     * @return string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getRequestTokenUrl($callback)
    {
        parse_str($this->getRequestToken($callback), $query);

        return static::OAUTH_AUTHORIZE_ENDPOINT.'?oauth_token='.$query['oauth_token'];
    }

    /**
     * Get Oauth access token
     *
     * @param string $oauthToken
     * @param string $oauthVerifier
     * @return boolean|mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getAccessToken($oauthToken, $oauthVerifier)
    {
        return $this->oauth(
            static::GET_ACCESS_TOKEN_METHOD,
            static::OAUTH_GET_ACCESS_TOKEN_ENDPOINT,
            ['oauth_token' => $oauthToken, 'oauth_verifier' => $oauthVerifier]
        );
    }
}
