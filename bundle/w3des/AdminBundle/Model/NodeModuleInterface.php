<?php
namespace w3des\AdminBundle\Model;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

interface NodeModuleInterface
{

    public static function name(): string;

    public static function fields(): array;

    public static function configureOptions(OptionsResolver $resolver): void;

    public function adminType(array $options): string;

    /**
     * @param string $name
     * @param array $options - section based settins
     * @param array $fieldOptions - current options
     * @return string|null Override FieldType
     */
    public function configureAdminField(string $name, array &$fieldOptions, array $options): void;

    /**
     * @param NodeView $module
     * @param array $options
     * @param string $section
     *
     * @return null|array|Response
     */
    public function frontend(Request $request, NodeView $module, array $options);
}

