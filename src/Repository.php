<?php

namespace Cajudev\Rest;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

use Cajudev\Rest\Collections\EntityCollection;

abstract class Repository extends EntityRepository
{
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return new EntityCollection(parent::findBy($criteria, $orderBy, $limit, $offset));
    }

    public function matching(Criteria $criteria)
    {
        return new EntityCollection(parent::matching($criteria));
    }
}
