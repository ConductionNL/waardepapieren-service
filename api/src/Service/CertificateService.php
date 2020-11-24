<?php

namespace App\Service;

use App\Entity\Certificate;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use PhpOffice\PhpWord\SimpleType\DocProtect;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\DocProtect;
use Symfony\Component\HttpFoundation\Session\Session;
use Ramsey\Uuid\Uuid;

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
        $certificate = $certificate->setId(Uuid::uuid4());
        $certificate = $this->createClaim($certificate);
        $certificate = $this->createImage($certificate);
        $certificate = $this->createDocument($certificate);

        return $certificate;
    }

    public function createClaim(Certificate $certificate) {
        $person = $certificate->getPerson();
        // generate jwt token with this person
        // ^ don't forget to check if $person is a bsn or 'haal centraal' uri!?

        $jwt = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJic24iOiI5OTk5OTM0NTYiLCJuYW1lIjoiSm9obiBEb2UifQ.xasJlHtinAZUjPSPieYyW7-TF1wW-06x-ph4BOrt3fo';

        $certificate->setClaim($jwt);

        return $certificate;
    }

    public function createImage(Certificate $certificate) {

        $configuration['size'] = 300;
        $configuration['margin'] = 10;
        $configuration['writer'] = 'png';

        $qrCode = $this->qrCodeFactory->create($certificate->getClaim(), $configuration);
        $response = new QrCodeResponse($qrCode);

        $certificate->setImage('data:image/png;base64,'.base64_encode($response->getContent()));

        // Save it to a file
        $filename = dirname(__FILE__, 3)."/var/".$certificate->getId().".png";
        $qrCode->writeFile($filename);
        $certificate->setImageLocation($filename);

        return $certificate;
    }

    public function createDocument(Certificate $certificate) {
        // Deze willen we later uit het wrc halen
        $document = 'pdf document';

        // do some rendering

        // Creating the new document...
        $phpWord = new PhpWord();

        // Setup write protection
        $rendererName = Settings::PDF_RENDERER_DOMPDF;
        $rendererLibraryPath = realpath('../vendor/dompdf/dompdf');
        Settings::setPdfRenderer($rendererName, $rendererLibraryPath);

        $documentProtection = $phpWord->getSettings()->getDocumentProtection();
        $documentProtection->setEditing(DocProtect::READ_ONLY);
        $documentProtection->setPassword('myPassword');

        /* Note: any element you append to a document must reside inside of a Section. */

        // Adding an empty Section to the document...
        $section = $phpWord->addSection();
        $section->addText($document);

        // Add the iamge
        $section->addImage($certificate->getImageLocation());

        // Creating the file
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
        $filename = dirname(__FILE__, 3)."/var/".$certificate->getId().".pdf";
        $writer->save($filename);

        $certificate->setDocument('data:'.mime_content_type($filename).';base64,'.base64_encode(file_get_contents($filename)));

        // Lets remove the temporary file
        unlink($filename);
        unlink($certificate->getImageLocation());

        return $certificate;
    }
}
