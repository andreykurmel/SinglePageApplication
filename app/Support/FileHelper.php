<?php

namespace Vanguard\Support;

class FileHelper
{
    /**
     * @param string $path
     * @param bool $absolute
     * @return string
     */
    public static function tmpImportFolder(string $path = '', bool $absolute = true): string
    {
        return $absolute
            ? storage_path('app/tmp_import/' . $path)
            : 'tmp_import/' . $path;
    }

    /**
     * @param string $filepath
     * @return string
     */
    public static function extension(string $filepath): string
    {
        return pathinfo($filepath, PATHINFO_EXTENSION);
    }

    /**
     * @param string $filepath
     * @return string
     */
    public static function name(string $filepath): string
    {
        return pathinfo($filepath, PATHINFO_BASENAME);
    }
}