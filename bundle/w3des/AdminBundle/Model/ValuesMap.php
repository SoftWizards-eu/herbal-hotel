<?php
namespace w3des\AdminBundle\Model;

use w3des\AdminBundle\Service\Values;

class ValuesMap
{

    private $definitions;

    private $values;

    private $locale;

    /**
     * @param ValueDefinition[] $definitions
     * @param array $values
     */
    public function __construct(array $definitions, array $values, $locale)
    {
        $this->definitions = $definitions;
        $this->values = $values;
        $this->locale = $locale;
    }

    public function get($name, $locale = null)
    {
        if ($this->definitions[$name]['locale']) {
            return $this->values[$name][$locale ?: $this->locale];
        } else {
            return $this->values[$name][Values::MODEL_DEFAULT_LOCALE];
        }
    }

    public function has($name)
    {
        return isset($this->definitions[$name]);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function getDefinitions()
    {
        return $this->definitions;
    }
    /**
     * @return $values
     */
    public function getValues()
    {
        return $this->values;
    }


    /**
     * @return $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    public function __isset($name)
    {
        return isset($this->definitions[$name]);
    }

}

