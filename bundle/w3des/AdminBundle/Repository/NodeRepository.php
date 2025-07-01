<?php
namespace w3des\AdminBundle\Repository;

use Doctrine\ORM\EntityRepository;
use w3des\AdminBundle\Entity\Node;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use w3des\AdminBundle\Entity\NodeVariable;
use w3des\AdminBundle\Entity\Setting;
use w3des\AdminBundle\Model\NodeView;
use w3des\AdminBundle\Service\Nodes;
use Doctrine\ORM\Query;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;

class NodeRepository extends EntityRepository
{

    public function findByPath(int $service, string $path, string $locale)
    {

        $locales = [$locale, 'xx'];
        $qb = $this->_em->getRepository(Node::class)->createQueryBuilder('n');
        $qb->andWhere('n.service = :service');
        $qb->andWhere('n.locale in(:locale)');
        $qb->innerJoin('n.urls', 'u')->addSelect('u');
        $qb->andWhere('u.service = :service');
        $qb->andWhere('u.locale in(:locale)');
        $qb->andWhere('u.path = :path')->setParameters([
            'path' => $path,
            'locale' => $locales,
            'service' => $service
        ]);
        $this->expandJoin($qb);

        $ex = $qb->getQuery()->execute();

        return count($ex) ? $ex[0] : null;
    }

    private function vars(QueryBuilder $qb, $root, $vars, $fields, $num, $locales)
    {
        foreach ($vars as $field => $valueDef) {
            $qb->innerJoin($root . '.variables', 'v_' . $field . $num, Join::WITH, 'v_' . $field . $num . '.name = \'' . $field . '\' and v_' . $field . $num . '.locale in (:locales' . $num . ')');
            $qb->setParameter('locales' . $num, $locales);
            /** @var \w3des\AdminBundle\Model\ValueDefinition $tmp */
            $tmp = $fields[$field];
            if (is_array($valueDef) && isset($valueDef['value'])) {
                $val = $valueDef['value'];
            } else {
                $val = $valueDef;
            }
            if ($val === null || $val === '') {
                continue;
            }
            if (is_array($valueDef) && isset($valueDef['sign']) && $valueDef['sign'] != '=') {
                $val = is_array($val) ? $val[0] : $val;
                if ($val instanceof NodeView) {
                    $val = $val->model;
                }
                switch ($tmp['storeType']) {
                    case 'string':
                        $qb->andWhere('v_' . $field . $num . '.stringValue ' . $valueDef['sign'] . ' :val' . $num . '')->setParameter('val' . $num, $val);
                        break;
                    case 'bool':
                        $val = $val ? 1 : 0;
                    case 'integer':
                        $qb->andWhere('v_' . $field . $num . '.intValue ' . $valueDef['sign'] . ' :val' . $num . '')->setParameter('val' . $num, $val);
                        break;
                    case 'node':
                        $qb->andWhere('v_' . $field . $num . '.nodeValue ' . $valueDef['sign'] . ' :val' . $num . '')->setParameter('val' . $num, $val);
                        break;
                    case 'date':
                    case 'datetime':
                        if ($val == 'now') {
                            $val = new \DateTime();
                        }
                        $qb->andWhere('v_' . $field . $num . '.dateTimeValue ' . $valueDef['sign'] . ' :val' . $num . '')->setParameter('val' . $num, $val);
                        break;
                }
            } else {

                if (! is_array($val)) {
                    $val = [
                        $val
                    ];
                }

                switch ($tmp['storeType']) {
                    case 'string':
                        $qb->andWhere('v_' . $field . $num . '.stringValue in (:val' . $num . ')')->setParameter('val' . $num, $val);
                        break;
                    case 'bool':
                        $val = $val ? 1 : 0;
                    case 'integer':
                        $qb->andWhere('v_' . $field . $num . '.intValue in (:val' . $num . ')')->setParameter('val' . $num, $val);
                        break;
                    case 'node':
                        $qb->andWhere('v_' . $field . $num . '.nodeValue in (:val' . $num . ')')->setParameter('val' . $num, $val);
                        break;
                    case 'date':
                    case 'datetime':
                        $qb->andWhere('v_' . $field . $num . '.dateTimeValue in (:val' . $num . ')')->setParameter('val' . $num, $val);
                        break;
                }
            }
            $num ++;
        }

        return $num;
    }

    /**
     *
     * Options
     * - locale
     * - where {field => value, field => valuesArr, field => [sign, value]}
     * - orderBy
     * - parent
     * - pagging
     * - max
     *
     * @param int $service
     * @param string $type
     * @param array $fields
     * @param array $cfg
     * @return array
     */
    public function findByConfig(int $service, string $type, Nodes $nodeService, array $cfg = [])
    {
        $tcfg = $nodeService->getNodeCfg($type);
        $fields = $tcfg['fields'];
        $qb = $this->_em->getRepository(Node::class)->createQueryBuilder('n');
        $locales = ['xx', $cfg['page_locale']];
        $this->expandJoin($qb);
        if ($tcfg['url']) {
            //$qb->leftJoin('n.urls', 'u')->addSelect('u')->andWhere('u.locale in (:locales)')->setParameter('locales', [$cfg['page_locale'], 'xx']);
        }
        $qb->andWhere('n.type = :type')->setParameter('type', $type);
        if (($cfg['service']??null) !== -1) {
            $qb->andWhere('n.service = :service')->setParameter('service', $cfg['service'] ?? $service);
        }
        $qb->andWhere('n.locale= :locale')->setParameter('locale', $cfg['locale']);
        if (\array_key_exists('parent', $cfg)) {
            if ($cfg['parent'] === null) {
                $qb->andWhere('n.parent is null');
            } else {
                $qb->andWhere('n.parent = :parent')->setParameter('parent', $cfg['parent']);
            }

        }
        $num = 0;
        if (isset($cfg['id'])) {
            if (\is_array($cfg['id']) && count($cfg['id'])) {
                $qb->andWhere('n.id in (:ids)')->setParameter('ids', $cfg['id']);
            } elseif (!is_array($cfg['id'])) {
                $qb->andWhere('n.id = :id', $cfg['id']);
            } else {
                $qb->andWhere('n.id = -1');
            }
        }
        if(isset($cfg['module'])) {
            foreach ($cfg['module'] as $section => $module) {
                $field = $section. '_modules';
                $vars = [];
                if (\is_array($module)) {
                    $vars = $module[1];
                    $module = $module[0];
                }
                $module = 'module.' . $module;
                $qb->innerJoin('n.variables', 'v_' . $field . $num, Join::WITH, 'v_' . $field . $num . '.name = \'' . $field . '\'');
                $qb->innerJoin('v_' . $field . $num . '.nodeValue', 'v_' . $field . $num . '_node');
                $qb->andWhere( 'v_' . $field . $num . '_node.type = :val' . $num);
                $qb->setParameter('val' . $num, $module);

                $num = $this->vars($qb, 'v_' . $field . $num . '_node', $vars, $nodeService->getNodeCfg($module)['fields'], ++$num, $locales);
            }
        }
        if (isset($cfg['search'])) {
            $qb->innerJoin('n.variables', 'v_search' . $num);
            $qb->andWhere( 'lower(v_search' . $num . '.stringValue) like :_search or lower(v_search' . $num . '.textValue) like :_search');
            $qb->setParameter('_search', '%' . \mb_strtolower($cfg['search']) . '%');
            $num ++;
        }
        if (isset($cfg['where'])) {
            $num = $this->vars($qb, 'n', $cfg['where'], $fields, $num, $locales);
        }
        if (isset($cfg['position'])) {
            if ($cfg['position']) {
                $qb->leftJoin('n.positions', 'pos', Expr\Join::WITH, 'pos.target = :target')->setParameter('target', $cfg['position']);
                $qb->addOrderBy('pos.pos');
            } else {
                $qb->addOrderBy('n.pos');
            }
        }
        if (isset($cfg['orderBy'])) {
            // XXX support "node" store type, it's title
            foreach ($cfg['orderBy'] as $field => $direction) {
                $qb->leftJoin('n.variables', 'v_' . $field . $num, Join::WITH, 'v_' . $field . $num . '.name = \'' . $field . '\' and v_' . $field . $num . '.locale in (:locales' . $num . ')');
                $qb->setParameter('locales' . $num, $locales);
                $tmp = $fields[$field];
                switch ($tmp['storeType']) {
                    case 'string':
                        $qb->addOrderBy('v_' . $field . $num . '.stringValue', $direction);
                        break;
                    case 'integer':
                        $qb->addOrderBy('v_' . $field . $num . '.intValue', $direction);
                        break;
                    case 'date':
                    case 'datetime':
                        $qb->addOrderBy('v_' . $field . $num . '.dateTimeValue', $direction);
                        break;
                }
                $num ++;
            }
        } else {
            $qb->addOrderBy('n.pos');
        }
        if (isset($cfg['callback'])) {
            $cfg['callback']($qb);
        }

        if (isset($cfg['query'])) {
            return $qb;
        }

        if (isset($cfg['pagging']) && $cfg['pagging']) {
            $q = new Paginator($this->fetch($qb->getQuery()));
            $max = max((int) ceil($q->count() / $cfg['max']), 1);
            $page = max($cfg['page'], 1);
            $page = min($page, $max);
            $this->fetch($q->getQuery())
            ->setMaxResults($cfg['max'])
            ->setFirstResult(($page - 1) * $cfg['max']);
            return [
                'list' => $q,
                'current' => $page,
                'pages' => $max,
                'max' => $cfg['max'],
                'total' => $q->count()
            ];
        } elseif (isset($cfg['max'])) {
            $qb->setMaxResults($cfg['max']);
        }
        $list = $this->fetch($qb->getQuery())->execute();

        return [
            'list' => $list,
            'current' => 1,
            'max' => 1,
            'total' => count($list)
        ];
    }

    private function fetch(Query $query)
    {
        $query->setFetchMode(Node::class, 'variables', ClassMetadata::FETCH_EAGER);
        $query->setFetchMode(NodeVariable::class, 'valueFile', ClassMetadata::FETCH_EAGER);
        $query->setFetchMode(NodeVariable::class, 'valueNode', ClassMetadata::FETCH_EAGER);

        return $query;
    }

    public function checkInUse(Node $node, $except = null)
    {
        $qb = $this->_em->getRepository(Node::class)->createQueryBuilder('n');
        $cnt = $qb->select('count(n) c')
        ->where('n.parent = :node')
        ->setParameter('node', $node)
        ->getQuery()
        ->getSingleScalarResult();
        if ($cnt > 0) {
            return true;
        }

        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->_em->getRepository(NodeVariable::class)->createQueryBuilder('v');
        $qb->select('count(v.name) c');
        if ($except instanceof NodeVariable) {
            $qb->andWhere('not (v.node = :node2 and v.name = :name and v.pos = :pos and v.locale = :locale)')->setParameters([
                'name' => $except->getName(),
                'pos' => $except->getPos(),
                'locale' => $except->getLocale(),
                'node2' => $except->getNode()
            ]);
        }
        $qb->andWhere('v.nodeValue = :node')->setParameter('node', $node);
        $cnt = $qb->getQuery()->getSingleScalarResult();
        if ($cnt > 0) {
            return true;
        }

        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->_em->getRepository(Setting::class)->createQueryBuilder('v');
        $qb->select('count(v) c');

        if ($except instanceof Setting) {
            $qb->andWhere('v.id != :ignore')->setParameter('ignore', $except->getId());
        }
        $qb->andWhere('v.nodeValue = :node')->setParameter('node', $node);
        $cnt = $qb->getQuery()->getSingleScalarResult();
        if ($cnt > 0) {
            return true;
        }

        return false;
    }

    private function expandJoin(QueryBuilder $qb)
    {
        //$qb->distinct();
        return;
        $qb->join('n.variables', 'nv')->addSelect('nv');
        $qb->join('nv.fileValue', 'nf')->addSelect('nf');
        $qb->join('nv.nodeValue', 'nn')->addSelect('nn');

        $qb->join('nn.url', 'nnu')->addSelect('nnu');
        $qb->join('nn.variables', 'nnv')->addSelect('nnv');
        $qb->join('nnv.fileValue', 'nnf')->addSelect('nnf');

    }

    public function searchByConfig(int $service, array $types, array $cfg = [])
    {

        $qb = $this->_em->getRepository(Node::class)->createQueryBuilder('n');
        //$this->expandJoin($qb);
        $qb->leftJoin('n.urls', 'u')->addSelect('u');
        $qb->andWhere('n.type in (:type)')->setParameter('type', $types);
        $qb->andWhere('n.service = :service')->setParameter('service', $service);
        if ($cfg['locale'] ?? null) {
            $qb->andWhere('n.locale in (:locale)')->setParameter('locale', [$cfg['locale'], 'xx']);
        }


        $num = 0;

        if (isset($cfg['search']) ) {
            $qb->innerJoin('n.variables', 'v_search');
            $qb->leftJoin('v_search.nodeValue', 'subValue');
            $qb->leftJoin('subValue.variables', 'subVariables');
            $qb->andWhere( 'lower(v_search.stringValue) like :_search or lower(v_search.textValue) like :_search or lower(subVariables.stringValue) like :_search or lower(subVariables.textValue) like :_search');
            $qb->setParameter('_search', '%' . \mb_strtolower($cfg['search']) . '%');
            $num ++;
        }

        if (isset($cfg['callback'])) {
            $cfg['callback']($qb);
        }

        $sq = $this->_em->getRepository(Node::class)->createQueryBuilder('rn');
        $qb->select('n.id');
        $sq->andWhere($sq->expr()->in('rn.id', $qb->getQuery()->getDQL()))->setParameters($qb->getParameters());

        $qb = $sq;

        if (isset($cfg['query'])) {
            return $qb;
        }


        if (isset($cfg['pagging']) && $cfg['pagging']) {
            $page = max($cfg['page'], 1);
            $qb->setFirstResult(($page - 1) * $cfg['max']);
            $qb->setMaxResults($cfg['max']);
            $q = new Paginator($qb->getQuery());
            $max = max((int) ceil($q->count() / $cfg['max']), 1);
            $page = min($page, $max);
            /*$this->fetch($qb->getQuery())
            ->setMaxResults($cfg['max'])
            ->setFirstResult(($page - 1) * $cfg['max']);*/
            $list = [];
            foreach ($q as $item) {
                $list[] = $item;
            }
            return [
                'list' => $list,
                'current' => $page,
                'pages' => $max,
                'max' => $cfg['max'],
                'total' => $q->count(),
                'search' => $cfg['search']
            ];
        } elseif (isset($cfg['max'])) {
            $qb->setMaxResults($cfg['max']);
        }
        $list = $this->fetch($qb->getQuery())->execute();

        return [
            'list' => $list,
            'current' => 1,
            'max' => 1,
            'total' => count($list),
            'search' => $cfg['search']
        ];
    }

    public function fileInUse($id)
    {
        return $this->_em->getRepository(NodeVariable::class)->createQueryBuilder('s')->select('count(s.name)')->where('s.fileValue = :file')->setParameter('file', $id)->getQuery()->getSingleScalarResult();
    }
}

