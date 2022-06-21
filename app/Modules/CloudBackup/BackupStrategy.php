<?php

namespace Vanguard\Modules\CloudBackup;


use Vanguard\Models\User\UserCloud;

interface BackupStrategy
{
    public function __construct(UserCloud $cloud);

    public function upload(string $source_filepath, string $target_filepath);
}