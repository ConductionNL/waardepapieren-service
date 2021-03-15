<?php

namespace App\Service;

use App\Entity\Certificate;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use DateTimeZone;
use Dompdf\Dompdf;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment as Twig;

class CertificateService
{
    private $commonGroundService;
    private $qrCodeFactory;
    private $twig;
    private $filesystem;
    private $requestStack;

    public function __construct(CommonGroundService $commonGroundService, QrCodeFactoryInterface $qrCodeFactory, Twig $twig, RequestStack $requestStack)
    {
        $this->commonGroundService = $commonGroundService;
        $this->qrCodeFactory = $qrCodeFactory;
        $this->twig = $twig;
        $this->filesystem = new Filesystem();
        $this->requestStack = $requestStack;
    }

    /**
     * This function retrieves an existing certificate from the database.
     *
     * @param Uuid $id The uuid of the certificate we want to retrieve
     *
     * @return Certificate Retrieved certificate object
     */
    public function get($id)
    {
        $registerdCertificate = $this->commonGroundService->getResource(['component'=>'wari', 'type'=>'certificate', 'id'=>$id]);

        $certificate = new Certificate();
        $certificate->setId($registerdCertificate['id']);
        $certificate->setClaim($registerdCertificate['claim']);
        $certificate->setImage($registerdCertificate['image']);
        $certificate->setDocument($registerdCertificate['document']);

        return $certificate;
    }

    /**
     * This function creates all the different data types of the certificate.
     *
     * @param Certificate $certificate The certificate object
     * @param array       $fields
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     *
     * @return Certificate the completed certificate object
     */
    public function create(Certificate $certificate, $fields = [])
    {
        // Get person from BRP

        // ^ don't forget to check if $person is a bsn or 'haal centraal' uri!?

        if (filter_var($certificate->getPerson(), FILTER_VALIDATE_URL)) {
            $person = $this->commonGroundService->getResource($certificate->getPerson());
        } else {
            $person = $this->commonGroundService->getResource(['component'=>'brp', 'type'=>'ingeschrevenpersonen', 'id'=>$certificate->getPerson()]);
        }

        // Lets check if this actually brought us a person
        if (!$person) {
            /* @todo throw error */
        } else {
            $person = $certificate->setPersonObject($person);
        }

        // Theorganisation should be dynamic
        $organization = 'https://wrc.zaakonline.nl/organisations/16353702-4614-42ff-92af-7dd11c8eef9f';
        $registerdCertificate = ['person'=>$certificate->getPerson(), 'organization'=>$organization, 'type'=>$certificate->getType()];
        $registerdCertificate = $this->commonGroundService->createResource($registerdCertificate, ['component'=>'wari', 'type'=>'certificates']);

        // Then we can create a certificate
        $certificate = $certificate->setId(Uuid::fromString($registerdCertificate['id']));
        $certificate = $this->createClaim($certificate);

        $certificate = $this->createImage($certificate);
        $certificate = $this->createDocument($certificate);

        // And update the created certificate to the register
        $registerdCertificate['type'] = $certificate->getType();
        $registerdCertificate['image'] = $certificate->getImage();
        $registerdCertificate['document'] = $certificate->getDocument();

        $this->commonGroundService->saveResource($registerdCertificate);

        // Now we can return our freshly created certificate
        return $certificate;
    }

    /**
     * This function creates the claim based on the type defined in the certificate object.
     *
     * @param Certificate $certificate The certificate object
     *
     * @throws \Exception
     *
     * @return Certificate The modified certificate object
     */
    public function createClaim(Certificate $certificate)
    {

        // Lets add data to this claim
        $claimData = $certificate->getClaimData();

        switch ($certificate->getType()) {
            case 'akte_van_geboorte':

                if (array_key_exists('geboorte', $certificate->getPersonObject())) {
                    $claimData['geboorte'] = [];
                    $claimData['geboorte']['datum'] = $certificate->getPersonObject()['geboorte']['datum']['datum'];
                    $claimData['geboorte']['land'] = $certificate->getPersonObject()['geboorte']['land']['omschrijving'];
                    $claimData['geboorte']['plaats'] = $certificate->getPersonObject()['geboorte']['plaats']['omschrijving'];
                } else {
                    $claimData['overlijden'] = ['indicatieGeboorte'=>false];
                }

                break;
            case 'akte_van_huwelijk':

                break;
            case 'akte_van_registratie_van_een_partnerschap':

                break;

            case 'akte_van_overlijden':

                if (array_key_exists('overlijden', $certificate->getPersonObject())) {
                    $claimData['overlijden'] = $certificate->getPersonObject()['overlijden'];
                } else {
                    $claimData['overlijden'] = ['indicatieOverleden'=>false];
                }

                break;
            case 'akte_van_omzetting_van_een_registratie_van_een_partnerschap':

                break;
            case 'verklaring_van_huwelijksbevoegdheid':

                break;
            case 'verklaring_van_in_leven_zijn':

                if (array_key_exists('overlijden', $certificate->getPersonObject())) {
                    $claimData['overlijden'] = $certificate->getPersonObject()['overlijden'];
                } else {
                    $claimData['overlijden'] = ['indicatieOverleden'=>false];
                }

                break;
            case 'verklaring_van_nederlandershap':

                if (array_key_exists('nationaliteiten', $certificate->getPersonObject())) {
                    $claimData['nationaliteiten'] = $certificate->getPersonObject()['nationaliteiten'];
                } else {
                    $claimData['nationaliteiten'] = ['nederlandschap'=>false];
                }

                break;
            case 'uittreksel_basis_registratie_personen':

                if (array_key_exists('naam', $certificate->getPersonObject())) {
                    $claimData['naam'] = $certificate->getPersonObject()['naam'];
                    unset($claimData['naam']['@id']);
                    unset($claimData['naam']['@type']);
                    unset($claimData['naam']['uuid']);
                }

                if (array_key_exists('geboorte', $certificate->getPersonObject())) {
                    $claimData['geboorte'] = [];
                    $claimData['geboorte']['datum'] = $certificate->getPersonObject()['geboorte']['datum']['datum'];
                    $claimData['geboorte']['land'] = $certificate->getPersonObject()['geboorte']['land']['omschrijving'];
                    $claimData['geboorte']['plaats'] = $certificate->getPersonObject()['geboorte']['plaats']['omschrijving'];
                }
                if (array_key_exists('verblijfplaats', $certificate->getPersonObject())) {
                    $claimData['verblijfplaats'] = $certificate->getPersonObject()['verblijfplaats'];
                    unset($claimData['verblijfplaats']['@id']);
                    unset($claimData['verblijfplaats']['@type']);
                    unset($claimData['verblijfplaats']['uuid']);
                }

                break;
            case 'uittreksel_registratie_niet_ingezetenen':

                break;
            case 'historisch_uittreksel_basis_registratie_personen':

                if (array_key_exists('naam', $certificate->getPersonObject())) {
                    $claimData['naam'] = $certificate->getPersonObject()['naam'];
                    unset($claimData['naam']['@id']);
                    unset($claimData['naam']['@type']);
                    unset($claimData['naam']['uuid']);

                    $claimData['naam'] = array_filter($claimData['naam'], "unsetEmpty");
                }

                if (array_key_exists('geboorte', $certificate->getPersonObject())) {
                    $claimData['geboorte'] = [];
                    $claimData['geboorte']['datum'] = $certificate->getPersonObject()['geboorte']['datum']['datum'];
                    $claimData['geboorte']['land'] = $certificate->getPersonObject()['geboorte']['land']['omschrijving'];
                    $claimData['geboorte']['plaats'] = $certificate->getPersonObject()['geboorte']['plaats']['omschrijving'];

                    $claimData['geboorte'] = array_filter($claimData['geboorte'], "unsetEmpty");
                }
                if (array_key_exists('verblijfplaats', $certificate->getPersonObject())) {
                    $claimData['verblijfplaats'] = $certificate->getPersonObject()['verblijfplaats'];
                    $claimData['verblijfplaats']['van'] = '2021-01-01';
                    unset($claimData['verblijfplaats']['@id']);
                    unset($claimData['verblijfplaats']['@type']);
                    unset($claimData['verblijfplaats']['uuid']);

                    $claimData['verblijfplaats'] = array_filter($claimData['verblijfplaats'], "unsetEmpty");
                }

                $claimData['verblijfplaatsHistorish'] = [
                    ['van'              => '2010-01-01',
                        'tot'           => '2010-12-31',
                        'verblijfplaats'=> ['huisnummer'=>60, 'postcode'=>'9876 ZZ', 'straatnaam'=>'Straathofjesweg', 'woonplaatsnaam'=>'Medemblik'],
                    ],
                    ['van'              => '2011-01-01',
                        'tot'           => '2011-12-31',
                        'verblijfplaats'=> ['huisnummer'=>61, 'postcode'=>'9876 ZZ', 'straatnaam'=>'Straathofjesweg', 'woonplaatsnaam'=>'Hoorn'],
                    ],
                    ['van'              => '2012-01-01',
                        'tot'           => '2020-12-31',
                        'verblijfplaats'=> ['huisnummer'=>62, 'postcode'=>'9876 ZZ', 'straatnaam'=>'Straathofjesweg', 'woonplaatsnaam'=>'Zaanstad'],
                    ],
                ];
                break;
            default:
                /*@todo throw error */
        }
        $certificate->setW3c($this->w3cClaim($claimData, $certificate));
        $claimData['persoon'] = $certificate->getPersonObject()['burgerservicenummer'];
        $claimData['doel'] = $certificate->getType();


        $certificate->setClaimData($claimData);

        // Create token payload as a JSON string
        $claim = [
            'iss'                 => $certificate->getId(),
            'user_id'             => $certificate->getPersonObject()['id'],
            'user_representation' => $certificate->getPersonObject()['@id'],
            'claim_data'          => $certificate->getClaimData(),
            'validation_uri'      => 'https://waardepapieren-gemeentehoorn.commonground.nu/api/v1/waar',
            'iat'                 => time(),
        ];
        $certificate = $certificate->setClaim($claim);

        // Create token payload as a JSON string
        $discipl = [
            'claimData' => [
                'did:discipl:ephemeral:crt:4c86faf535029c8cf4a371813cc44cb434875b18'=> [
                    'link:discipl:ephemeral:tEi6K3mPRmE6QRf4WvpxY1hQgGmIG7uDV85zQILQNSCnQjAZPg2mj4Fbok/BHL9C8mFJQ1tCswBHBtsu6NIESA45XnN13pE+nLD6IPOeHx2cUrObxtzsqLhAy4ZXN6eDpZDmqnb6ymELUfXu/D2n4rL/t9aD279vqjFRKgBVE5WsId9c6KEYA+76mBQUBoJr8sF7w+3oMjzKy88oW693I3Keu+cdl/9sRCyYAYIDzwmg3A6n8t9KUpsBDK1b6tNznA6qoiN9Zb4JZ7rpq6lnVpyU5pyJjD+p9DiWgIYsVauJy8WOcKfNWkeOomWez0of2o+gu9xf+VLzcX3MSiAfZA=='=> $certificate->getClaimData(),
                ],
            ],
            'metadata' => ['cert'=>'zuid-drecht.nl:8080'],
        ];
        $certificate = $certificate->setDiscipl($discipl);

        // Create token payload as a JSON string
        $certificate = $certificate->setIrma($discipl);

        $jwt = $this->createJWT($certificate);

        $certificate->setJWT($jwt);

        return $certificate;
    }

    /**
     * This function creates a QR code for the given claim.
     *
     * @param Certificate $certificate The certificate object
     *
     * @return Certificate The modified certificate object
     */
    public function createImage(Certificate $certificate)
    {

        // First we need set a bit of basic configuration
        $configuration['size'] = 300;
        $configuration['margin'] = 0;
        $configuration['writer'] = 'png';
        $configuration['errorCorrectionLevel'] = 'low';

        // Then we need to render the QR code
        $qrCode = $this->qrCodeFactory->create($certificate->getJwt(), $configuration);
        $response = new QrCodeResponse($qrCode);

        // And finnaly we need to set the result on the certificate resource
        $certificate->setImage('data:image/png;base64,'.base64_encode($response->getContent()));

        return $certificate;
    }


    /**
     * Filter function for removing empty items
     *
     */
    public function unsetEmpty($var)
    {
        return ($var !== NULL  && $var !== "");
    }

    /**
     * This function creates the (pdf) document for a given certificate type.
     *
     * @param Certificate $certificate The certificate object
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     *
     * @return Certificate The modified certificate object
     */
    public function createDocument(Certificate $certificate)
    {

        // First we need the HTML  for the template
        $html = $this->twig->render('certificates/'.$certificate->getType().'.html.twig', [
            'qr'     => $certificate->getImage(),
            'claim'  => $certificate->getClaim(),
            'person' => $certificate->getPersonObject(),
            'base'   => '/organizations/'.$certificate->getOrganization().'.html.twig',
        ]);

        // Then we need to render the template
        $dompdf = new DOMPDF();
        $dompdf->loadHtml($html);
        $dompdf->render();

        // And finnaly we need to set the result on the certificate resource
        $certificate->setDocument('data:application/pdf;base64,'.base64_encode($dompdf->output()));

        return $certificate;
    }

    /**
     * This function generates a jwt token using the claim that's available from the certificate object.
     *
     * @param Certificate $certificate The certificate object
     *
     * @return string The generated jwt token
     */
    public function createJWT(Certificate $certificate)
    {

        // Create a secret
        $secret = $certificate->getId();

        // Create a payload
        $payload = $certificate->getClaim();

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

    /**
     * This function generates a claim based on the w3c structure.
     *
     * @param array       $data        The data used to create the claim
     * @param Certificate $certificate The certificate object
     *
     * @throws \Exception
     *
     * @return array The generated claim
     */
    public function w3cClaim(array $data, Certificate $certificate)
    {
        $now = new \DateTime('now', new DateTimeZone('Europe/Amsterdam'));
        $array = [];
        $array['@context'] = ['https://www.w3.org/2018/credentials/v1', 'https://www.w3.org/2018/credentials/examples/v1'];
        $array['id'] = $certificate->getId();
        $array['type'] = ['VerifiableCredential', $certificate->getType()];
        $array['issuer'] = $certificate->getOrganization();
        $array['inssuanceDate'] = $now->format('H:i:s d-m-Y');
        $array['credentialSubject']['id'] = $certificate->getPersonObject()['burgerservicenummer'];
        foreach ($data as $key => $value) {
            $array['credentialSubject'][$key] = $value;
        }
        $array['proof'] = $this->createProof($certificate, $array);

        return $array;
    }

    /**
     * This function creates a proof.
     *
     * @param Certificate $certificate the certificate object
     * @param array       $data        the data that gets stored in the jws token of the proof
     *
     * @return array proof
     */
    public function createProof(Certificate $certificate, array $data)
    {
        $proof = [];
        $proof['type'] = 'RsaSignature';
        $proof['created'] = date('H:i:s d-m-Y', filectime("cert/{$certificate->getOrganization()}.pem"));
        $proof['proofPurpose'] = 'assertionMethode';
        $proof['verificationMethod'] = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost()."/cert/{$certificate->getOrganization()}.pem";
        $proof['jws'] = $this->createJWS($certificate, $data['credentialSubject']);

        return $proof;
    }

    /**
     * This function generates a JWS token with the RS512 algorithm.
     *
     * @param Certificate $certificate the certificate object
     * @param array       $data        the data that gets stored in the jws token
     *
     * @return string Generated JWS token.
     */
    public function createJWS(Certificate $certificate, array $data)
    {
        $algorithmManager = new AlgorithmManager([
            new RS512(),
        ]);
        $jwk = JWKFactory::createFromKeyFile(
            "../cert/{$certificate->getOrganization()}.pem"
        );
        $jwsBuilder = new \Jose\Component\Signature\JWSBuilder($algorithmManager);
        $payload = json_encode([
            'iat'  => time(),
            'nbf'  => time(),
            'exp'  => time() + 3600,
            'iss'  => $certificate->getId(),
            'aud'  => $certificate->getPersonObject()['burgerservicenummer'],
            'data' => $data,
        ]);
        $jws = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($jwk, ['alg' => 'RS512'])
            ->build();
        $serializer = new CompactSerializer();

        return $serializer->serialize($jws, 0);
    }
}
