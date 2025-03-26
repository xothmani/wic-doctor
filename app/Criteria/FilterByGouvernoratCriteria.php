<?php

namespace App\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;
use Illuminate\Support\Facades\DB;

class FilterByGouvernoratCriteria implements CriteriaInterface
{
    protected $gouvernoratList;

    public function __construct($gouvernoratList)
    {
        $this->gouvernoratList = $gouvernoratList;
    }

   public function apply($model, RepositoryInterface $repository)
    {
        return $model->whereHas('address', function ($query) {
            $query->whereIn('gouvernorat->fr', $this->gouvernoratList);
        });
    }
}

