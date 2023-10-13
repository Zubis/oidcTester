<?php

namespace App\Security\Provider;

use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class KeycloakProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string|null
     */
    protected $discoveryUrl = null;
    protected $discoveryContent = null;

    private function getDiscoveryContent(string $property): string
    {
        if (!$this->discoveryUrl) {
            throw new Exception('Discovery URL is empty');
        }

        if (!$this->discoveryContent) {
            $response = $this->httpClient->request('GET', $this->discoveryUrl);
            $this->discoveryContent = json_decode($response->getBody()->getContents(), true);
        }

        return $this->discoveryContent[$property];
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->getDiscoveryContent('authorization_endpoint');
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getDiscoveryContent('token_endpoint');
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getDiscoveryContent('userinfo_endpoint');
    }

    public function getLogoutUrl(): string
    {
        return $this->getDiscoveryContent('end_session_endpoint');
    }

    public function getDefaultScopes(): array
    {
        return ['email'];
    }

    public function checkResponse(ResponseInterface $response, $data): void
    {
        if (!empty($data['error'])) {
            $error = $data['error'].': '.$data['error_description'];
            throw new IdentityProviderException($error, 500, $data);
        }
    }

    public function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new KeycloakResourceOwner($response);
    }
}
