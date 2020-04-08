<?php

use PHPUnit\Framework\TestCase;

use Cajudev\RestfulApi\CriteriaBuilder;
use Doctrine\Common\Collections\Criteria;

class CriteriaBuilderTest extends TestCase
{
    public function test_should_take_args_and_build_criteria()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('excluded', false));
        $criteria->andWhere(Criteria::expr()->contains('sit', 'lorem'));
        $criteria->andWhere(Criteria::expr()->contains('amet', 'lorem'));
        $criteria->andWhere(Criteria::expr()->eq('active', true));
        $criteria->orderBy(['amet' => 'desc']);
        $criteria->setFirstResult(20);
        $criteria->setMaxResults(20);

        $builder = new CriteriaBuilder([
            'page' => 2,
            'limit' => 20,
            'search' => 'lorem',
            'searchables' => ['sit', 'amet'],
            'sortables' => ['sit', 'amet'],
            'sort' => 'amet',
            'order' => 'desc',
            'active' => true,
        ]);

        $this->assertEquals($criteria, $builder->build());
    }
}
