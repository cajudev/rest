<?php

namespace Cajudev\RestfulApi;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

abstract class Repository extends EntityRepository
{
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return new EntityList(parent::findBy($criteria, $orderBy, $limit, $offset));
    }

    public function matching(Criteria $criteria)
    {
        return new EntityList(parent::matching($criteria));
    }
}
