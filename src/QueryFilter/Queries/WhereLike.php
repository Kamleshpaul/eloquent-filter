<?php

namespace eloquentFilter\QueryFilter\Queries;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class WhereLike
 * @package eloquentFilter\QueryFilter\Queries
 */
class WhereLike extends BaseClause
{
    /**
     * @param $query
     * @return Builder
     */
    public function apply($query): Builder
    {
        return $query->where("$this->filter", 'like', $this->values['like']);
    }
}
