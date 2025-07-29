<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\Vipps;
use PHPUnit\Framework\TestCase;

class VippsTest extends TestCase
{
    const CLIENT_ID = 'XXXXXXXXX';
    const CLIENT_SECRET = 'YYYYYYYYY';
    const REDIRECT_URI = 'https://example.com/connect/check';

    /**
     * @var Vipps
     */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new Vipps([
            'clientId' => self::CLIENT_ID,
            'clientSecret' => self::CLIENT_SECRET,
            'redirectUri' => self::REDIRECT_URI,
        ]);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertEquals($query['client_id'], self::CLIENT_ID);

        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertEquals($query['redirect_uri'], self::REDIRECT_URI);

        $this->assertArrayHasKey('state', $query);
        $this->assertNotNull($query['state']);
        $this->assertNotNull($this->provider->getState());

        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }
}
