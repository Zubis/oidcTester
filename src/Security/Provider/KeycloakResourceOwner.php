<?php

namespace App\Security\Provider;

use DateTime;
use Exception;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class KeycloakResourceOwner implements ResourceOwnerInterface
{
    /**
     * Creates new resource owner.
     */
    public function __construct(
        /**
         * Raw response.
         */
        protected array $response = []
    ) {
    }

    /**
     * Get resource owner id.
     */
    public function getId(): ?string
    {
        return $this->response['sub'] ?? null;
    }

    /**
     * Get resource owner email.
     */
    public function getEmail(): ?string
    {
        return $this->response['email'] ?? null;
    }

    /**
     * Get resource owner name.
     */
    public function getName(): ?string
    {
        return $this->response['name'] ?? null;
    }

    public function getRoles(string $client): array
    {
        if (
            isset($this->response['resource_access'])
            && isset($this->response['resource_access'][$client])
            && isset($this->response['resource_access'][$client]['roles'])
        ) {
            return $this->response['resource_access'][$client]['roles'];
        }

        return [];
    }

    public function isProfileDeleted(): bool
    {
        return $this->response['individual_profile_deleted'] ?? false;
    }

    /**
     * Return all of the owner details available as an array.
     */
    public function toArray(): array
    {
        return $this->response;
    }

    public function getFirstName(): ?string
    {
        return $this->response['given_name'] ?? null;
    }

    public function getLastName(): ?string
    {
        return $this->response['family_name'] ?? null;
    }

    public function getBirthCountry(): ?string
    {
        return $this->response['ro_birth_country'] ?? $this->response['birth_country'] ?? null;
    }

    public function getBirthCity(): ?string
    {
        return $this->response['ro_birth_city'] ?? $this->response['birth_city'] ?? null;
    }

    public function getBirthDate(): ?DateTime
    {
        return
            isset($this->response['ro_birth_date']) ?
                new DateTime($this->response['ro_birth_date']) :
                (
                    isset($this->response['birth_date']) ?
                    new DateTime($this->response['birth_date']) :
                    null
                );
    }

    /**
     * On utilise les termes gender et civility pour augmenter les chances de compatibilité.
     */
    public function getCivility(): ?string
    {
        return $this->response['ro_gender'] ?? $this->response['gender'] ?? $this->response['ro_civility'] ?? $this->response['civility'] ?? null;
    }

    public function getIdentityProvider(): ?string
    {
        return $this->response['identity_provider_name'] ?? null;
    }

    public function getCreatedDate(): ?DateTime
    {
        if (!isset($this->response['created_timestamp'])) {
            return null;
        }

        try {
            $createdDate = new DateTime();
            $createdDate->setTimestamp((int) substr((string) $this->response['created_timestamp'], 0, -3));

            return $createdDate;
        } catch (Exception) {
            return null;
        }
    }

    public function getConsentTosNotAccepted(): bool
    {
        return $this->response['consent_tos_not_accepted'] ?? false;
    }
}
