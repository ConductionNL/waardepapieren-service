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
 *          "get",
 *          "put",
 *          "delete",
 *          "get_change_logs"={
 *              "path"="/certificates/{id}/change_log",
 *              "method"="get",
 *              "swagger_context" = {
 *                  "summary"="Changelogs",
 *                  "description"="Gets al the change logs for this resource"
 *              }
 *          },
 *          "get_audit_trail"={
 *              "path"="/certificates/{id}/audit_trail",
 *              "method"="get",
 *              "swagger_context" = {
 *                  "summary"="Audittrail",
 *                  "description"="Gets the audit trail for this resource"
 *              }
 *          }
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
     * @example 999993456
     *
     * @Groups({"read", "write"})
     */
    private $person;

    /**
     * @var string The type of this certificate. This can be one of the following: {"geboorte akte", "verblijfs geschiedenis", "uitreksel brp"}.
     *
     * @example geboorte akte
     *
     * @Assert\Choice({"geboorte akte", "verblijfs geschiedenis", "uitreksel brp"})
     *
     * @Groups({"read", "write"})
     */
    private $type;

    /**
     * @var string The claim of this certificate. This is a jwt token.
     *
     * @example eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJic24iOiI5OTk5OTM0NTYiLCJuYW1lIjoiSm9obiBEb2UifQ.xasJlHtinAZUjPSPieYyW7-TF1wW-06x-ph4BOrt3fo
     *
     * @Groups({"read"})
     */
    private $claim;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getClaim(): ?string
    {
        return $this->claim;
    }

    public function setClaim(string $claim): self
    {
        $this->claim = $claim;

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
