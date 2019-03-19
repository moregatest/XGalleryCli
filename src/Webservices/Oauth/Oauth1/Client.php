<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Oauth\Oauth1;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use XGallery\Exceptions\Exception;
use XGallery\Webservices\Oauth\Oauth1\Traits\HasAuthorize;
use XGallery\Webservices\Restful;

/**
 * Class Client
 *
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
     * @param       $method
     * @param       $uri
     * @param       $parameters
     * @param array $options
     * @return boolean|string
     * @throws GuzzleException
     */
    public function api($method, $uri, $parameters, $options = [])
    {
        $parameters = $this->sign(
            $method,
            $uri,
            $parameters
        );

        if ($method == 'GET') {
            $uri .= '?'.http_build_query($parameters);
        } else {
            $options['headers']['Authorization'] = $this->getOauthHeader();
        }

        $response = $this->fetch($method, $uri, $options);

        if ($response === false) {
            return false;
        }

        return $response;
    }

    /**
     * @param $callback
     *
     * @return boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getRequestToken($callback)
    {
        $this->credential->token       = '';
        $this->credential->tokenSecret = '';

        return $this->api(
            static::TOKEN_REQUEST_METHOD,
            static::OAUTH_REQUEST_TOKEN_ENDPOINT,
            ['oauth_callback' => $callback]
        );
    }

    /**
     * @param $callback
     *
     * @return boolean|string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getRequestTokenUrl($callback)
    {
        try {
            parse_str($this->getRequestToken($callback), $query);
        } catch (Exception $exception) {
            return false;
        }

        return static::OAUTH_AUTHORIZE_ENDPOINT.'?oauth_token='
            .$query['oauth_token'];
    }

    /**
     * @param $oauthToken
     * @param $oauthVerifier
     *
     * @return boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getAccessToken($oauthToken, $oauthVerifier)
    {
        return $this->api(
            static::GET_ACCESS_TOKEN_METHOD,
            static::OAUTH_GET_ACCESS_TOKEN_ENDPOINT,
            [
                'oauth_token' => $oauthToken,
                'oauth_verifier' => $oauthVerifier,
            ]
        );
    }
}