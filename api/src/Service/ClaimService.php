<?php

namespace App\Service;

use Jose\Component\Core\Util\RSAKey;
use Jose\Component\KeyManagement\JWKFactory;
use Symfony\Component\Filesystem\Filesystem;

class ClaimService
{
    private $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * This function checks if cert and template files exist for the given rsin.
     *
     * @param string $rsin rsin of the organisation we want to check
     *
     * @return bool true if rsin is already used or false if available
     */
    public function checkRsin(string $rsin)
    {
        if (
            $this->filesystem->exists("cert/{$rsin}.pem") ||
            $this->filesystem->exists("public/cert/{$rsin}.pem") ||
            $this->filesystem->exists("templates/organizations/{$rsin}.html.twig")
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function generates a RSAKey, creates pem files for the public and private key and copies the default template to create one with the rsin.
     *
     * @param string $rsin rsin of the organisation we want to create files for
     */
    public function createOrganization(string $rsin)
    {
        $jwk = JWKFactory::createRSAKey(
            4096, // Size in bits of the key. We recommend at least 2048 bits.
            [
                'alg' => 'RS512',
                'use' => 'alg',
            ]
        );
        $this->filesystem->dumpFile("cert/{$rsin}.pem", RSAKey::createFromJWK($jwk)->toPEM());
        $this->filesystem->dumpFile("public/cert/{$rsin}.pem", RSAKey::createFromJWK($jwk->toPublic())->toPEM());
        $this->filesystem->copy('templates/organizations/default.html.twig', "templates/organizations/{$rsin}.html.twig");
    }
}
