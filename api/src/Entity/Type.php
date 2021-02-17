<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\TypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true},
 *     itemOperations={
 *          "get",
 *          "put"
 *     },
 *     collectionOperations={
 *          "get",
 *          "post"
 *     }
 * )
 * @ORM\Entity(repositoryClass=TypeRepository::class)
 * @ApiFilter(SearchFilter::class, properties={
 *     "organizationConfig.rsin": "exact",
 *     "bsn": "exact"
 * })
 */
class Type
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
     * @var string The name of this Type.
     *
     * @example Akte van geboorte
     *
     * @Assert\Length(
     *     max = 255
     * )
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var string The description of this Type.
     *
     * @example Akte van geboorte
     *
     * @Assert\Length(
     *     max = 255
     * )
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write"})
     */
    private $description;

    /**
     * @var string The actual value of this Type.
     *
     * @example akte_van_geboorte
     *
     * @Assert\Length(
     *     max = 255
     * )
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $value;

//    /**
//     * @Assert\Length(
//     *     max = 255
//     * )
//     * @ORM\Column(type="string", length=255)
//     */
//    private $bsn;

    /**
     * @Groups({"read", "write"})
     * @MaxDepth(1)
     * @ORM\ManyToOne(targetEntity=OrganizationConfig::class, inversedBy="types")
     */
    private $organizationConfig;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

//    public function getBsn(): ?string
//    {
//        return $this->bsn;
//    }
//
//    public function setBsn(string $bsn): self
//    {
//        $this->bsn = $bsn;
//
//        return $this;
//    }

    public function getOrganizationConfig(): ?OrganizationConfig
    {
        return $this->organizationConfig;
    }

    public function setOrganizationConfig(?OrganizationConfig $organizationConfig): self
    {
        $this->organizationConfig = $organizationConfig;

        return $this;
    }
}
