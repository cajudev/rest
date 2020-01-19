<?php

namespace Cajudev\RestfulApi;

use Doctrine\ORM\EntityRepository;

abstract class Repository extends EntityRepository
{
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return new EntityList(parent::findBy($criteria, $orderBy, $limit, $offset));
    }
}
