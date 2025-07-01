<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use w3des\AdminBundle\Service\Nodes;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaV3Type;

class ServiceType extends AbstractType
{
    private Nodes $nodes;
    public function __construct(Nodes $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label' => 'form.name'
        ]);
        $builder->add('email', EmailType::class, [
            'label' => 'form.email'
        ]);
        $builder->add('phone', TextType::class, [
            'label' => 'form.phone'
        ]);
        $builder->add('location', TextType::class, [
            'label' => 'form.location'
        ]);

        $choices = [];
        foreach ($this->nodes->getNodes('category', ['orderBy' => ['title' => 'asc']])['list'] as $row) {
            $choices[$row->vars->title] = $row->vars->title;
        }
        $builder->add('category', ChoiceType::class, [
            'label' => 'form.choice',
            'choices' => $choices
        ] );
        $builder->add('content', TextareaType::class, [
            'label' => 'form.content'
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'module' => 'main'
        ]);
    }
}

