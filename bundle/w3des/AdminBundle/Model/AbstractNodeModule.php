<?php
namespace w3des\AdminBundle\Model;

use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Form\Type\NodeModuleType;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractNodeModule implements NodeModuleInterface
{

    public static function fields(): array
    {
        return [];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {}

    public function adminType(array $options): string
    {
        return NodeModuleType::class;
    }

    public function configureAdminField(string $name, array &$fieldOptions, array $options): void
    {}

    public function frontend(Request $request, NodeView $module, array $options)
    {
        return [
            'module' => $module,
            'options' => $options
        ];
    }
}

