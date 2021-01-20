<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Certificate;
use App\Entity\Component;
use App\Service\CertificateService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class CertificateSubscriber implements EventSubscriberInterface
{
    private $certificateService;
    private $serializer;
    private $filesystem;

    public function __construct(SerializerInterface $serializer, CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
        $this->serializer = $serializer;
        $this->filesystem = new Filesystem();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['certificate', EventPriorities::POST_VALIDATE],
        ];
    }

    public function certificate(ViewEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $contentType = $event->getRequest()->headers->get('accept');
        $route = $event->getRequest()->attributes->get('_route');
        $certificate = $event->getControllerResult();

        if (!$contentType) {
            $contentType = $event->getRequest()->headers->get('Accept');
        }

        // Entity Check
        if (!$certificate instanceof Certificate ||
            !$this->filesystem->exists("../cert/{$certificate->getOrganization()}.pem") ||
            !$this->filesystem->exists("cert/{$certificate->getOrganization()}.pem") ||
            !$this->filesystem->exists("../templates/organizations/{$certificate->getOrganization()}.html.twig")) {
            return;
        }

        // Lets support field selection
        if ($fields = $event->getRequest()->get('fields')) {
            $fields = explode(',', $fields);
        }

        // We should also check on entity = component
        if ($method == 'POST') {
            $certificate = $this->certificateService->create($certificate, $fields);
        } elseif ($method == 'GET' && $event->getRequest()->get('id')) {
            $certificate = $this->certificateService->get($event->getRequest()->get('id'));
        }

        switch ($contentType) {
            case 'application/json':
                $renderType = 'json';
                break;
            case 'application/ld+json':
                $renderType = 'jsonld';
                break;
            case 'application/hal+json':
                $renderType = 'jsonhal';
                break;
            default:
                $contentType = 'application/ld+json';
                $renderType = 'jsonld';
        }

        if ($fields != [] && $fields != '') {
            // now we need to overide the normal subscriber
            $response = $this->serializer->serialize(
                $certificate,
                $renderType,
                ['attributes'    => $fields]
            );
        } else {
            $response = $this->serializer->serialize(
                $certificate,
                $renderType,
            );
        }
        // Creating a response
        $response = new Response(
            $response,
            Response::HTTP_OK,
            ['content-type' => $contentType]
        );

        $event->setResponse($response);
    }
}
