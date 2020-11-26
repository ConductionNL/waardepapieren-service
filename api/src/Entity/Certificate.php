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
class Certificate
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
     */
    private $person;

    /**
     * @var array The person for this certificate as an object from the BRP
     *
     * @example https://dev.zuid-drecht.nl/api/v1/brp/ingeschrevenpersonen/999992016
     *
     */
    private $personObject;

    /**
     * @var string The type of this certificate. This can be one of the following: {"geboorte akte", "verblijfs geschiedenis", "uitreksel brp"}.
     *
     * @example geboorte akte
     *
     * @Assert\Choice({
     *     "akte_van_geboorte",
     *     "akte_van_huwelijk",
     *     "akte_van_ooverlijden",
     *     "akte_van_registratie_van_een_partnerschap",
     *     "akte_van_omzetting_van_een_huwelijk_in_een_registratie_van_een_partnerschap",
     *     "akte_van_omzetting_van_een_registratie_van_een_partnerschap",
     *     "verklaring_van_huwelijksbevoegdheid",
     *     "verklaring_van_in_leven_zijn",
     *     "verklaring_van_nederlandershap",
     *     "uittreksel_basis_registratie_personen",
     *     "uittreksel_registratie_niet_ingezetenen",
     *     "uittreksel_registratie_niet_ingezetenen",
     *     "historisch_uittreksel_basis_registratie_personen",
     * })
     *
     * @Groups({"read", "write"})
     */
    private $type;

    /**
     * @var array The claim of this certificate as an json object
     *
     * @example eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJic24iOiI5OTk5OTM0NTYiLCJuYW1lIjoiSm9obiBEb2UifQ.xasJlHtinAZUjPSPieYyW7-TF1wW-06x-ph4BOrt3fo
     *
     * @Groups({"read"})
     */
    private $claim;

    /**
     * @var string The claim of this certificate as a jwt token.
     *
     * @example eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJic24iOiI5OTk5OTM0NTYiLCJuYW1lIjoiSm9obiBEb2UifQ.xasJlHtinAZUjPSPieYyW7-TF1wW-06x-ph4BOrt3fo
     *
     * @Groups({"read"})
     */
    private $jwt;

    /**
     * @var string The image of this certificate. This is a qr-code.
     *
     * @example ...
     *
     * @Groups({"read"})
     */
    private $image;

    /**
     */
    private $imageLocation;

    /**
     * @var string The document of this certificate. This is a pdf.
     *
     * @example ...
     *
     * @Groups({"read"})
     */
    private $document;

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

    public function getPersonObject(): ?array
    {
        return $this->personObject;
    }

    public function setPersonObject(array $personObject): self
    {
        $this->personObject = $personObject;

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

    public function getClaim(): ?array
    {
        return $this->claim;
    }

    public function setClaim(array $claim): self
    {
        $this->claim = $claim;

        return $this;
    }

    public function getJWT(): ?string
    {
        return $this->jwt;
    }

    public function setJWT(string $jwt): self
    {
        $this->jwt = $jwt;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageLocation(): ?string
    {
        return $this->imageLocation;
    }

    public function setImageLocation(string $imageLocation): self
    {
        $this->imageLocation = $imageLocation;

        return $this;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(string $document): self
    {
        $this->document = $document;

        return $this;
    }
}
