<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\ValidateClaim;
use App\Service\JWSService;
use Jose\Component\KeyManagement\JWKFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ValidateClaimSubscriber implements EventSubscriberInterface
{
    private $JWSService;

    public function __construct(JWSService $JWSService)
    {
        $this->JWSService = $JWSService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['validate', EventPriorities::POST_VALIDATE],
        ];
    }

    public function validate(ViewEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $claimToValidate = $event->getControllerResult();
        if ($method == 'POST' && $claimToValidate instanceof ValidateClaim) {
            $claim = $claimToValidate->getClaim();
            $claimToValidate->setValidBody($this->JWSService->checkTokenData($claim['proof']['jws'], $claim['credentialSubject']));
            $jwk = JWKFactory::createFromKeyFile(str_replace($event->getRequest()->getSchemeAndHttpHost().'/', '', $claim['proof']['verificationMethod']));
            $claimToValidate->setValidSignature($this->JWSService->verifyJWSToken($jwk, $claim['proof']['jws']));

            return $claimToValidate;
        }
    }
}
