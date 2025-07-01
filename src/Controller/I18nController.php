<?php
namespace App\Controller;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class I18nController
{

    private CacheInterface $cache;

    private TranslatorInterface $translator;

    private int $expire;

    public function __construct(CacheInterface $cache, TranslatorInterface $translator, $environment)
    {
        $this->cache = $cache;
        $this->translator = $translator;
        $this->expire = $environment == 'prod' ? 3600 : 60;
    }

    /**
     * @Route("/i18n/{domain}/{_locale}.js", name="i18n")
     */
    public function i18n($domain, $_locale)
    {
        $res = new Response($this->cache->get('locale.' . $domain . '.' . $_locale, function (ItemInterface $item) use ($domain, $_locale) {
            /** @var \Symfony\Component\Translation\TranslatorBagInterface $trans */
            $messages = [];
            $fallback = $this->translator->getCatalogue($_locale)
                ->getFallbackCatalogue();
            foreach ($fallback->all($domain) as $k => $v) {
                $messages[$k] = $v;
            }
            if ($fallback->getLocale() != $_locale) {
                foreach ($this->translator->getCatalogue($_locale)
                    ->all($domain) as $k => $v) {
                    $messages[$k] = $v;
                }
            }
            $item->expiresAfter($this->expire);

            return sprintf("window.i18n = window.i18n || { locale : '%s', fallback : '%s', domain: {} };
window.i18n.domain['%s'] = %s;
", $_locale, $fallback->getLocale(), $domain, \json_encode($messages, \JSON_UNESCAPED_UNICODE));
        }), 200, [
            'content-type' => 'text/javascript'
        ]);
        $res->setPublic(true);

        $res->setMaxAge($this->expire);

        return $res;
    }
}

