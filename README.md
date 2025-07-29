# Vipps Provider for OAuth 2.0 Client
This package provides Vipps OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require oleksandr-yushchenko/oauth2-vipps
```

## Usage

Usage is the same as The League's OAuth client, using `\League\OAuth2\Client\Provider\Vipps` as the provider.

### Authorization Code Flow

```php
$provider = new League\OAuth2\Client\Provider\Vipps([
    // Required
    'clientId'                  => '{vipps-client-id}',
    'clientSecret'              => '{vipps-client-secret}',
    'redirectUri'               => 'https://example.com/callback-url',
    // Optional
    'subscriptionKey'           => '{vipps-subscription-key}',
    'merchantSerialNumber'      => '{vipps-merchant-serial-number}',
    'partnerMode'               => TRUE,
    'testMode'                  => TRUE,
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getFirstname());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

## Credits

- [Oleksandr Yushchenko](https://github.com/Oleksandr-Yushchenko)
- [All Contributors](https://github.com/Oleksandr-Yushchenko/oauth2-vipps/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/Oleksandr-Yushchenko/oauth2-vipps/blob/master/LICENSE) for more information.
