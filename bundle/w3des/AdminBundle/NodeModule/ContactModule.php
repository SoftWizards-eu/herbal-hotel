<?php
namespace w3des\AdminBundle\NodeModule;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use w3des\AdminBundle\Form\Type\CKEditorType;
use w3des\AdminBundle\Form\Type\ContactType;
use w3des\AdminBundle\Model\AbstractNodeModule;
use w3des\AdminBundle\Model\NodeView;
use w3des\AdminBundle\Service\Settings;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactModule extends AbstractNodeModule
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
        return 'contact_form';
    }

    public static function fields(): array
    {
        return [
            /*'content' => [
                'label' => 'Treść po prawej',
                'type' => CKEditorType::class,
                'index' => true,
                'options' => [
                    'config' => [
                        'bodyClass' => 'text text-content contact-content'
                    ]
                ]
            ]*/
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function frontend(Request $request, NodeView $module, array $options)
    {
        $form = $this->getForm($module);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $view = $form->createView();
            $body = '<p>Dane formularza:</p>';
            foreach ($form as $field) {
                if ($field->getName() == 'to' || $field->getName() == '_module' || $field->getName() == 'recaptcha') {
                    continue;
                }
                $ff = $view->children[$field->getName()];
                $label = $this->translator->trans($ff->vars['label'], [], $ff->vars['translation_domain'] ?? 'messages');
                $body .= '<p><strong>' . $label . '</strong>: ' . $field->getData() . '</p>';
            }
            $msg = new Email();
            $msg->subject('Formularz kontaktowy');
            $msg->html($body);
            $msg->from($this->settings->get('mail_from') ?? 'exmaple@example.com');
            if ($form->has('to')) {
                $to = $this->nodes->fetch($form['to']->getData());
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
            $request->getSession()
                ->getFlashBag()
                ->add('info' . $module->id, 'Formularz został wysłany');

            return new RedirectResponse($request->getRequestUri() . '#contact' . $module->id);
        }

        return [
            'module' => $module,
            'form' => $form->createView()
        ];
    }

    /**
     * @param NodeView $module
     * @return FormInterface
     */
    public function getForm(NodeView $module)
    {
        if (! isset($this->forms[$module->id])) {
            $this->forms[$module->id] = $this->factory->create(ContactType::class, [
                '_module' => $module->id
            ], [
                'module' => 'contact_' . ($module->id > 0 ? 'module_' . $module->id : 'main')
            ]);
        }

        return $this->forms[$module->id];
    }
}

