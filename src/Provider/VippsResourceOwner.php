<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\EmailNotVerifiedException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

/**
 * Class Vipps Resource Owner.
 */
class VippsResourceOwner implements ResourceOwnerInterface
{

    use ArrayAccessorTrait;

    /**
     * Domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * Raw response.
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array $response
     *   Response.
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id.
     *
     * @return string|null
     *   owner id.
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'sid');
    }

    /**
     * Get resource owner sub.
     *
     * @return string|null
     *   Owner sub.
     */
    public function getSub()
    {
        return $this->getValueByKey($this->response, 'sub');
    }

    /**
     * Get resource owner email.
     *
     * @return string|null
     *   Owner email.
     */
    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * Get resource owner email verified.
     *
     * @return string|null
     *   Owner email verified.
     */
    public function emailVerified()
    {
        return boolval($this->getValueByKey($this->response, 'email_verified'));
    }

    /**
     * Get resource owner name.
     *
     * @return string|null
     *   Owner name.
     */
    public function getName()
    {
        return $this->getValueByKey($this->response, 'name');
    }

    /**
     * Get resource owner first name.
     *
     * @return string|null
     *   Owner First name.
     */
    public function getFirstName()
    {
        return $this->getValueByKey($this->response, 'given_name');
    }

    /**
     * Get resource owner last name.
     *
     * @return string|null
     *   Owner last name.
     */
    public function getLastName()
    {
        return $this->getValueByKey($this->response, 'family_name');
    }

    /**
     * Get resource owner phone.
     *
     * @return string|null
     *   Owner phone number.
     */
    public function getPhoneNumber()
    {
        return $this->getValueByKey($this->response, 'phone_number');
    }

    /**
     * Get resource owner nickname.
     *
     * @return string|null
     *   Nickname.
     */
    public function getNickname()
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * Get resource owner Address.
     *
     * @return AddressData
     *   Address array.
     */
    public function getAddress()
    {
        $addressBody = $this->getValueByKey($this->response, 'address');
        if (!empty($addressBody[0])) {
            // The old code used to use the array key 0, which at the moment does not
            // exist. But if we do it like this, both should certainly work.
            $addressBody = $addressBody[0];
        }

        return new AddressData(
            $addressBody['address_type'],
            $addressBody['country'],
            $addressBody['formatted'],
            $addressBody['postal_code'],
            $addressBody['region'],
            $addressBody['street_address']
        );
    }

    /**
     * Get resource owner url. Not supported.
     *
     * @return null
     *   Null value.
     */
    public function getUrl()
    {
        return null;
    }

    /**
     * Get resource owner avatar url. Not supported.
     *
     * @return null
     *   Null value.
     */
    public function getAvatarUrl()
    {
        return null;
    }

    /**
     * Set resource owner domain.
     *
     * @param string $domain
     *   Vipps domain.
     *
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
     *   Resource Owner.
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     *   Response array.
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * Verification guard.
     *
     * @throws \League\OAuth2\Client\Provider\Exception\EmailNotVerifiedException
     */
    public function verificationGuard()
    {
        if (!$this->emailVerified()) {
            throw new EmailNotVerifiedException("Email is not verified");
        }
    }
}
