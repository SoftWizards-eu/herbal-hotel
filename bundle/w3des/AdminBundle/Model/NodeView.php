<?php
namespace w3des\AdminBundle\Model;

use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Service\Nodes;
use Symfony\Component\Routing\RouterInterface;

/**
 * @property-read NodeView[] $children
 * @property-read NodeView $parent
 * @property-read ModuleInfo[][] $modules
 * @property-read int $pos
 * @property-read int $id
 * @property-read string|null $url
 * @property-read string $type
 * @property-read service $service
 * @property-read Node $model
 * @property-read ValuesMap $vars
 *
 */
class NodeView
{

    public Node $model;

    private Nodes $_nodes;

    private $_children;

    private $_parent;

    private $_modules;

    private $_vars;

    public function __construct(Nodes $nodes, NodeView $parent = null)
    {
        $this->_nodes = $nodes;
        $this->_parent = $parent;
    }

    public function __get($name)
    {
        if ($name === 'children') {
            if ($this->_children === null) {
                $this->_children = $this->_nodes->wrapArray($this->model->getChildren());
            }
            return $this->_children;
        }

        if ($name === 'parent') {
            if ($this->_parent === null && $this->model->getParent() !== null) {
                $this->_parent = $this->_nodes->wrap($this->model->getParent());
            }
            return $this->_parent;
        }
        if ($name === 'url') {
            return $this->_nodes->getUrl($this->model, RouterInterface::ABSOLUTE_PATH);
        }

        if ($name === 'vars') {
            if ($this->_vars === null) {
                $this->_vars = $this->_nodes->wrapVariables($this->_nodes->getVariables($this->model));
            }
            return $this->_vars;
        }

        if ($name === 'modules') {
            if ($this->_modules === null) {
                $this->calcModules();
            }

            return $this->_modules;
        }

        return $this->model->{'get' . $name}();
    }

    private function calcModules()
    {
        $this->_modules = [];
        foreach ($this->_nodes->getNodeCfg($this->model->getType())['modules'] as $sectionName => $info) {
            $this->_modules[$sectionName] = [];
            foreach ($this->vars->{$info['field']} as $module) {
                $mod = new NodeView($this->_nodes, $this);
                $mod->model = $module->model;
                $this->_modules[$sectionName][] = $mod;
            }
        }
    }

    public function url($mode = RouterInterface::ABSOLUTE_PATH)
    {
        return $this->_nodes->getUrl($this->model, $mode);
    }

    public function __isset($name)
    {
        return true;
    }

}

