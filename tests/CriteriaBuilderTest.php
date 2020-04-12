<?php

use PHPUnit\Framework\TestCase;

use Cajudev\Rest\CriteriaBuilder;
use Doctrine\Common\Collections\Criteria;

class CriteriaBuilderTest extends TestCase
{
    public function test_should_take_args_and_build_criteria()
    {
        $counter = Criteria::create();
        $counter->where(Criteria::expr()->eq('excluded', false));
        $counter->andWhere(Criteria::expr()->orX(
            Criteria::expr()->contains('sit', 'lorem'),
            Criteria::expr()->contains('amet', 'lorem')
        ));
        $counter->andWhere(Criteria::expr()->eq('active', true));
        $counter->orderBy(['amet' => 'desc']);

        $criteria = clone $counter;
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

        $this->assertEquals([$counter, $criteria], $builder->build());
    }
}
