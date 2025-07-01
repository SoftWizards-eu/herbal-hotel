<?php
namespace w3des\AdminBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use w3des\AdminBundle\Service\CMS;

class LocaleListener implements EventSubscriberInterface
{

    private $adminLocale;

    private $defaultPageLocale;

    private $availableLocales;

    private TranslatorInterface $translator;

    private CMS $cms;

    public function __construct(CMS $cms, TranslatorInterface $translator, $adminLocale, $defaultPageLocale, $availableLocales)
    {
        $this->adminLocale = $adminLocale;
        $this->defaultPageLocale = $defaultPageLocale;
        $this->availableLocales = $availableLocales;
        $this->translator = $translator;
        $this->cms = $cms;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => array(
                array(
                    'onKernelRequest',
                    15
                )
            )
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (! $event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();
        if (! \preg_match('#^/admin#i', $path)) {
            $this->cms->setLocale($event->getRequest()
                ->getLocale());
            $this->cms->resolveService($request->getHost());
            return;
        }
        $this->cms->resolveService($request->getHost(), $request->getSession()->get('_service'));
        $this->translator->setLocale($this->adminLocale);
        $request->setLocale($this->adminLocale);
        $loc = $request->getSession()->get('_page_locale', $this->defaultPageLocale);
        if (! \in_array($loc, $this->availableLocales)) {
            $loc = $this->defaultPageLocale;
        }
        $request->attributes->set('_page_locale', $loc);
        $request->attributes->set('pageLocale', $loc);
        $this->cms->setLocale($loc);
        // $request->setLocale($loc);
    }
}
