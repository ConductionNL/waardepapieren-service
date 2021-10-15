<?php

namespace App\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/metadata")
 */
class ClaimController
{
    /**
     * @Route("/public_keys/{rsin}")
     */
    public function keyAction(string $rsin): Response
    {
        $filesystem = new Filesystem();
        $key = file_get_contents(__DIR__."/../../public/cert/{$rsin}.pem");
        return new Response(
            json_encode(['key' => $key, 'rsin' => $rsin]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }
}
