<?php

namespace Vanguard\Classes;

use Exception;
use Illuminate\Support\Collection;
use Vanguard\Models\File;

class CalcSizeAllFiles
{
    /**
     *
     */
    public static function run()
    {
        File::chunk(100, function (Collection $files) {
            $files->each(function (File $file) {
                try {
                    $content = file_get_contents(storage_path('app/public/') . $file->filepath . $file->filename);
                    $file->filesize = strlen($content);
                    $file->save();
                } catch (Exception $exception) {
                }
            });
        });
    }
}