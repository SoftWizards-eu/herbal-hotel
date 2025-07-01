<?php
namespace w3des\AdminBundle\Form\DataMapper;

use Symfony\Component\Form\DataMapperInterface;

class OrderedArrayMapper implements DataMapperInterface
{
    public function mapDataToForms($viewData, \Traversable $forms)
    {
        $forms = iterator_to_array($forms);
        foreach ($forms as $form) {
            $form->setData($viewData[$form->getName()]?? null);
        }
    }

    public function mapFormsToData(\Traversable $forms, &$viewData)
    {
        $forms = iterator_to_array($forms);
        $ids = [];
        $pos = 0;
        foreach ($forms as $form) {
            $ids[$form->getName()] = $pos++;
            $viewData[$form->getName()] = $form->getData();
        }
        foreach ($viewData as $k => $v) {
            if (!isset($ids[$k])) {
                unset($viewData[$k]);
            }
        }



        \uksort($viewData, function($a, $b) use($ids) {
            return $ids[$a] - $ids[$b];
        });
    }

}

