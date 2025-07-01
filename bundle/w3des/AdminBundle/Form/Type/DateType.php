<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class DateType extends AbstractType
{

    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\DateType';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd',
            'html5' => false,
            'attr' => [
                'class' => 'date'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(function ($v) {
            if ($v == 'now') {
                return new \DateTime();
            }
            return $v;
        }, function ($v) {
            return $v;
        }));
    }

    public function getBlockPrefix()
    {
        return 'admindate';
    }
}

