<?php

namespace App\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class FilterByGouvernoratCriteria implements CriteriaInterface
{
    protected $gouvernoratList;

    public function __construct(array $gouvernoratList)
    {
        $this->gouvernoratList = $gouvernoratList;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        // If gouvernorat is provided, filter by the gouvernorat JSON field
        return $model->whereHas('address', function($query) {
            // Assuming the 'gouvernorat' field is a JSON column,
            // and we are matching the 'fr' key inside the JSON object
            $query->whereJsonContains('gouvernorat->fr', $this->gouvernoratList);
        });
    }
}
