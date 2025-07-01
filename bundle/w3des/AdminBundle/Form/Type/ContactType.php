<?php
namespace w3des\AdminBundle\Form\Type;

use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use w3des\AdminBundle\Service\Nodes;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaV3Type;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrueV3;

class ContactType extends AbstractType
{

    private $nodes;

    public function __construct(Nodes $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label' => 'form.name',
            'attr' => [
                'placeholder' => 'form.name'
            ],
            'required' => true
        ]);
        $builder->add('email', EmailType::class, [
            'label' => 'form.email',
            'attr' => [
                'placeholder' => 'form.email'
            ],
            'required' => false
        ]);
        $builder->add('phone', TextType::class, [
            'label' => 'form.phone',
            'attr' => [
                'placeholder' => 'form.phone'
            ],
            'required' => false
        ]);
        $builder->add('subject', TextType::class, [
            'label' => 'form.subject',
            'attr' => [
                'placeholder' => 'form.subject'
            ],
            'required' => false
        ]);
        if (is_array($options['contacts']) && count($options['contacts'])) {
            $choices = [];
            foreach ($options['contacts'] as $cnt) {
                $choices[$this->nodes->getVariable($cnt, 'name')] = $cnt->getId();
            }
            $builder->add('to', ChoiceType::class, [
                'choices' => $choices,
                'choices_as_values' => true,
                'label' => 'form.to'
            ]);
        }
        $builder->add('content', TextareaType::class, [
            'label' => 'form.message',
            'attr' => [
                'placeholder' => 'form.message',
                'rows' => 1
            ]
        ]);
        $cnt = new \App\Validator\IsTrueV3();
        $cnt->message = 'form.invalid_captcha';

        $builder->add('recaptcha', EWZRecaptchaV3Type::class, [
            'mapped' => false,
            'required' => true,
            'block_prefix' => 'custom_ewz_recaptcha',
            'constraints' => array(
                $cnt
            ),
            'action_name' => $options['module'],
            'label' => false,

        ]);
    }

    public function getParent()
    {
        return FormType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'contacts' => false,
            'module' => 'main'
        ]);
    }
}

