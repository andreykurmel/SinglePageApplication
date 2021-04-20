<?php

namespace Vanguard\Modules\CloudBackup;


use Vanguard\Models\User\UserCloud;

interface BackupStrategy
{
    public function __construct(UserCloud $cloud);

    public function upload(string $source_path, string $file_name, string $target_path);
}