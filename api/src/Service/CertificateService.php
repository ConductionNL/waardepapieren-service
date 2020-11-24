<?php

namespace App\Service;

use App\Entity\Certificate;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use Symfony\Component\HttpFoundation\Session\Session;

class CertificateService
{
    private $commonGroundService;
    private $params;
    private $qrCodeFactory;

    public function __construct(CommonGroundService $commonGroundService, ParameterBagInterface $params, QrCodeFactoryInterface $qrCodeFactory)
    {
        $this->commonGroundService = $commonGroundService;
        $this->params = $params;
        $this->qrCodeFactory = $qrCodeFactory;
    }

    public function handle(Certificate $certificate)
    {
        $person = $certificate->getPerson();

        $certificate = $this->createClaim($certificate);
        $certificate = $this->createImage($certificate);
        //$certificate = $this->createDocument($certificate);

        return $certificate;
    }

    public function createClaim(Certificate $certificate) {
        // generate jwt token with this person
        // ^ don't forget to check if $person is a bsn or 'haal centraal' uri!?
        $jwt = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJic24iOiI5OTk5OTM0NTYiLCJuYW1lIjoiSm9obiBEb2UifQ.xasJlHtinAZUjPSPieYyW7-TF1wW-06x-ph4BOrt3fo';

        $certificate->setClaim($jwt);

        return $certificate;
    }

    public function createImage(Certificate $certificate) {

        $configuration['size'] = 300;
        $configuration['margin'] = 10;

        $qrCode = $this->qrCodeFactory->create($certificate->getClaim(), $configuration);
        $response = new QrCodeResponse($qrCode);

        $certificate->setImage(base64_encode($response->getContent()));

        return $certificate;
    }

    public function createDocument(Certificate $certificate) {
        // Deze willen we later uit het wrc halen
        $document = 'pdf document';

        // do some rendering

        // Creating the new document...
        $phpWord = new PhpWord();

        // Setup write protection
        //$phpWord->getSettings()->setPdfRendererPath(dirname(__FILE__)."/../../Office/tcpdf");
        //$phpWord->getSettings()->setPdfRendererName('TCPDF');

        //$documentProtection = $phpWord->getSettings()->getDocumentProtection();
        //$documentProtection->setEditing(DocProtect::READ_ONLY);
        //$documentProtection->setPassword('myPassword');

        /* Note: any element you append to a document must reside inside of a Section. */

        // Adding an empty Section to the document...
        $section = $phpWord->addSection();
        $section->addText($document);

        // Creating the file
        $session = new Session();

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
        $filename = dirname(__FILE__, 3)."/var/".$session->getId().".pdf";
        $writer->save($filename);

        $certificate->setDocument(base64_encode(file_get_contents($filename)));

        // Lets remove the temporary file
        unlink($filename);

        return $certificate;
    }
}
