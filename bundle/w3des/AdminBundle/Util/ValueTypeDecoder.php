<?php
namespace w3des\AdminBundle\Util;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use w3des\AdminBundle\Form\Type\AdvFileType;
use w3des\AdminBundle\Form\Type\CKEditorType;
use w3des\AdminBundle\Form\Type\DateTimeType;
use w3des\AdminBundle\Form\Type\EmbedNodeType;
use w3des\AdminBundle\Form\Type\FullNodeType;
use w3des\AdminBundle\Form\Type\NodeModulesType;
use w3des\AdminBundle\Form\Type\NodeType;
use w3des\AdminBundle\Form\Type\UploadedImageType;
use w3des\AdminBundle\Model\ValueTypeInterface;

class ValueTypeDecoder
{

    private static $knownTypes = [
        AdvFileType::class => 'file',
        UploadedImageType::class => 'file',
        CKEditorType::class => 'text',
        TextareaType::class => 'text',
        DateTimeType::class => 'datetime',
        \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class => 'datetime',
        NodeType::class => 'node',
        CheckboxType::class => 'bool',
        TextType::class => 'string',
        EmailType::class => 'string',
        FullNodeType::class => 'node',
        EmbedNodeType::class => 'node',
        NodeModulesType::class => 'node'
    ];

    public static function decode($typeName, array $options): ?string
    {
        if (isset(self::$knownTypes[$typeName])) {
            return self::$knownTypes[$typeName];
        }
        $type = new \ReflectionClass($typeName);
        if ($type->implementsInterface(ValueTypeInterface::class)) {
            $res = \call_user_func($typeName . '::getStoreType', $options);
            self::$knownTypes[$typeName] = $res;
            return $res;
        }

        return null;
    }
}

