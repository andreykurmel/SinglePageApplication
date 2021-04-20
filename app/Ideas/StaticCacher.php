<?php

namespace Vanguard\Ideas;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

trait StaticCacher
{
    protected static $static_cache = [];

    /**
     * @param string $name
     */
    protected function initCache(string $name)
    {

        if (empty(self::$static_cache[$name])) {
            self::$static_cache[$name] = [];
        }
    }

    /**
     * @param array $ids
     * @param string $name
     * @return array
     */
    protected function getCache(array $ids, string $name = 'cache')
    {
        $this->initCache($name);
        $objects = collect([]);
        $found_ids = [];
        foreach ($ids as $id) {
            if (!empty(self::$static_cache[$name][$id])) {
                $objects[] = self::$static_cache[$name][$id];
                $found_ids[] = $id;
            }
        }
        $rest_ids = array_diff($ids, $found_ids);
        return [$objects, $rest_ids];
    }

    /**
     * @param Collection $items
     * @param string $field
     * @param string $name
     */
    protected function setCache(Collection $items, string $field = 'id', string $name = 'cache')
    {
        $this->initCache($name);
        foreach ($items as $object) {
            self::$static_cache[$name][ $object[$field] ] = $object;
        }
    }
}