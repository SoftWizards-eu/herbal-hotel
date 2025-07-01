<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Service\Values;
use w3des\AdminBundle\Form\DataMapper\ValueDataMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ValuesType extends AbstractType
{

    private $values;

    public function __construct(Values $values)
    {
        $this->values = $values;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper(new ValueDataMapper($this->values, $options['fields'], $options['value_type'], $options['locales']));
        foreach ($options['fields'] as $field) {
            if ($options['configure_value']) {
                $options['configure_value']($field['name'], $field['options']);
            }
            $builder->add($field['name'], ValueFieldType::class, [
                'locales' => $options['locales'],
                'definition' => $field,
                'label' => false,
                'label_prefix' => $options['label_prefix']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('fields')
            ->setRequired('value_type')
            ->setNormalizer('value_type', function (Options $options, $val) {
            if (\is_string($val)) {
                return function () use ($val) {
                    return new $val();
                };
            }
            if (! \is_callable($val)) {
                throw new \InvalidArgumentException('value_type have to by callable or string');
            }
            return $val;
        })
            ->setRequired('locales')
            ->setDefault('sections', false)
            ->setDefault('configure_value', null)
            ->setDefault('empty_data', function() {
                return new ArrayCollection();
            })->setDefault('label_prefix', '');
    }

    public function getBlockPrefix() {
        return 'values';
    }
    /**
     * {@inheritDoc}
     */
    public function buildView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options)
    {
        $view->vars['sections'] = $options['sections'];
    }

}

