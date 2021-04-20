<?php

namespace Vanguard\Ideas\Repos;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

trait ModelCacherTrait
{
    /**
     * @var string
     */
    protected $cache_str = '';

    //Key prefix example: "{$this->cache_str}.id.15"
    /**
     * @param array $array_values
     * @param string $field_key
     * @param \Closure $closure
     * @return Collection
     */
    protected function rememberArray(array $array_values, string $field_key, \Closure $closure)
    {
        $cached_objects = collect([]);
        $found_items = [];
        foreach ($array_values as $val) {
            $obj = \Cache::store('array')->get( "{$this->cache_str}.{$field_key}.{$val}" );
            if ($obj) {
                $cached_objects[] = $obj;
                $found_items[] = $val;
            }
        }

        $rest_values = array_diff($array_values, $found_items);
        $loaded_objects = $rest_values
            ? call_user_func($closure, $rest_values)
            : collect([]);
        foreach ($loaded_objects as $loaded) {
            $new_val = $loaded[$field_key];
            \Cache::store('array')->forever( "{$this->cache_str}.{$field_key}.{$new_val}", $loaded );
        }

        return $cached_objects->merge($loaded_objects);
    }

    /**
     * @param $value
     * @param string $field_key
     * @param \Closure $closure
     * @return mixed
     */
    protected function rememberSingle($value, string $field_key, \Closure $closure)
    {
        return \Cache::store('array')->rememberForever("{$this->cache_str}.{$field_key}.{$value}", function () use ($closure) {
            return call_user_func($closure);
        });
    }



    //Key prefix example: "{$this->cache_str}.special_object"
    /**
     * @param $key
     * @param \Closure $closure
     * @return mixed
     */
    protected function rememberObject($key, \Closure $closure)
    {
        return \Cache::store('array')->rememberForever("{$this->cache_str}.{$key}", function () use ($closure) {
            return call_user_func($closure);
        });
    }
}