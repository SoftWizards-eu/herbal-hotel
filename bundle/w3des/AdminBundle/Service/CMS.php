<?php
namespace w3des\AdminBundle\Service;

use Symfony\Contracts\Translation\LocaleAwareInterface;

class CMS implements LocaleAwareInterface, \ArrayAccess
{

    private array $serviceSettings = ['id' => 0];

    private string $locale;

    private array $services;

    private array $globals;

    public function __construct(string $defaultPageLocale, array $services)
    {
        $this->locale = $defaultPageLocale;
        $this->services = $services;
        $this->globals = [];
    }

    public function getService()
    {
        return $this->serviceSettings['id'];
    }

    public function getSettings(mixed $service = null): mixed
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

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
    public function offsetGet(mixed $offset): mixed
    {
        return $this->globals[$offset] ?? null;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->globals[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->globals[$offset]);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->globals[] = $value;
        } else {
            $this->globals[$offset] = $value;
        }
    }

    public function set(mixed $offset, mixed $value): void
    {
        $this[$offset] = $value;
    }

    public function get(mixed $offset, mixed $default = null): mixed
    {
        return $this[$offset] ?? $default;
    }

    public function resolveService(string $host, mixed $selected = null): void
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

