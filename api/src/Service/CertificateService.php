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

    public function get($id){
        $registerdCertificate = $this->commonGroundService->getResource(['component'=>'wari','type'=>'certificate','id'=>$id]);

        $certificate = New Certificate();
        $certificate->setId($registerdCertificate['id']);
        $certificate->setClaim($registerdCertificate['claim']);
        $certificate->setImage($registerdCertificate['image']);
        $certificate->setDocument($registerdCertificate['document']);

        return $certificate;
    }

    public function create(Certificate $certificate)
    {
        // Lets first get a prelimitary id from the register

        // Theorganisation should be dynamic
        $organization = 'https://wrc.zaakonline.nl/organisations/16353702-4614-42ff-92af-7dd11c8eef9f';
        $registerdCertificate = ['person'=>$certificate->getPerson(),'organization'=>$organization];
        $registerdCertificate = $this->commonGroundService->createResource($registerdCertificate, ['component'=>'wari','type'=>'certificates']);

        // Then we can create a certificate
        $certificate = $certificate->setId( Uuid::fromString($registerdCertificate['id']));
        $certificate = $this->createClaim($certificate);
        $certificate = $this->createImage($certificate);
        $certificate = $this->createDocument($certificate);

        // And update the created certificate to the register
        $registerdCertificate['claim'] = $certificate->getClaim();
        $registerdCertificate['image'] = $certificate->getImage();
        $registerdCertificate['document'] = $certificate->getDocument();

        $this->commonGroundService->saveResource($registerdCertificate);

        // Now we can return our freshly created certificate
        return $certificate;
    }

    public function createClaim(Certificate $certificate) {
        // Get person from BRP

        // ^ don't forget to check if $person is a bsn or 'haal centraal' uri!?

        if(filter_var($certificate->getPerson(), FILTER_VALIDATE_URL)){
            $person = $this->commonGroundService->getResource($certificate->getPerson());
        }
        else{
            $person = $this->commonGroundService->getResource(['component'=>'brp','type'=>'ingeschrevenpersonen','id'=>$certificate->getPerson()]);
        }
        //
        if(!$person){
            /* @todo throw error */
        }

        // Create a secret
        $secret = $certificate->getId();

        // Create token payload as a JSON string
        $payload = [
            'iss' => $certificate->getId(),
            'user_id' => $person['id'],
            'user_representation' => $person['@id'],
            'iat' => time()
        ];

        $jwt = $this->createJWT($payload, $secret);

        $certificate->setClaim($jwt);

        return $certificate;
    }

    /*
     * This function creates a QR code for the given claim
     */
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

    /*
     * This function creates the (pdf) document for a given certificate type
     */
    public function createDocument(Certificate $certificate) {
        // Deze willen we later uit het wrc halen

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

        $section->addImage(realpath('../public/images/logo_hoorn.jpg'));

        $header = $section->addHeader();
        $header->addWatermark( realpath('../public/images/cert_hoorn.jpg'), array('marginTop' => 200, 'marginLeft' => 50));

        switch ($certificate->getType()) {
            case "geboorte akte":
                $section->addText(
                    'Geboorte Akte',
                    array('name' => 'Calibri', 'size' => 22, 'color' => 'CA494D', 'bold' => true)
                );
                $section->addText('/* @todo weergeven relevant en geclaimde gegevens */');
                break;
            case "verblijfs geschiedenis":
                $section->addText(
                    'Verblijfs geschiedenis',
                    array('name' => 'Calibri', 'size' => 22, 'color' => 'CA494D', 'bold' => true)
                );
                $section->addText('/* @todo weergeven relevant en geclaimde gegevens */');
                break;
            case "uitreksel brp":
                $section->addText(
                    'Uitreksel BRP',
                    array('name' => 'Calibri', 'size' => 22, 'color' => 'CA494D', 'bold' => true)
                );
                $section->addText('/* @todo weergeven relevant en geclaimde gegevens */');
                break;
            default:
                /* @todo throw error */
        }


        $section->addTextBreak(2);
        $section->addText(
            'Uw gefaliceerde claim',
            array('name' => 'Calibri', 'size' => 15, 'color' => 'CA494D', 'bold' => true)
        );

        $section->addText($certificate->getClaim());

        // Add the iamge
        $section->addTextBreak(2);
        $section->addText(
            'Uw scanbare claim',
            array('name' => 'Tahoma', 'size' => 16, 'color' => 'CA494D', 'bold' => true)
        );
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

    /*
     * This function creates a jwt envelope for a payload and secret
     */
    public function createJWT(array $payload, string $secret) {
        // Create token header as a JSON string
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        // Encode Header to Base64Url String
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // Encode Payload to Base64Url String
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader.'.'.$base64UrlPayload, $secret, true);

        // Encode Signature to Base64Url String
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Return JWT
        return $base64UrlHeader.'.'.$base64UrlPayload.'.'.$base64UrlSignature;
    }
}
