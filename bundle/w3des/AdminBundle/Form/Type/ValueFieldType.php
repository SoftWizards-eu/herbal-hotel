<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Service\Values;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\CallbackTransformer;
use w3des\AdminBundle\Form\DataMapper\OrderedArrayMapper;

class ValueFieldType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \w3des\AdminBundle\Model\ValueDefinition $cfg */
        $cfg = $options['definition'];
        foreach ($cfg['locale'] ? $options['locales'] : [Values::MODEL_DEFAULT_LOCALE] as $locale) {
            $builder->add($locale, $cfg['type'], array_merge([
                'label_format' => $options['label_prefix'] . $cfg['name'],
                'required' => false
            ], $cfg['options']));
            if ($cfg['array']) {
                $builder->get($locale)->addViewTransformer(new CallbackTransformer(function($v) {
                    return $v;
                }, function($v) {
                    return $v;
                }, false));
            }
            /*if ($cfg['array'] && $cfg['sortable']) {
                $builder->get($locale)->setDataMapper(new OrderedArrayMapper());
            }*/

        }



    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'definition',
            'locales'
        ])->setDefault('label_prefix', '');
    }

    public function getBlockPrefix() {
        return 'value_field';
    }
}

