<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $clientId = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Wellknown = null;

    #[ORM\Column(length: 255)]
    private ?string $ClientSecret = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getWellknown(): ?string
    {
        return $this->Wellknown;
    }

    public function setWellknown(string $Wellknown): static
    {
        $this->Wellknown = $Wellknown;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->ClientSecret;
    }

    public function setClientSecret(string $ClientSecret): static
    {
        $this->ClientSecret = $ClientSecret;

        return $this;
    }
}
