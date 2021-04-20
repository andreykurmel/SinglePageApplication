<?php

namespace Vanguard\Singletones;

interface AuthUserSingleton
{
    public function getMenuTree();

    public function getMenuTreeFolder(int $folder_id);

    public function getUserGroupsMember();

    public function getIdsUserGroupsEditAdded();

    public function getTablePermissionIdsMember();
}