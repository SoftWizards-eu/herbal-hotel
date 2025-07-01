<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use w3des\AdminBundle\Service\CMS;

class LocaleSubscriber implements EventSubscriberInterface
{

    private $defaultLocale;

    private $pageLocales;

    public function __construct(string $defaultLocale, array $pageLocales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->pageLocales = $pageLocales;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if ($event->isMainRequest()) {
            $locale = $request->cookies->get('LOCALE');
            if (!\in_array($locale, $this->pageLocales)) {
                $locale = null;
            }
            if ($locale) {
                $request->setLocale($locale);
            } else {
                $request->setLocale($this->defaultLocale);
            }

        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [
                [
                    'onKernelRequest',
                    20
                ]
            ]
        ];
    }
}