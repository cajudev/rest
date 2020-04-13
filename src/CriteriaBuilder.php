<?php

namespace Cajudev\Rest;

use Doctrine\Common\Collections\Criteria;

use Cajudev\Rest\Utils\Setter;
use Cajudev\Rest\Utils\Sanitizer;


class CriteriaBuilder
{
    private int $page = 1;
    private int $limit = 10;
    private string $search = '';
    private string $sort = '';
    private string $order = 'asc';
    private ?bool $active = null;
    private array $searchables = [];
    private array $sortables = [];

    public function __construct($params)
    {
        $setter = new Setter($this, $params);
        $setter->set(Setter::MODE_SAFE);
    }

    public function build()
    {
        $counter = Criteria::create();

        $counter->where(Criteria::expr()->eq('excluded', false));
        
        if ($this->search) {
            $contains = [];
            foreach ($this->searchables as $searchable) {
                $contains[] = Criteria::expr()->contains($searchable, $this->search);
            }
            $counter->andWhere(Criteria::expr()->orX(...$contains));
        }
        
        if (is_bool($this->active)) {
            $counter->andWhere(Criteria::expr()->eq('active', $this->active));
        }

        if (in_array($this->sort, $this->sortables)) {
            $counter->orderBy([$this->sort => $this->order]);
        }
        
        
        $criteria = clone $counter;
        
        $offset = ($this->page - 1) * $this->limit;
        $criteria->setFirstResult($offset);
        $criteria->setMaxResults($this->limit);

        return [$counter, $criteria];
    }

    public function setPage($page) { if ($page) ($this->page = $page); }
    public function setLimit($limit) { if ($limit) ($this->limit = $limit); }
    public function setSearch($search) { if ($search) ($this->search = $search); }
    public function setSort($sort) { if ($sort) ($this->sort = $sort); }
    public function setOrder($order) { if ($order) ($this->order = $order); }
    public function setSearchables($searchables) { if ($searchables) ($this->searchables = $searchables); }
    public function setSortables($sortables) { if ($sortables) ($this->sortables = $sortables); }
    public function setActive($active) { if ($active) ($this->active = Sanitizer::boolean($active)); }
}