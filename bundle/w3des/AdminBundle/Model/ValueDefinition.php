<?php
namespace w3des\AdminBundle\Model;

use w3des\AdminBundle\Util\ValueTypeDecoder;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Definition for ValueInterface
 */
class ValueDefinition implements \Serializable
{

    public $name;

    public $locale = '';

    public $type;

    public $storeType;

    public $array = false;

    public $options = [];

    public $index = false;

    public $default = null;

    public $sortable = null;

    public function __construct($name, $options = [])
    {
        $this->name = $name;
        $this->locale = $options['locale'] ?? false;
        $this->type = $options['type'] ?? TextType::class;
        $this->array = $options['array'] ?? false;
        $this->options = $options['options'] ?? [];
        $this->index = $options['index'] ?? false;
        $this->default = $options['default'] ?? null;
        $this->sortable = $options['sortable'] ?? null;
        $this->storeType = $options['storeType'] ?? ValueTypeDecoder::decode($this->type, $this->options);
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'locale' => $this->locale,
            'type' => $this->type,
            'storeType' => $this->storeType,
            'array' => $this->array,
            'options' => $this->options,
            'index' => $this->index,
            'default' => $this->default,
            'sortable' => $this->sortable,
        ];
    }

    public function serialize()
    {
        return serialize(array_values($this->toArray()));
    }

    public function unserialize($serialized)
    {
        list ($this->name, $this->locale, $this->type, $this->storeType, $this->array, $this->options, $this->default, $this->sortable) = \unserialize($serialized);
    }
}

