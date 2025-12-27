<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait Manageable
{
     /**
      * getSpecificLogByColumn
      *
      * @param Model $model
      * @param string $column
      * @param mixed $value
      * @param array|null|null $attributes
      * 
      * @return Model
      */
     public function getSpecificLogByColumn(Model $model, string $column, mixed $value, array|null $attributes = null): Model|null {

          return $model::when($attributes, fn(Builder $q): Builder =>
                                   $q->where($attributes))
                              ->where($column, $value)
                              ->first();
     }
}
