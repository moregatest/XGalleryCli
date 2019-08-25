<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\OAuth;

use App\Service\HttpClient;
use App\Traits\HasLogger;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;

/**
 * Class OAuthClient
 * @package App\Service\OAuth
 */
class OAuthClient
{
    use HasLogger;

    const SIGNATURE_METHOD = 'HMAC-SHA1';

    const TOKEN_REQUEST_METHOD = 'GET';

    const GET_ACCESS_TOKEN_METHOD = 'GET';

    const VERSION = '1.0';

    const OAUTH_REQUEST_TOKEN_ENDPOINT = '';

    const OAUTH_AUTHORIZE_ENDPOINT = '';

    const OAUTH_GET_ACCESS_TOKEN_ENDPOINT = '';

    const REST_ENDPOINT = '';

    /**
     * @var HttpClient
     */
    public $client;

    /**
     * @var array
     */
    private $credential;

    /**
     * @var array
     */
    private $oauthParameters;

    /**
     * @var integer|null
     */
    private $expireAfter;

    /**
     * OAuthClient constructor.
     */
    public function __construct()
    {
        $this->client = new HttpClient;
    }

    /**
     * @param $expireAfter
     */
    public function setExpireAfter($expireAfter)
    {
        $this->expireAfter = $expireAfter;
    }

    /**
     * @param $callback
     * @return string
     * @throws GuzzleException
     */
    public function getRequestTokenUrl($callback)
    {
        parse_str($this->getRequestToken($callback), $query);

        return static::OAUTH_AUTHORIZE_ENDPOINT . '?oauth_token=' . $query['oauth_token'];
    }

    /**
     * @param $callback
     * @return boolean|string
     * @throws GuzzleException
     */
    public function getRequestToken($callback)
    {
        $this->credential['token']       = '';
        $this->credential['tokenSecret'] = '';

        return $this->request(
            static::TOKEN_REQUEST_METHOD,
            static::OAUTH_REQUEST_TOKEN_ENDPOINT,
            ['oauth_callback' => $callback]
        );
    }

    /**
     * @param $method
     * @param $uri
     * @param $parameters
     * @param array $options
     * @return bool|string
     * @throws GuzzleException
     */
    public function request($method, $uri, $parameters, $options = [])
    {
        try {
            $parameters = $this->sign($method, $uri, $parameters);

            if ($method === 'GET') {
                $uri .= '?' . http_build_query($parameters);
            } else {
                $options['headers']['Authorization'] = $this->getOauthHeader();
            }

            $response = $this->client->request($method, $uri, $options);

            if ($response === false) {
                return false;
            }

            return $response;
        } catch (InvalidArgumentException $exception) {
            $this->logError($exception->getMessage());

            return false;
        }
    }

    /**
     * Oauth signature
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     *
     * @return mixed
     */
    private function sign($method, $uri, $parameters)
    {
        $parameters = array_merge($this->getOauthParameters(), $parameters);

        if ($this->credential['token']) {
            $parameters['oauth_token'] = $this->credential['token'];
        }

        ksort($parameters);

        $parametersString = [];

        foreach ($parameters as $key => $value) {
            /**
             * @uses Both $key and $value MUST BE encoded
             */
            $parametersString[] = $this->encode($key) . '=' . $this->encode($value);
        }

        $baseSignature = $this->encode(strtoupper($method))
            . '&' . $this->encode($uri)
            . '&' . $this->encode(
                implode('&', $parametersString)
            );

        $parameters['oauth_signature'] = $this->getSignature($baseSignature);

        // For header we'll use encode for signature
        $this->oauthParameters                    = $parameters;
        $this->oauthParameters['oauth_signature'] = $this->encode($parameters['oauth_signature']);

        return $parameters;
    }

    /**
     * Get oauth parameters
     *
     * @return array
     */
    private function getOauthParameters()
    {
        return
            [
                'oauth_consumer_key'     => $this->credential['consumerKey'],
                'oauth_nonce'            => $this->getNonce(),
                'oauth_signature_method' => self::SIGNATURE_METHOD,
                'oauth_timestamp'        => time(),
                'oauth_version'          => self::VERSION,
            ];
    }

    /**
     * getNonce
     *
     * @return string
     */
    private function getNonce()
    {
        return md5(uniqid((string)mt_rand(), true));
    }

    /**
     * encode
     *
     * @param array|string $value
     * @return array|mixed
     */
    private function encode($value)
    {
        if (!is_array($value)) {
            return str_replace('%7E', '~', str_replace('+', ' ', rawurlencode((string)$value)));
        }

        foreach ($value as $key => $aValue) {
            $value[$key] = $this->encode($aValue);
        }

        return $value;
    }

    /**
     * Get encrypted signature
     *
     * @param string $baseSignature
     *
     * @return string
     */
    private function getSignature($baseSignature)
    {
        return base64_encode(hash_hmac('SHA1', $baseSignature, $this->getKey(), true));
    }

    /**
     * Get key
     *
     * @return string
     */
    private function getKey()
    {
        return $this->encode($this->credential['consumerSecretKey'])
            . '&' . $this->encode($this->credential['tokenSecret']);
    }

    /**
     * Get oauth header
     *
     * @return string
     */
    private function getOauthHeader()
    {
        $header = 'OAuth ';

        foreach ($this->oauthParameters as $key => $value) {
            $header .= $key . '="' . $value . '",';
        }

        return rtrim($header, ',');
    }

    /**
     * @param $oauthToken
     * @param $oauthVerifier
     * @return boolean|string
     * @throws GuzzleException
     */
    public function getAccessToken($oauthToken, $oauthVerifier)
    {
        return $this->request(
            static::GET_ACCESS_TOKEN_METHOD,
            static::OAUTH_GET_ACCESS_TOKEN_ENDPOINT,
            ['oauth_token' => $oauthToken, 'oauth_verifier' => $oauthVerifier]
        );
    }

    /**
     * @param $consumerKey
     * @param $consumerSecretKey
     * @param $token
     * @param $tokenSecret
     */
    protected function setCredential($consumerKey, $consumerSecretKey, $token, $tokenSecret)
    {
        $this->credential['consumerKey']       = $consumerKey;
        $this->credential['consumerSecretKey'] = $consumerSecretKey;
        $this->credential['token']             = $token;
        $this->credential['tokenSecret']       = $tokenSecret;
    }
}
