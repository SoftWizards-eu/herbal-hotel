<?php
namespace w3des\AdminBundle\Model;

use w3des\AdminBundle\Entity\File;
use w3des\AdminBundle\Entity\Node;

interface ValueInterface
{

    public function setNewPos($pos);

    public function getNewPos();

    public function cleanValues();

    public function getStringValue();

    public function setStringValue($stringValue);

    public function getIntValue();

    public function setIntValue($intValue);

    public function getFloatValue();

    public function setFloatValue($floatValue);

    public function getTextValue();

    public function setTextValue($textValue);

    public function getFileValue();

    public function setFileValue(File $file = null);

    public function getNodeValue();

    public function setNodeValue(Node $node = null);

    public function getType();

    public function setType($type);

    public function getLocale();

    public function setLocale($locale);

    public function getName();

    public function setName($name);

    /**
     * @return \DateTime|null
     */
    public function getDateTimeValue();

    public function setDateTimeValue($dateTime);

    public function getPos();

    public function setPos($pos);

    public function getValue();
}

