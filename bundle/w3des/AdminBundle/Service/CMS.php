<?php
namespace w3des\AdminBundle\Service;

use Symfony\Contracts\Translation\LocaleAwareInterface;

class CMS implements LocaleAwareInterface, \ArrayAccess
{

    private array $serviceSettings = ['id' => 0];

    private string $locale;

    private array $services;

    private array $globals;

    public function __construct($defaultPageLocale, $services)
    {
        $this->locale = $defaultPageLocale;
        $this->services = $services;
    }

    public function getService()
    {
        return $this->serviceSettings['id'];
    }

    public function getSettings($service = null)
    {
        if ($service === null) {
            return $this->serviceSettings;
        }
        foreach ($this->services as $s) {
            if ($s['id'] == $service) {
                return $s;
            }
        }
        return $this->services[$service];
    }

    public function getServices()
    {
        return $this->services;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }
    public function offsetGet($offset)
    {
        return $this->globals[$offset];
    }

    public function offsetExists($offset)
    {
        return isset($this->globals[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->globals[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        $this->globals[$offset] = $value;
    }

    public function set($offset, $value)
    {
        $this[$offset] = $value;
    }

    public function get($offset, $default)
    {
        return $this[$offset] ?? $default;
    }

    public function resolveService($host, $selected = null)
    {
        if ($selected == null) {
            foreach ($this->services as $id => $service) {
                if (isset($service['domains']) && \in_array($host, $service['domains'])) {
                    $selected = $id;
                    break;
                }
            }
        }
        if (!$selected) {
            $selected = 'default';
        }
        $this->serviceSettings = $this->services[$selected];
    }

}

