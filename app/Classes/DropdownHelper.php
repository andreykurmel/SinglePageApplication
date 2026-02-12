<?php

namespace Vanguard\Classes;


use Illuminate\Support\Arr;
use Vanguard\Models\DDL;
use Vanguard\User;

class DropdownHelper
{
    /**
     * @param string $mirfield
     * @param $ref_row
     * @return mixed|string
     */
    public static function valOrShow(string $mirfield, $ref_row)
    {
        $val = $ref_row[$mirfield] ?? '';
        if (!empty($ref_row['_rc_' . $mirfield])) {
            $rc_obj = (array)($ref_row['_rc_' . $mirfield][$val] ?? []);
            $val = $rc_obj['show_val'] ?? $val;
        }
        return $val;
    }

    /**
     * @param string $mirfield
     * @param $ref_row
     * @return array|null
     */
    public static function forFrontEndSelect(string $mirfield, $ref_row)
    {
        $val = $ref_row[$mirfield] ?? '';
        $rc_obj = (array)($ref_row['_rc_' . $mirfield][$val] ?? []);

        $usr_str = '';
        $usr_obj = $ref_row['_u_' . $mirfield][$val] ?? null;
        if ($usr_obj) {
            $usr_str = $usr_obj instanceof User
                ? $usr_obj->full_name()
                : "{$usr_obj->first_name} {$usr_obj->last_name}";
        }

        return [
            'val' => $rc_obj['init_val'] ?? $val,
            'show' => $usr_str ?: $rc_obj['show_val'] ?? $val,
            'img' => Arr::first($rc_obj['img_vals'] ?? []),
            'ref_bg_color' => $rc_obj['ref_bg_color'] ?? '',
            'max_selections' => $rc_obj['max_selections'] ?? '',
        ];
    }

    /**
     * @param DDL $ddl
     * @param array $results
     * @return array
     */
    public static function ddlSort(DDL $ddl, array $results): array
    {
        if ($ddl->datas_sort) {
            usort($results, function ($a, $b) use ($ddl) {
                if ($ddl->ignore_lettercase) {
                    return strtolower($ddl->datas_sort) == 'desc'
                        ? strtolower($b['show']) <=> strtolower($a['show']) //desc
                        : strtolower($a['show']) <=> strtolower($b['show']);//asc
                } else {
                    return strtolower($ddl->datas_sort) == 'desc'
                        ? $b['show'] <=> $a['show'] //desc
                        : $a['show'] <=> $b['show'];//asc
                }
            });
        }
        return $results;
    }
}