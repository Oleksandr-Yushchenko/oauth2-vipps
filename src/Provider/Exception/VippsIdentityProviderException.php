<?php

namespace League\OAuth2\Client\Provider\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * Class Vipps Identity Provider Exception.
 */
class VippsIdentityProviderException extends IdentityProviderException
{

    /**
     * Creates client exception from response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *   Response.
     * @param array|string $data
     *   Parsed response data.
     *
     * @return VippsIdentityProviderException
     *   Vipps identity provider exception.
     */
    public static function clientException(ResponseInterface $response, $data)
    {
        return static::fromResponse(
            $response,
            $data['message'] ?? $response->getReasonPhrase()
        );
    }

    /**
     * Creates oauth exception from response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *   Response.
     * @param array|string $data
     *   Parsed response data.
     *
     * @return VippsIdentityProviderException
     *   Vipps identity provider exception.
     */
    public static function oauthException(ResponseInterface $response, $data)
    {
        return static::fromResponse(
            $response,
            $data['error'] ?? $response->getReasonPhrase()
        );
    }

    /**
     * Creates identity exception from response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *   Response.
     * @param string $message
     *   Message.
     *
     * @return VippsIdentityProviderException
     *   Vipps identity provider exception.
     */
    protected static function fromResponse(
        ResponseInterface $response,
        $message = null
    ) {
        return new static($message, $response->getStatusCode(),
            (string)$response->getBody());
    }

}
