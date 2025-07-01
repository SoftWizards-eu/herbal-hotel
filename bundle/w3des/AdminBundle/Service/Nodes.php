<?php
namespace w3des\AdminBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Model\ValueDefinition;
use w3des\AdminBundle\Model\ModuleInfo;
use w3des\AdminBundle\Model\ValuesMap;
use w3des\AdminBundle\Model\NodeView;
use w3des\AdminBundle\Entity\NodeVariable;

class Nodes
{

    private EntityManagerInterface $em;

    private array $cfg;

    private Values $vals;

    private RouterInterface $router;

    private string $defaultLocale;

    private CMS $cms;

    public function __construct(EntityManagerInterface $em, CMS $cms, Values $vals, RouterInterface $router, $defaultLocale, array $cfg)
    {
        $this->em = $em;
        $this->cfg = $cfg;
        $this->vals = $vals;
        $this->router = $router;
        $this->defaultLocale = $defaultLocale;
        $this->cms = $cms;
    }

    public function getCfg()
    {
        return $this->cfg;
    }

    public function getNodeCfg($type)
    {
        return $this->cfg[$type];
    }

    public function getVariables(Node $node, $locale = null): ValuesMap
    {
        if ($locale == null) {
            $locale = $this->cms->getLocale();
        }
        if ($node->getVariableMap() == null) {
            $cfg = $this->getNodeCfg($node->getType());
            $node->setVariableMap($this->vals->createMap($node->getVariables(), $cfg['fields'], $locale));
        }

        return $node->getVariableMap();
    }

    public function getVariable(Node $node, $name, $locale = null)
    {
        return $this->getVariables($node, $locale)->get($name, $locale);
    }

    /**
     * @return array[]
     */
    public function getFields($type)
    {
        return $this->cfg[$type]['fields'];
    }

    public function getSectionModules(Node $node, $section, $trackParents = false)
    {
        $res = [];
        foreach ($this->getVariable($node, $section . '_modules') as $pos => $item) {
            $res[] = new ModuleInfo($item->getType(), $this->getVariables($item), $pos, $node, $section);
        }

        return $res;
    }

    public function getUrl(Node $node, $mode = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        
        if (! $node->getUrl()) {
            return null;
        }

        return $this->router->generate('node', [
            'path' => $node->getUrl($this->cms->getLocale())->getPath(),
            '_locale' => $this->cms->getLocale()
        ], $mode);
    }

    public function getNodes(string $type, array $cfg = [])
    {
        if (! isset($cfg['locale'])) {
            if ($this->getNodeCfg($type)['locale']) {
                $cfg['locale'] = $this->cms->getLocale();
            } else {
                $cfg['locale'] = Values::MODEL_DEFAULT_LOCALE;
            }
        }
        $cfg['page_locale'] = $this->cms->getLocale();
        $res = $this->em->getRepository(Node::class)->findByConfig($this->cms->getService(), $type, $this, $cfg);
        if ($cfg['query'] ?? false) {
            return $res;
        }

        $res['list'] = $this->wrapArray($res['list']);

        return $res;
    }

    public function getById(int $id): ?NodeView
    {
        return $this->wrap($this->em->find(Node::class, $id));
    }

    public function getByPath(string $path, ?string $locale): ?NodeView
    {
        return $this->wrap($this->em->getRepository(Node::class)->findByPath($this->cms->getService(), $path, $locale ?? $this->cms->getLocale()));
    }

    public function inUse(Node $node, $except = null)
    {
        return $this->em->getRepository(Node::class)->checkInUse($node, $except);
    }

    /**
     * @param Node $node
     * @return NodeView
     */
    public function wrap(?Node $node): ?NodeView
    {
        if (null === $node) {
            return null;
        }
        $tmp = new NodeView($this);
        $tmp->model = $node;

        return $tmp;
    }

    /**
     *
     * return NodeView[]
     */
    public function wrapArray(iterable $it)
    {
        $list = [];
        foreach ($it as $node) {
            $list[] = $this->wrap($node);
        }
        return $list;
    }

    public function wrapVariables(ValuesMap $vars): ValuesMap
    {
        $values = $vars->getValues();
        $definitions = $vars->getDefinitions();
        foreach ($values as $name => $localized) {
            if ($definitions[$name]['storeType'] == 'node') {
                foreach ($localized as $loc => $v) {
                    if ($definitions[$name]['array']) {
                        $v = $this->wrapArray($v);
                    } else {
                        $v = $this->wrap($v);
                    }
                    $values[$name][$loc] = $v;
                }
            }
        }
        return new ValuesMap($definitions, $values, $vars->getLocale());
    }

    public function search(array $types, array $cfg = [])
    {

        $res = $this->em->getRepository(Node::class)->searchByConfig($this->cms->getService(), $types, $cfg);
        if ($cfg['query'] ?? false) {
            return $res;
        }

        $res['list'] = $this->wrapArray($res['list']);

        return $res;
    }
}

