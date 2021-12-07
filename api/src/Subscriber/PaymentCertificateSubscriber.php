<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Certificate;
use App\Entity\Payment;
use App\Service\CertificateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class PaymentCertificateSubscriber implements EventSubscriberInterface
{
    private ParameterBagInterface $params;
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private CertificateService $certificateService;

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em, SerializerInterface $serializer, CertificateService $certificateService)
    {
        $this->params = $params;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->certificateService = $certificateService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['paymentCertificate', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function paymentCertificate(ViewEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->get('_route');

        // Entity Check
        if (
            $route !== 'api_payments_create_certificate_item' ||
            $method !== 'GET'
        ) {
            return;
        }

        $id = $event->getRequest()->get('id');

        $payment = $this->em->getRepository('App\Entity\Payment')->findOneBy(['id' => $id]);

        if (!$payment instanceof Payment) {
            return;
        }

        $shaSignature = $this->params->get('app_shasign');
        if (isset($shaSignature) && $event->getRequest()->query->get('orderID') && $event->getRequest()->query->get('PAYID') && $event->getRequest()->query->get('SHASIGN')) {
            $variables['paramsArray'] = $event->getRequest()->query->all();

            $keyArray = [];
            foreach ($variables['paramsArray'] as $key => $value) {
                // Dont take the hashed shasign
                if ($key !== 'SHASIGN') {
                    $keyArray[strtoupper($key)] = $value;
                }
            }
            ksort($keyArray);

            $signature = [];
            foreach ($keyArray as $key => $value) {
                $signature[] = $key.'='.$value.$shaSignature;
            }
            $hashedSign = strtoupper(hash('sha256', implode('', $signature)));

            if ($hashedSign === $event->getRequest()->query->get('SHASIGN')) {
                $variables['hashResult'] = 'success';
            } else {
                $variables['hashResult'] = 'failed';
            }

            $receivedOrderId = $event->getRequest()->query->get('orderID');
            $orderId = $payment->getOrderId();

            if (isset($variables['paramsArray']['STATUS']) && ($variables['paramsArray']['STATUS'] == '5' ||
                $variables['paramsArray']['STATUS'] == '9' || $variables['paramsArray']['STATUS'] == '51' ||
                $variables['paramsArray']['STATUS'] == '91') && isset($orderId) && isset($receivedOrderId) && $orderId == $receivedOrderId) {
                // Create certificate
                $certificate = new Certificate();
                $certificate->setType($payment->getType());
                $certificate->setOrganization($payment->getOrganization());
                $certificate->setPerson($payment->getPerson());
                $result = $this->certificateService->create($certificate);

                $response = $this->serializer->serialize(
                    $result,
                    'json',
                );

                $response = new Response(
                    $response,
                    Response::HTTP_OK,
                    ['content-type' => 'application/json']
                );

                $event->setResponse($response);
            } else {
                throw new HttpException('500', 'Unsuccessful Payment');
            }
        } else {
            return;
        }
    }
}
