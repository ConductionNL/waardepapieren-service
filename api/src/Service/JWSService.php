<?php

namespace App\Service;

use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\RSAKey;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment as Twig;

class JWSService
{
    private $commonGroundService;
    private $params;
    private $twig;
    private $filesystem;

    public function __construct(CommonGroundService $commonGroundService, ParameterBagInterface $params, Twig $twig)
    {
        $this->commonGroundService = $commonGroundService;
        $this->params = $params;
        $this->twig = $twig;
        $this->filesystem = new Filesystem();
    }

    /**
     * This function verifies if the provided public key is the signature needed for the JWS token
     *
     * @param JWK $key The public jwk key we use to validate the JWS token
     * @param string $token The JWS Token we want to validate
     * @return bool True if valid or false if the public key does not match the token's signature
     */
    public function verifyJWSToken(JWK $key, string $token) {
        $algorithmManager = new AlgorithmManager([
            new RS512(),
        ]);

        $jwsVerifier = new JWSVerifier($algorithmManager);

        $serializerManager = new JWSSerializerManager([new CompactSerializer()]);

        $jws = $serializerManager->unserialize($token);

        return $jwsVerifier->verifyWithKey($jws, $key, 0);
    }

    /**
     * This function checks if the data stored in an array is the same as the data stored in the JWS Token
     *
     * @param string $token The JWS token we compare to the data array
     * @param array $data array we want to compare to the JWS token
     * @return bool True of the JWS token and data array match or false of there is a difference between the two
     */
    public function checkTokenData(string $token, array $data) {
        $json = base64_decode(explode('.', $token)[1]);
        $json = json_decode($json, true)['data'];

        $difference = array_diff(array_map('serialize', $json), array_map('serialize', $data));

        if (empty($difference)) {
            return true;
        } else {
            return false;
        }
    }

}
