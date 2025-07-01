<?php
namespace w3des\AdminBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class NodeRoutingLoader extends Loader
{

    private $loaded = false;

    private $locales;

    private $default;

    private $prefix = '';

    public function __construct($locales, $default, $prefix = '')
    {
        $this->locales = $locales;
        $this->default = $default;
        $this->prefix = $prefix;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        $later = [];
        foreach ($this->locales as $locale) {
            if ($locale == $this->default) {
                $prefix = '';
            } else {
                $prefix = '/' . $locale;
            }
            $r = new Route($prefix . '');
            $r->setDefaults([
                '_controller' => 'App\Controller\HomeController::home',
                '_locale' => $locale,
                '_canonical_route' => 'homepage'
            ]);
            $routes->add('homepage.' . $locale, $r);

            $r = new Route($prefix . '/{path}');
            $r->setDefaults([
                '_controller' => 'App\Controller\NodeController::node',
                '_locale' => $locale,
                '_canonical_route' => 'node'
            ]);
            $later['node.' . $locale] = $r;
        }

        foreach ($later as $n => $v) {
            $routes->add($n, $v);
        }

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'node' === $type;
    }
}

