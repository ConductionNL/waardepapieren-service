<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Payment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Ramsey\Uuid\Uuid;

class PaymentSubscriber implements EventSubscriberInterface
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['payment', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function payment(ViewEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $payment = $event->getControllerResult();
        $route = $event->getRequest()->get('_route');

        // Entity Check
        if (
            !$payment instanceof Payment ||
            $method == 'GET' ||
            $method == 'DELETE' ||
            !$this->params->has('app_shasign')
        ) {
            return;
        }


        if

        $shaSignature = $this->params->get('app_shasign');
        $variables = [];

        $paymentArray = [
            'PSPID'          => 'gemhoorn',
            'orderid'        => Uuid::uuid4()->toString(),
            'amount'         => $payment->getPrice(),
            'currency'       => 'EUR',
            'language'       => 'nl_NL',
            'CN'             => $payment->getName(),
            'TITLE'          => 'Certificate',
            'BGCOLOR'        => 'white',
            'TXTCOLOR'       => 'black',
            'TBLBGCOLOR'     => 'white',
            'TBLTXTCOLOR'    => 'black',
            'BUTTONBGCOLOR'  => 'white',
            'BUTTONTXTCOLOR' => 'black',
            'FONTTYPE'       => 'Verdana',
            'ACCEPTURL'      => $payment->getIngenicoUrl(),
            'EXCEPTIONURL'   => $payment->getIngenicoUrl(),
            'DECLINEURL'     => $payment->getIngenicoUrl(),
            'CANCELURL'      => $payment->getIngenicoUrl(),
        ];

        $variables['keyArray'] = [];

        foreach ($paymentArray as $key => $value) {
            $variables['keyArray'][strtoupper($key)] = $value;
        }

        ksort($variables['keyArray']);
        $variables['signature'] = [];

        foreach ($variables['keyArray'] as $key => $value) {
            $variables['signature'][] = $key . '=' . $value . $shaSignature;
        }

        $paymentArray['SHASign'] = hash('sha256', implode('', $variables['signature']));

        $payment->setConfiguration($paymentArray);
        $payment->setRedirectUrl('https://secure.ogone.com/ncol/test/orderstandard.asp');
        $payment->setOrderId($paymentArray['orderid']);

        return $payment;
    }
}
