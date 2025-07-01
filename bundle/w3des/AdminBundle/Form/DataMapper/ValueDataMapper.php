<?php
namespace w3des\AdminBundle\Form\DataMapper;

use w3des\AdminBundle\Model\ValueDefinition;
use Symfony\Component\Form\DataMapperInterface;
use w3des\AdminBundle\Service\Values;
use Doctrine\Common\Collections\ArrayCollection;

class ValueDataMapper implements DataMapperInterface
{

    private $type;

    private $fields;

    private $locales;

    private $mapping;

    private $values;

    private $map;

    /**
     *
     * @param ValueDefinition[] $fields
     * @param string $type
     */
    public function __construct(Values $values, array $fields, callable $type, array $locales)
    {
        $this->type = $type;
        $this->fields = $fields;
        $this->locales = $locales;
        $this->values = $values;
    }

    /**
     */
    public function mapDataToForms($viewData, \Traversable $forms)
    {
        if ($viewData !== null) {
            $map = $this->values->dataView($viewData, $this->fields, $this->locales);
            $forms = iterator_to_array($forms);
            foreach ($map as $name =>  $item) {
                $forms[$name]->setData($item);
            }
        }

    }

    public function mapFormsToData(\Traversable $forms, &$viewData)
    {
        $collect = [];
        $forms = iterator_to_array($forms);
        foreach ($forms as $name => $field) {
            $collect[$name] = $field->getData();
        }
        if ($viewData === null) {
            $viewData = new ArrayCollection();
        }
        //var_dump($viewData);
        $this->values->setDataView($viewData, $collect, $this->fields, $this->locales, $this->type);
    }
}

