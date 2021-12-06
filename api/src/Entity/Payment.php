<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
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
 *          "create_certificate"={
 *              "method"="GET",
 *              "path"="/payments/{id}/certificate"
 *          },
 *     },
 *     collectionOperations={
 *          "post",
 * 	        "get"
 *     }
 * )
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 * 
 * @ApiFilter(BooleanFilter::class)
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(DateFilter::class, strategy=DateFilter::EXCLUDE_NULL)
 * @ApiFilter(SearchFilter::class)
 */
class Payment
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
     * @var string The person for this certificate, this can be a bsn or 'haal centraal' uri.
     *
     * @example https://dev.zuid-drecht.nl/api/v1/brp/ingeschrevenpersonen/999992016
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $person;

    /**
     * @var string Name of the person.
     *
     * @example Jan Klaas
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string The type of this certificate. This can be one of the following: {"geboorte akte", "verblijfs geschiedenis", "uitreksel brp"}.
     *
     * @example geboorte akte
     *
     * @Assert\Choice({
     *     "akte_van_geboorte",
     *     "akte_van_huwelijk",
     *     "akte_van_overlijden",
     *     "akte_van_registratie_van_een_partnerschap",
     *     "akte_van_omzetting_van_een_huwelijk_in_een_registratie_van_een_partnerschap",
     *     "akte_van_omzetting_van_een_registratie_van_een_partnerschap",
     *     "haven",
     *     "verklaring_diplomas",
     *     "verklaring_inkomen",
     *     "verklaring_studieschuld",
     *     "verklaring_van_huwelijksbevoegdheid",
     *     "verklaring_van_in_leven_zijn",
     *     "verklaring_van_nederlandershap",
     *     "uittreksel_basis_registratie_personen",
     *     "uittreksel_registratie_niet_ingezetenen",
     *     "historisch_uittreksel_basis_registratie_personen",
     * })
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @var string The organizations that is requested to "sign" this claim
     *
     * @example https://example.com/organization
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $organization;

    /**
     * @var int The organizations that is requested to "sign" this claim
     *
     * @example https://example.com/organization
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", length=255)
     */
    private $price;

    /**
     * @var array The organizations that is requested to "sign" this claim
     *
     * @example https://example.com/organization
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $configuration = [];

    /**
     * @var string Status of the payment
     *
     * @example unpaid
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    /**
     * @var string The organizations that is requested to "sign" this claim
     *
     * @example https://example.com/organization
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", nullable=true)
     */
    private $redirectUrl;

    /**
     * @var string The organizations that is requested to "sign" this claim
     *
     * @example https://example.com/organization
     *
     * @Groups({"read", "write"})
     */
    private $ingenicoUrl;

    /**
     * @var string The organizations that is requested to "sign" this claim
     *
     * @example https://example.com/organization
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", nullable=true)
     */
    private $orderId;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getPerson(): ?string
    {
        return $this->person;
    }

    public function setPerson(string $person): self
    {
        $this->person = $person;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getIngenicoUrl(): ?string
    {
        return $this->ingenicoUrl;
    }

    public function setIngenicoUrl(string $ingenicoUrl): self
    {
        $this->ingenicoUrl = $ingenicoUrl;

        return $this;
    }
}
