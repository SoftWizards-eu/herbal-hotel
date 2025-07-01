<?php
namespace w3des\AdminBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use w3des\AdminBundle\Entity\File;
use w3des\AdminBundle\Entity\Node;
use VertigoLabs\DoctrineFullTextPostgres\ORM\Mapping\TsVector;

trait ValueTrait
{

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $locale;

    /**
     * @ORM\Column(type="integer")
     */
    private $pos = 0;

    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $stringValue;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $intValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $floatValue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $textValue;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTimeValue;

    /**
     * @ORM\ManyToOne(targetEntity="w3des\AdminBundle\Entity\File", fetch="EAGER", cascade={"persist", "merge", "detach", "refresh"})
     * @ORM\JoinColumn(name="file_value", referencedColumnName="id")
     */
    private $fileValue;

    private $newPos;

    /**
     * @var TsVector
     * @TsVector(name="string_value_fts", fields={"stringValue", "textValue"}, language="english")
     */
    private $fts;

    public $uploadedFile;

    /**
     * @ORM\ManyToOne(targetEntity="Node", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="node_value", referencedColumnName="id")
     */
    private $nodeValue;

    public function getPos()
    {
        return $this->pos;
    }

    public function setPos($pos)
    {
        $this->pos = $pos;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function cleanValues()
    {
        $this->intValue = null;
        $this->stringValue = null;
        $this->textValue = null;
        $this->floatValue = null;
        $this->dateTimeValue = null;
        $this->fileValue = null;
        $this->nodeValue = null;
    }

    public function getStringValue()
    {
        return $this->stringValue;
    }

    public function setStringValue($stringValue)
    {
        $this->cleanValues();
        $this->stringValue = $stringValue;
        return $this;
    }

    public function getIntValue()
    {
        return $this->intValue;
    }

    public function setIntValue($intValue)
    {
        $this->cleanValues();
        $this->intValue = $intValue;
        return $this;
    }

    public function getFloatValue()
    {
        return $this->floatValue;
    }

    public function setFloatValue($floatValue)
    {
        $this->cleanValues();
        $this->floatValue = $floatValue;
        return $this;
    }

    public function getTextValue()
    {
        return $this->textValue;
    }

    public function setTextValue($textValue)
    {
        $this->cleanValues();
        $this->textValue = $textValue;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    public function getDateTimeValue()
    {
        return $this->dateTimeValue;
    }

    public function setDateTimeValue($dateTimeValue)
    {
        $this->dateTimeValue = $dateTimeValue;
        return $this;
    }

    /**
     * @return File
     */
    public function getFileValue()
    {
        return $this->fileValue;
    }

    public function setFileValue(File $file = null)
    {
        $this->fileValue = $file;
    }

    public function setNodeValue(Node $nodeValue = null)
    {
        $this->cleanValues();
        $this->nodeValue = $nodeValue;
        return $this;
    }

    public function getNodeValue()
    {
        return $this->nodeValue;
    }

    public function getValue()
    {
        switch ($this->getType()) {
            /** @var \w3des\AdminBundle\Entity\Setting $sett */
            case 'string':
                return $this->getStringValue();
            case 'text':
                return $this->getTextValue();
            case 'datetime':
                return $this->getDateTimeValue();
            case 'bool':
                return (bool) $this->getIntValue();
            case 'integer':
                return $this->getIntValue();
            case 'float':
                return $this->getFloatValue();
            case 'file':
                return $this->getFileValue();
            case 'node':
                return $this->getNodeValue();
        }
    }

    public function setValue($value)
    {
        switch ($this->getType()) {
            /** @var \w3des\AdminBundle\Entity\Setting $sett */
            case 'string':
                $this->setStringValue($value);
                break;
            case 'text':
                $this->setTextValue($value);
                break;
            case 'datetime':
                $this->setDateTimeValue($value);
                break;
            case 'bool':
                $this->setIntValue($value ? 1 : 0);
                break;
            case 'integer':
                $this->setIntValue($value);
                break;
            case 'float':
                $this->setFloatValue($value);
                break;
            case 'file':
                $this->setFileValue($value);
                break;
            case 'node':
                $this->setNodeValue($value);
                break;
            default:
                throw new \InvalidArgumentException('Unknown type');
        }
    }

    public static function getFieldName($storeType)
    {
        switch ($storeType) {
            /** @var \w3des\AdminBundle\Entity\Setting $sett */
            case 'string':
                return 'stringValue';
            case 'text':
                return 'textValue';
            case 'datetime':
                return 'dateTimeValue';
            case 'bool':
                return 'intValue';
            case 'integer':
                return 'intValue';
            case 'float':
                return 'floatValue';
            case 'file':
                return 'fileValue';
            case 'node':
                return 'nodeValue';
        }
    }

    /**
     * @return $newPos
     */
    public function getNewPos()
    {
        return $this->newPos;
    }

    /**
     * @param mixed $newPos
     */
    public function setNewPos($newPos)
    {
        $this->newPos = $newPos;
    }
}
