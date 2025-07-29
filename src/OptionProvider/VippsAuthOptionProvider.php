<?php

namespace League\OAuth2\Client\OptionProvider;

use League\OAuth2\Client\Tool\QueryBuilderTrait;

/**
 * Class Vipps Auth Option Provider.
 */
class VippsAuthOptionProvider implements OptionProviderInterface
{

    use QueryBuilderTrait;

    /**
     * Get access token options.
     *
     * @inheritdoc
     */
    public function getAccessTokenOptions($method, array $params)
    {
        $body = $this->getAccessTokenBody([
            'grant_type' => $params['grant_type'],
            'code' => $params['code'],
            'redirect_uri' => $params['redirect_uri'],
        ]);

        $options = [
            'headers' => [
                'Authorization' => "Basic " . base64_encode("{$params['client_id']}:{$params['client_secret']}"),
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $body,
        ];

        // Primary access token is required when Partner mode is enabled.
        if (isset($params['primary_access_token'])) {
            $options['headers']['Authorization'] = "Bearer " . $params['primary_access_token'];
        }

        return $options;
    }

    /**
     * Returns the request body for requesting an access token.
     *
     * @param array $params
     *   Parameters.
     *
     * @return string
     *   Access token body.
     */
    protected function getAccessTokenBody(array $params)
    {
        return urldecode($this->buildQueryString($params));
    }
}
