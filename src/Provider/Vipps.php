<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\OptionProvider\VippsAuthOptionProvider;
use League\OAuth2\Client\Provider\Exception\VippsIdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Implements Vipps OAuth2 provider.
 */
class Vipps extends AbstractProvider
{

    use BearerAuthorizationTrait;

    /**
     * Production API URL.
     *
     * @const string
     */
    protected const BASE_VIPPS_URL = 'https://api.vipps.no';

    /**
     * Test API URL.
     *
     * @const string
     */
    protected const BASE_VIPPS_URL_TEST = 'https://apitest.vipps.no';

    /**
     * Merchant Serial Number.
     *
     * @var string
     */
    protected $merchantSerialNumber;

    /**
     * Subscription Key.
     *
     * @var string
     */
    protected $subscriptionKey;

    /**
     * Is test mode.
     *
     * @var bool
     */
    protected $testMode = false;

    /**
     * Is partner mode.
     *
     * @var bool
     */
    protected $partnerMode = false;

    /**
     * Vipps constructor.
     *
     * @param array $options
     *   Options array.
     * @param array $collaborators
     *   Collaborators array.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
        $this->setOptionProvider(new VippsAuthOptionProvider());
        $this->testMode = boolval($options['testMode']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHeaders()
    {
        $headers = parent::getDefaultHeaders();
        if ($this->subscriptionKey) {
            $headers['Ocp-Apim-Subscription-Key'] = $this->subscriptionKey;
        }
        if ($this->merchantSerialNumber) {
            $headers['Merchant-Serial-Number'] = $this->merchantSerialNumber;
        }
        return $headers;
    }

    /**
     * Get authorization url to begin OAuth flow.
     *
     * @return string
     *   Authorization URL.
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->getBaseVippsUrl() . '/access-management-1.0/access/oauth2/auth';
    }

    /**
     * Get access token url to retrieve token.
     *
     * @param array $params
     *   Parameters.
     *
     * @return string
     *   Base access token URL.
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getBaseVippsUrl() . '/access-management-1.0/access/oauth2/token';
    }

    /**
     * Get "primary" access token url to retrieve token.
     *
     * @return string
     *   Primary access token URL.
     */
    public function getPrimaryAccessTokenUrl()
    {
        return $this->getBaseVippsUrl() . '/accesstoken/get';
    }

    /**
     * Get provider url to fetch user details.
     *
     * @param \League\OAuth2\Client\Token\AccessToken $token
     *   Access token.
     *
     * @return string
     *   Resource owner details URL.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getBaseVippsUrl() . '/vipps-userinfo-api/userinfo';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     *   Empty.
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * Check a provider response for errors.
     *
     * @link https://developer.github.com/v3/#client-errors
     * @link https://developer.github.com/v3/oauth/#common-errors-for-the-access-token-request
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *   Response.
     * @param array|string $data
     *   Parsed response data.
     *
     * @throws \League\OAuth2\Client\Provider\Exception\VippsIdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw VippsIdentityProviderException::clientException($response,
                $data);
        } elseif (isset($data['error'])) {
            throw VippsIdentityProviderException::oauthException($response,
                $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     *   Response.
     * @param \League\OAuth2\Client\Token\AccessToken $token
     *   Access token.
     *
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
     *   Resource owner.
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new VippsResourceOwner($response);

        $user->verificationGuard();

        return $user->setDomain($this->getBaseVippsUrl());
    }

    /**
     * Get scope separator.
     *
     * @return string
     *   String +.
     */
    protected function getScopeSeparator()
    {
        return '+';
    }

    /**
     * Build a query string from array. Vipps API doesn't work with encoded url.
     *
     * @param array $params
     *   Parameters.
     *
     * @return string
     *   Url decoded.
     */
    protected function buildQueryString(array $params)
    {
        return urldecode(parent::buildQueryString($params));
    }

    /**
     * Check is test mode.
     *
     * @return bool
     *   True or false.
     */
    protected function isTest()
    {
        return $this->testMode;
    }

    /**
     * Check is partner mode.
     *
     * @return bool
     *   True or false.
     */
    protected function isPartner()
    {
        return $this->partnerMode;
    }

    /**
     * Get the base Vipps URL.
     *
     * @return string
     *   Vipps URL.
     */
    protected function getBaseVippsUrl()
    {
        return $this->isTest() ? static::BASE_VIPPS_URL_TEST : static::BASE_VIPPS_URL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationParameters(array $options)
    {
        $options = parent::getAuthorizationParameters($options);
        if ($this->isPartner()) {
            $options['msn'] = $this->merchantSerialNumber;
            unset($options['client_id']);
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($grant, array $options = [])
    {
        if ($this->isPartner()) {
            $grant = $this->verifyGrant($grant);
            $request_options = [
                'headers' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ];
            $method = $this->getAccessTokenMethod();
            $url = $this->getPrimaryAccessTokenUrl();
            $request = $this->getRequest($method, $url, $request_options);
            $response = $this->getParsedResponse($request);
            if (false === is_array($response)) {
                throw new \UnexpectedValueException(
                    'Invalid response received from Authorization Server. Expected JSON.'
                );
            }
            $prepared = $this->prepareAccessTokenResponse($response);
            $primary_token = $this->createAccessToken($prepared, $grant);
            $options['primary_access_token'] = $primary_token;
        }

        return parent::getAccessToken($grant, $options);
    }

}
