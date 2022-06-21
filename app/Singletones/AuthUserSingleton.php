<?php

namespace Vanguard\Singletones;

interface AuthUserSingleton
{
    public function getMenuTree();

    public function getMenuTreeFolder(int $folder_id, string $structure = 'private');

    public function getUserGroupsMember();

    public function getManagerOfUserGroups(bool $unserscored = false);

    public function getTablePermissionIdsMember();
}