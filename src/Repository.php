<?php

namespace Cajudev\Rest;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

use Cajudev\Rest\Collections\EntityCollection;

abstract class Repository extends EntityRepository
{
    /**
     * @Override
     */
    public function find($id, $lockMode = NULL, $lockVersion = NULL)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @Override
     */
    public function findOneBy(array $criteria, array $orderBy = null) {
        return parent::findOneBy($criteria + ['excluded' => false], $orderBy);
    }

    /**
     * @Override
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return new EntityCollection(parent::findBy($criteria, $orderBy, $limit, $offset));
    }

    /**
     * @Override
     */
    public function matching(Criteria $criteria)
    {
        return new EntityCollection(parent::matching($criteria));
    }
}
