<?php

namespace App\Service;

use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Jose\Component\Core\Util\RSAKey;
use Jose\Component\KeyManagement\JWKFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment as Twig;

class ClaimService
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

    public function checkRsin($rsin) {

        if (
            $this->filesystem->exists("cert/{$rsin}.pem") ||
            $this->filesystem->exists("public/cert/{$rsin}.pem")
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function createOrganization($rsin) {

        $jwk = JWKFactory::createRSAKey(
            4096, // Size in bits of the key. We recommend at least 2048 bits.
            [
                'alg' => 'RS512',
                'use' => 'alg'
            ]);
        $this->filesystem->dumpFile("cert/{$rsin}.pem", RSAKey::createFromJWK($jwk)->toPEM());
        $this->filesystem->dumpFile("public/cert/{$rsin}.pem", RSAKey::createFromJWK($jwk->toPublic())->toPEM());
        $this->filesystem->copy('templates/organizations/default.html.twig', "templates/organizations/{$rsin}.html.twig");
    }

}
