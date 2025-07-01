<?php
namespace w3des\NewsletterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class NewsletterType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disable = false;
        if ($options['data']->getSendAt()) {
            $disable = true;
        }
        $builder->add('title', TextType::class, [
            'label' => 'Tytuł',
            'disabled' => $disable
        ]);
        if ($builder->getData()->getId()) {
            $builder->add('test_email', EmailType::class, [
                'required' => false,
                'mapped' => false,
                'disabled' => $disable
            ]);
        }
        $builder->add('content', 'w3des\AdminBundle\Form\Type\CKEditorType', [
            'label' => 'Treść',
            'disabled' => $disable
        ]);
    }
}

