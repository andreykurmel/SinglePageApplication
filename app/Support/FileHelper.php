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

    /**
     * @param string $url
     * @return bool
     */
    public static function check_url(string $url): bool
    {
        $headers = @get_headers($url);
        return $headers && str_contains($headers[0], '200');
    }

    /**
     * @param string $filepath
     * @return bool
     */
    public static function exist(string $filepath): bool
    {
        if (filter_var($filepath, FILTER_VALIDATE_URL)) {
            return self::check_url($filepath);
        }
        return file_exists($filepath);
    }
}