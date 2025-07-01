<?php
namespace w3des\AdminBundle\NodeModule;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use w3des\AdminBundle\Model\AbstractNodeModule;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListModule extends AbstractNodeModule
{

    public static function name(): string
    {
        return 'list';
    }

    public static function fields(): array
    {
        return [
            'type' => [
                'label' => 'Rodzaj',
                'index' => false,
                'type' => ChoiceType::class,
                'storeType' => 'string',
                'options' => [
                    'required' => true
                ]
            ]
        ];
    }

    public function configureAdminField(string $name, array &$fieldOptions, array $options): void
    {
        if ($name === 'type') {
            $fieldOptions['choices'] = $options['choices'];
        }
    }
    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('choices');
    }
}

