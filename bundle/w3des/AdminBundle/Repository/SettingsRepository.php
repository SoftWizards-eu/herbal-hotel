<?php
namespace w3des\AdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SettingsRepository extends EntityRepository
{

    public function findByNames($service, array $names, array $locales)
    {
        return $this->findBy([
            'name' => $names,
            'locale' => $locales,
            'service' => $service
        ], [
            'name' => 'asc',
            'locale' => 'asc',
            'pos' => 'asc'
        ]);
    }

    public function fileInUse($id)
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.name)')
            ->where('s.fileValue = :file')
            ->setParameter('file', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

