<?php

namespace Cajudev\Rest;

use Doctrine\Common\Collections\Criteria;

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
        $this->page = $params['page'] ?? $this->page;
        $this->limit = $params['limit'] ?? $this->limit;
        $this->search = $params['search'] ?? $this->search;
        $this->sort = $params['sort'] ?? $this->sort;
        $this->order = $params['order'] ?? $this->order;
        $this->active = $params['active'] ?? $this->active;
        $this->searchables = $params['searchables'] ?? $this->searchables;
        $this->sortables = $params['sortables'] ?? $this->sortables;
    }

    public function build()
    {
        $criteria = Criteria::create();

        $criteria->where(Criteria::expr()->eq('excluded', false));
        
        if ($this->search) {
            foreach ($this->searchables as $searchable) {
                $criteria->andWhere(Criteria::expr()->contains($searchable, $this->search));
            }
        }
        
        if (is_bool($this->active)) {
            $criteria->andWhere(Criteria::expr()->eq('active', $this->active));
        }

        if (in_array($this->sort, $this->sortables)) {
            $criteria->orderBy([$this->sort => $this->order]);
        }
        
        $offset = ($this->page - 1) * $this->limit;
        $criteria->setFirstResult($offset);
        $criteria->setMaxResults($this->limit);

        return $criteria;
    }
}
