<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TenantRepository;

/**
 * Tenants
 *
 * @ORM\Table(name="tenants", uniqueConstraints={@ORM\UniqueConstraint(name="idx_name", columns={"tenant_name"})}, indexes={@ORM\Index(name="idx_enabled", columns={"enabled"})})
 * @ORM\Entity(repositoryClass=TenantRepository::class)
 */
class Tenants
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * I have to remove the generate value annotation because I don't wanna randomize the db name
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tenant_name", type="string", length=256, nullable=false)
     */
    private $tenantName;

    /**
     * @var string
     *
     * @ORM\Column(name="tenant_db", type="string", length=128, nullable=false)
     */
    private $tenantDb;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $dateCreated = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_updated", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $lastUpdated = 'CURRENT_TIMESTAMP';

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"="b'1'"})
     */
    private $enabled = 'b\'1\'';

    public function getId(): ?int
    {
        return $this->id;
    }
    # added this manually for debugging, also I think it's also required for the repository to construct the entirty? I have yet to disover it
    public function setId( int $id ) {
        $this->id = $id;
    }

    # I don't like how it's a tenant and the field name mentions that again (tenant_name)
    # So I'm going with "name"
    public function getName() {
        return $this->tenantName;
    }

    public function getTenantName(): ?string
    {
        return $this->tenantName;
    }

    public function setTenantName(string $tenantName): self
    {
        $this->tenantName = $tenantName;

        return $this;
    }

    public function getTenantDb(): ?string
    {
        return $this->tenantDb;
    }

    public function setTenantDb(string $tenantDb): self
    {
        $this->tenantDb = $tenantDb;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getLastUpdated(): ?\DateTimeInterface
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTimeInterface $lastUpdated): self
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }


}
