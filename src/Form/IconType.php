<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class IconType extends AbstractType
{

    public function getParent()
    {
        return ChoiceType::class;
    }
    /**
     * {@inheritDoc}
     */
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefault('attr', [
            'class' => 'icon-list'
        ]);
        $resolver->setDefault('choices', [
            'agregaty',
            'chlodnictwo',
            'maszynownie',
            'pic-browar',
            'pic-chemiczne',
            'pic-mieso',
            'pic-mleczne',
            'pic-publiczna',
            'pic-ryby',
            'pic-wszystkie',
            'skraplacze',
            'transport',
            'tunele',
            'wentylacja',
            'zbiorniki-cisnieniowe',
            'zbiorniki-nh3',
            'attachment',
            'cogs',
            'hammer-wrench',
            'camera',
            'clipboard2'
        ]);
        $resolver->setDefault('choice_label', function ($choice, $key, $value) {

            return $value;
        });
    }


}

