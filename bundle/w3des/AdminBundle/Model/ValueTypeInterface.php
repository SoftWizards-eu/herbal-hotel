<?php
namespace w3des\AdminBundle\Model;

interface ValueTypeInterface
{

    public function getStoreType(array $options): string;
}
