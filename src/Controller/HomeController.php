<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use w3des\AdminBundle\Service\ModuleRegistry;
use Symfony\Component\HttpFoundation\Response;
use w3des\AdminBundle\Model\NodeView;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Entity\Node;

class HomeController extends AbstractController
{
    private $pageLocales;

    public function __construct($pageLocales)
    {
        $this->pageLocales = $pageLocales;
    }

    public function home(Request $request, ModuleRegistry $registry, Nodes $nodes)
    {
        if ($request->isMethod('post') && $request->request->has('contact')) {
            $v = new NodeView($nodes);
            $v->model = new Node();
            $v->model->setType('module.contact_form');
            $res = $registry->getModule('module.contact_form')->frontend($request, $v, []);
            if ($res instanceof Response && $res->getStatusCode() !=200) {
                return $res;
            }
        }
        if ($request->isMethod('post') && ($request->request->has('service') || $request->request->has('offer'))) {
            $v = new NodeView($nodes);
            $v->model = new Node();
            $v->model->setType('module.offer_form');
            $res = $registry->getModule('module.offer_form')->frontend($request, $v, []);
            if ($res instanceof Response && $res->getStatusCode() !=200) {
                return $res;
            }
        }
        return $this->render('home/index.html.twig', []);
    }

    public function root(Request $request)
    {
        if ($this->isBot()) {
            return $this->home($request);
        }
        if ($request->cookies->has('LOCALE')) {
            if ($request->getLocale() != 'pl') {
                return $this->redirectToRoute('homepage', [
                    '_locale' => $request->getLocale()
                ]);
            }
            return $this->home($request);
        }

        $negotiator = new \Negotiation\LanguageNegotiator();
        $lang = $negotiator->getBest($request->headers->get('accept-language'), $this->pageLocales);
        $lang = $lang ? $lang->getType() : 'pl';
        $request->setLocale($lang);
        $resp = null;
        if ($lang == 'pl') {
            $resp = $this->home($request);
        } else {
            $resp = $this->redirectToRoute('homepage', [
                '_locale' => $request->getLocale()
            ]);
        }
        $resp->headers->setCookie(new Cookie('LOCALE', $lang, time() + 3600 * 24 * 7, "/", $request->getHost(), $request->isSecure(), ! $request->isSecure()));

        return $resp;
    }



    /**
     * @Route({
     *  "pl" = "/pl/rezerwacja",
     *  "en" = "/en/reservation",
     *  "de" = "/de/reservierung",
     *  "ru" = "/ru/rezervirovanie"
     * }, name="reservation")
     */
    public function reservation()
    {
        return $this->render('home/reservation.html.twig');
    }

    /**
     * @Route({
     *  "pl" = "/pl/polityka-prywatnosci",
     *  "en" = "/en/privacy-policy",
     *  "de" = "/de/privacy-policy",
     *  "ru" = "/ru/privacy-policy"
     * }, name="privacy_policy")
     */
    public function privacyPolicy()
    {
        return $this->render('home/privacy_policy.html.twig');
    }

    /**
     * @Route({
     *  "pl" = "/pl/regulaminy",
     *  "en" = "/en/rules",
     *  "de" = "/de/rules",
     *  "ru" = "/ru/rules"
     * }, name="rules")
     */
    public function rules()
    {
        return $this->render('home/rules.html.twig');
    }

    private function isBot()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return preg_match('/rambler|abacho|acoi|accona|aspseek|altavista|estyle|scrubby|lycos|geona|ia_archiver|alexa|sogou|skype|facebook|twitter|pinterest|linkedin|naver|bing|google|yahoo|duckduckgo|yandex|baidu|teoma|xing|java\/1.7.0_45|bot|crawl|slurp|spider|mediapartners|\sask\s|\saol\s/i', $_SERVER['HTTP_USER_AGENT']);
        }

        return false;
    }
}

