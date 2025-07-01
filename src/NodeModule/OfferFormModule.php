<?php
namespace App\NodeModule;

use w3des\AdminBundle\Model\AbstractNodeModule;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Model\NodeView;
use Symfony\Component\Form\FormFactoryInterface;
use w3des\AdminBundle\Service\Settings;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\FormInterface;
use App\Form\OfferType;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Form\ServiceType;

class OfferFormModule extends AbstractNodeModule
{
    private array $forms = [];

    private FormFactoryInterface $factory;

    private Settings $settings;

    private MailerInterface $mailer;

    private TranslatorInterface $translator;

    public function __construct(FormFactoryInterface $factory, Settings $settings, MailerInterface $mailer, TranslatorInterface $translator)
    {
        $this->factory = $factory;
        $this->settings = $settings;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    public static function name(): string
    {
        return 'offer_form';
    }

    public function frontend(Request $request, NodeView $module, array $options)
    {
        $offerForm = $this->getForm($module, 'offer');
        $serviceForm = $this->getForm($module, 'service');


        $offerForm->handleRequest($request);
        $serviceForm->handleRequest($request);

        $submittedForm = null;
        $subject = null;
        $userText = null;
        $moduleId = $module->id ?? 'main';
        if ($offerForm->isSubmitted() && $offerForm->isValid()) {
            $submittedForm = $offerForm;
            $subject = 'Formularz ofertowy';
            $userText = '<p>Dziękujemy za złożenie zapytania.<br />
Wkrótce nasz zespół skontaktuje się z Tobą.</p>';
        }
        if ($serviceForm->isSubmitted() && $serviceForm->isValid()) {
            $submittedForm = $serviceForm;
            $subject = 'Formularz serwisowy';
            $userText = '<p>Dziękujemy za przesłanie zgłoszenia serwisowego.<br />
Wkrótce nasz zespół skontaktuje się z Tobą.</p>';
        }
        if($submittedForm) {
            $view = $submittedForm->createView();
            $body = '<p>Dane formularza:</p>';
            foreach ($submittedForm as $field) {
                if ($field->getName() == 'to' || $field->getName() == '_module' || $field->getName() == 'recaptcha') {
                    continue;
                }
                $ff = $view->children[$field->getName()];
                $label = $this->translator->trans($ff->vars['label'], [], $ff->vars['translation_domain'] ?? 'messages');
                $body .= '<p><strong>' . $label . '</strong>: ' . $field->getData() . '</p>';
            }
            $msg = new Email();
            $msg->subject($subject);
            $msg->html($body);
            $msg->from($this->settings->get('mail_from') ?? 'example@example.com');
            if ($submittedForm->has('to')) {
                $to = $this->nodes->fetch($submittedForm['to']->getData());
                if ($to) {
                    $msg->addTo($this->nodes->getVariable($to, 'address'));
                } else {
                    $msg->addTo($this->settings->get('mail_to'));
                }
            } else {
                foreach (\explode(',', $this->settings->get('mail_to') ?? 'exmaple@example.com') as $mail) {
                    $msg->addTo($mail);
                }
            }

            $this->mailer->send($msg);

            $msgUser = new Email();
            $msgUser->subject($subject);
            $msgUser->html('<p>Witaj ' . $submittedForm->get('name')->getData() . ',</p>
' . $userText . '
<p></p>
<p>Wiadomość generowana automatycznie, prosimy nie odpowiadać. Preferowany kontakt za pośrednictwem formularza dostępnego na stronie zamówienia.</p>');
            $msgUser->addTo($submittedForm->get('email')->getData());
            $msgUser->from($this->settings->get('mail_from') ?? 'example@example.com');
            $this->mailer->send($msgUser);

            $request->getSession()
            ->getFlashBag()
            ->add('offer' . $moduleId, 'Formularz został wysłany');

            return new RedirectResponse($request->getRequestUri());
        }

        return [
            'module' => $module,
            'options' => $options,
            'offerForm' => $offerForm->createView(),
            'serviceForm' => $serviceForm->createView()
        ];
    }

    /**
     * @param NodeView $module
     * @return FormInterface
     */
    public function getForm(NodeView $module, $type)
    {
        if (! isset($this->forms[$module->id . '_' . $type])) {
            $this->forms[$module->id . '_' . $type] = $this->factory->create($type == 'service' ? ServiceType::class : OfferType::class, [
                '_module' => $module->id
            ], [

                'module' => $type .  '_' . ($module->id > 0 ? 'module_' . $module->id : 'main')
            ]);
        }

        return $this->forms[$module->id . '_' . $type];
    }
}

