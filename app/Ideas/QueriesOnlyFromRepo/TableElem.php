<?php

namespace Vanguard\Ideas\QueriesOnlyFromRepo;


use \ArrayAccess;
use Vanguard\Ideas\Repos\CachedTableRepository;

class TableElem implements ArrayAccess
{
    use AttributeObject;

    /**
     * @return mixed
     */
    public function _table()
    {
        return (new CachedTableRepository())->get( [$this->id] );
    }
}
