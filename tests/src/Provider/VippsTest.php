<?php

namespace League\OAuth2\Client\Test\Provider;

use GuzzleHttp\Psr7\Utils;
use League\OAuth2\Client\Provider\AddressData;
use League\OAuth2\Client\Provider\Vipps;
use League\OAuth2\Client\Provider\VippsResourceOwner;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class VippsTest extends TestCase
{
    use QueryBuilderTrait;

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

    public function testScopes()
    {
        $scopeSeparator = '+';
        $options = ['scope' => [uniqid(), uniqid()]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = urldecode($this->buildQueryString($query));
        $this->assertNotFalse(strpos($url, $encodedScope));
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $body = Utils::streamFor('{"access_token":"mock_access_token","expires_in":3600,"id_token":"mock_id_token","scope":"","token_type":"Bearer"}');
        $response->shouldReceive('getBody')->andReturn($body);
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData()
    {
        $email = uniqid();
        $firstname = uniqid();
        $lastname = uniqid();
        $name = $firstname . ' ' . $lastname;
        $phone = uniqid();
        $sid = rand(1000,9999);
        $userId = uniqid();
        $userAddress = json_encode([
            'address_type' => 'home',
            'country' => 'NO',
            'formatted' => "Brettevilles gate 5\n0481\nOslo\nNO",
            'postal_code' => '0481',
            'region' => 'Oslo',
            'street_address' => 'Brettevilles gate 5',
        ]);

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postBody = Utils::streamFor('{"access_token":"mock_access_token","expires_in":3600,"id_token":"mock_id_token","scope":"","token_type":"Bearer"}');
        $postResponse->shouldReceive('getBody')->andReturn($postBody);
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userBody = Utils::streamFor('{"sid": "'.$sid.'", "sub": "'.$userId.'", "name": "'.$name.'", "given_name": "'.$firstname.'", "family_name": "'.$lastname.'", "phone_number": "'.$phone.'", "email": "'.$email.'", "email_verified": "1", "address": '.$userAddress.'}');
        $userResponse->shouldReceive('getBody')->andReturn($userBody);
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertTrue($user instanceof VippsResourceOwner);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->toArray()['email']);
        $this->assertEquals($firstname, $user->getFirstName());
        $this->assertEquals($firstname, $user->toArray()['given_name']);
        $this->assertEquals($lastname, $user->getLastName());
        $this->assertEquals($lastname, $user->toArray()['family_name']);
        $this->assertEquals($phone, $user->getPhoneNumber());
        $this->assertEquals($phone, $user->toArray()['phone_number']);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($name, $user->toArray()['name']);
        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['sub']);
        $this->assertEquals($sid, $user->getSid());
        $this->assertEquals($sid, $user->toArray()['sid']);

        $address = $user->getAddress();
        $this->assertTrue($address instanceof AddressData);
    }
}
