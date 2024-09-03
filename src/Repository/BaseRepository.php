<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Base doctrine repository class for entities.
 *
 * @author  Divyasree MP <divyasree67@gmail.com>
 */
class BaseRepository
{
    public $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function insert($entity): int
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity->getId();
    }

    public function persistEntity($entity)
    {
        $this->em->persist($entity);
    }

    public function flushEntity()
    {
        $this->em->flush();
    }

    public function findById($id, $entityClass)
    {
        $entityData = $this->em->getRepository($entityClass)->find($id);

        return $entityData;
    }

    public function findByOneCriteria($criteria, $entityClass)
    { 
        $repository = $this->em->getRepository($entityClass);
        $entityData = $repository->findOneBy($criteria);

        return $entityData;
    }

    public function findAll($entityClass)
    { 
        $repository = $this->em->getRepository($entityClass);
        $entityData = $repository->findAll();

        return $entityData;
    }

    public function findByCriteria(
        $criteria, $entityClass, $orderBy = null, $limit = null, $offset = null)
    {
        $repository = $this->em->getRepository($entityClass);
        $entityData = $repository->findBy($criteria, $orderBy, $limit, $offset);

        return $entityData;
    }
}
?>