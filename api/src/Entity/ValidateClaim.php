<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true},
 *     itemOperations={
 *          "get"
 *     },
 *     collectionOperations={
 *          "post"
 *     }
 * )
 * @ORM\Entity()
 */
class ValidateClaim
{
    /**
     * @var UuidInterface The UUID identifier of this resource
     *
     * @example e2984465-190a-4562-829e-a8cca81aa35d
     *
     * @Assert\Uuid
     * @Groups({"read"})
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var array w3c claim
     *
     * @example
     *
     * @Groups({"write"})
     */
    private $claim;

    /**
     * @var bool true or false
     *
     * @example true
     *
     * @Groups({"read"})
     */
    private $validSignature;

    /**
     * @var bool true or false
     *
     * @example true
     *
     * @Groups({"read"})
     */
    private $validBody;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getClaim(): ?array
    {
        return $this->claim;
    }

    public function setClaim(array $claim): self
    {
        $this->claim = $claim;

        return $this;
    }

    public function getValidBody(): ?bool
    {
        return $this->validBody;
    }

    public function setValidBody(bool $validBody): self
    {
        $this->validBody = $validBody;

        return $this;
    }

    public function getValidSignature(): ?bool
    {
        return $this->validSignature;
    }

    public function setValidSignature(bool $validSignature): self
    {
        $this->validSignature = $validSignature;

        return $this;
    }

}
