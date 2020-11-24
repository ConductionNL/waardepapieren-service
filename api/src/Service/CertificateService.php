<?php

namespace App\Service;

use App\Entity\Certificate;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CertificateService
{
    private $em;
    private $commonGroundService;
    private $qrCodeFactory;
    private $params;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService, ParameterBagInterface $params, QrCodeFactoryInterface $qrCodeFactory)
    {
        $this->em = $em;
        $this->commonGroundService = $commonGroundService;
        $this->params = $params;
        $this->qrCodeFactory = $qrCodeFactory;
    }

    public function handle(Certificate $certificate)
    {
        $person = $certificate->getPerson();

        $claim = $this->createClaim($person);
        $image = $this->createImage($claim);
        $document = $this->createDocument($image);

        $certificate->setClaim($claim);
        $certificate->setImage($image);
        $certificate->setDocument($document);

        return $certificate;
    }

    public function createClaim($person) {
        // generate jwt token with this person
        // ^ don't forget to check if $person is a bsn or 'haal centraal' uri!?
        $jwt = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJic24iOiI5OTk5OTM0NTYiLCJuYW1lIjoiSm9obiBEb2UifQ.xasJlHtinAZUjPSPieYyW7-TF1wW-06x-ph4BOrt3fo';

        return $jwt;
    }

    public function createImage($claim) {

        $configuration['size'] = 300;
        $configuration['margin'] = 10;

        $qrCode = $this->qrCodeFactory->create($claim, $configuration);
        $response = new QrCodeResponse($qrCode);

        return base64_encode($response->getContent());
    }

    public function createDocument($image) {
        $document = 'pdf document';

        return $document;
    }
}
