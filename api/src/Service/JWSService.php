<?php

namespace App\Service;

use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Jose\Component\Core\AlgorithmManager;
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

    public function verifyJWSToken($key, $token) {
        $algorithmManager = new AlgorithmManager([
            new RS512(),
        ]);

        $jwsVerifier = new JWSVerifier($algorithmManager);

        $serializerManager = new JWSSerializerManager([new CompactSerializer()]);

        $jws = $serializerManager->unserialize($token);

        return $jwsVerifier->verifyWithKey($jws, $key, 0);
    }

    public function checkTokenData($token, $data) {
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
