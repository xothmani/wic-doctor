<?php


namespace App\Criteria\Specialities;



use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CountryCriteria implements CriteriaInterface
{
    protected $country;

    public function __construct($country = 'Tunisie')
    {
        $this->country = $country;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        return $model->where('pays', $this->country);
    }
}
