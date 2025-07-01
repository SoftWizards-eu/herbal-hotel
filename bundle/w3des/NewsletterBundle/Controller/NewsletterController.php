<?php
namespace w3des\NewsletterBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use w3des\NewsletterBundle\Entity\NewsletterEmail;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use w3des\AdminBundle\Service\CMS;

class NewsletterController extends AbstractController
{
    private CMS $cms;

    public function __construct(CMS $cms)
    {
        $this->cms = $cms;
    }
    /**
     * @Route("/subscribe", name="newsletter.subscribe")
     */
    public function subscribeAction(Request $request)
    {
        $_service = $this->cms->getService();
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(NewsletterEmail::class);
        $email = trim(($request->get('email')));
        if ($request->get('direct')) {
            $email = \base64_decode($email);
        } else  {
            $email = \strtolower($email);
        }
        if ($request->get('remove')) {
            $model = $repo->findOneBy(['email' => $email, 'locale' => $request->getLocale()]);
            if ($model) {
                $em->remove($model);
                $em->flush();
                $this->get('session')->getFlashBag()->add('newsletter-flash', 'Usunięto pomyślnie');
            } else {
                $this->get('session')->getFlashBag()->add('newsletter-flash', 'Nie znaleziono adresu e-mail');
            }

        } else {

            if ($this->isCsrfTokenValid('subscribe', $request->request->get('_token')) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($repo->findOneBy(['email' => $email, 'locale' => $request->getLocale()]) == null) {
                    $model = new NewsletterEmail();
                    $model->setEmail($email);
                    $model->setService($_service);
                    $model->setLocale($request->getLocale());
                    $model->setId(Uuid::uuid4()->toString());
                    $model->setCreatedAt(new \DateTime());
                    $em->persist($model);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('newsletter-flash', 'Zostałeś dodany do newslettera');
                } else {
                    $this->get('session')->getFlashBag()->add('newsletter-flash', 'Jesteś już użytkownikiem newslettera');
                }
            } else {
                $this->get('session')->getFlashBag()->add('newsletter-flash', 'Niepoprawny adres e-mail');
            }

        }

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('homepage'));
    }
}

