<?php

namespace Vanguard\Modules\Permissions;


use Vanguard\Models\Dcr\DcrLinkedTable;

class PermissionLinkedDcr extends PermissionObject
{
    /**
     * @param DcrLinkedTable $linked
     */
    public function setTableLinked(DcrLinkedTable $linked)
    {
        $this->setBasicsLinked($linked);
        $this->setTablePermis($linked->_linked_permission);
    }

    /**
     * @param DcrLinkedTable $linked
     */
    protected function setBasicsLinked(DcrLinkedTable $linked)
    {
        $this->can_add = 1;
    }
}