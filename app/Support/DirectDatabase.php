<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 23.07.21
 * Time: 16:33
 */

namespace Vanguard\Support;


use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DirectDatabase
{
    /**
     * @param int $table_id
     * @return array : [id, incoming_allow, user_id, table_name, table_id, ref_cond_name, use_category, use_name]
     */
    public static function loadIncomingLinks(int $table_id)
    {
        $rg = self::partIncomingLink([DB::raw('"RowGroup" as use_category'),'trg.name as use_name'], $table_id)
            ->join('table_row_groups as trg', 'trg.row_ref_condition_id', '=', 'trc.id')
            ->get();

        $ddl = self::partIncomingLink([DB::raw('"DDL" as use_category'),'d.name as use_name'], $table_id)
            ->join('ddl_references as dr', 'dr.table_ref_condition_id', '=', 'trc.id')
            ->join('ddl as d', 'd.id', '=', 'dr.ddl_id')
            ->get();

        $link = self::partIncomingLink([DB::raw('"Link" as use_category'),'tf.name as use_name'], $table_id)
            ->join('table_field_links as tfl', 'tfl.table_ref_condition_id', '=', 'trc.id')
            ->join('table_fields as tf', 'tf.id', '=', 'tfl.table_field_id')
            ->get();

        $merge = array_merge(
            $rg->toArray(),
            $ddl->toArray(),
            $link->toArray()
        );

        $result = [];
        foreach ($merge as $el) {
            $res = (array)$el;
            $res['_u_user_id'] = [];
            $res['_u_user_id'][$res['user_id']] = [
                'id' => $res['user_id'],
                'username' => $res['user_username'],
                'first_name' => $res['user_first_name'],
                'last_name' => $res['user_last_name'],
                'avatar' => $res['user_avatar'],
                'email' => $res['user_email'],
            ];
            $result[] = $res;
        }

        return $result;
    }

    /**
     * @param array $selector
     * @param int $table_id
     * @return Builder
     */
    protected static function partIncomingLink(array $selector, int $table_id)
    {
        return DB::query()
            ->select(array_merge([
                'u.username as user_username',
                'u.first_name as user_first_name',
                'u.last_name as user_last_name',
                'u.avatar as user_avatar',
                'u.email as user_email',
                'trc.id',
                'trc.incoming_allow',
                'u.id as user_id',
                't.id as table_id',
                't.name as table_name',
                'trc.name as ref_cond_name',
            ], $selector))
            ->from('table_ref_conditions as trc')
            ->join('tables as t', 't.id', '=', 'trc.table_id')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where('trc.ref_table_id', '=', $table_id);
    }
}