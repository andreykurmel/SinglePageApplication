<?php

namespace Vanguard\Ideas\QueriesOnlyFromRepo;

//Special for Illuminate\Database\Eloquent\Model;
trait NoAccessDatabase
{
    /**
     * @throws \Exception
     */
    protected function throwError()
    {
        throw new \Exception('Entity Classes don`t have access to Query Builder!');
    }

    /**
     * @throws \Exception
     */
    protected function newBaseQueryBuilder()
    {
        $this->throwError();
    }

    /**
     * @param $class
     * @throws \Exception
     */
    protected function newRelatedInstance($class)
    {
        $this->throwError();
    }
}