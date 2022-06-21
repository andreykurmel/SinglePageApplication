<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Vanguard\Classes\MselConvert;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableFieldService;

class MapRepository
{
    /**
     * @var int
     */
    protected $max_rows = 25000;

    /**
     * @var HelperService
     */
    protected $service;

    /**
     * TableDataRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Markers or Bounds for Table with GSI addon.
     * @param $type
     * @param Table $table
     * @param array $data
     * @param $user_id
     * @return array
     */
    public function getMapThing($type, Table $table, Array $data, $user_id)
    {
        $table->load(['_fields']);
        $finder = $this->gsiFinder($table);

        $lat_header = $finder->_fields->where('is_lat_field', '=', 1)->first();
        $long_header = $finder->_fields->where('is_long_field', '=', 1)->first();
        $icon_header = $finder->_fields->where('id', '=', $finder->map_icon_field_id)->first();

        if ($lat_header && $long_header) {
            ini_set('memory_limit', '256M');
            //select only 'icon' and 'lat','lng'
            $lat_field = $lat_header ? $lat_header->field : '""';
            $lng_field = $long_header ? $long_header->field : '""';
            $icn_field = $icon_header ? $icon_header->field : '""';

            $can_cache = !empty($data['special_params']['list_view_hash']) && $finder->version_hash
                ? $finder->version_hash.'_'.$data['special_params']['list_view_hash']
                : '';

            if ($can_cache) {
                $rows = \Cache::store('redis')->rememberForever($can_cache, function () use ($table, $data, $user_id, $lat_field, $lng_field, $icn_field) {
                    return $this->getMapRows($table, $data, $user_id, $lat_field, $lng_field, $icn_field);
                });
            } else {
                $rows = $this->getMapRows($table, $data, $user_id, $lat_field, $lng_field, $icn_field);
            }

            switch ($type) {
                case 'Bounds':
                    $res = $this->getMapBounds($rows, $lat_header->field, $long_header->field);
                    break;
                case 'Markers':
                    $res = $this->getMapMarkers($rows, $data);
                    break;
                default:
                    $res = [];
                    break;
            }

            return $res;
        } else {
            return [];
        }
    }

    /**
     * @param Table $table
     * @param array $data
     * @param $user_id
     * @param string $lat_field
     * @param string $lng_field
     * @param string $icn_field
     * @return Collection
     */
    protected function getMapRows(Table $table, Array $data, $user_id, string $lat_field, string $lng_field, string $icn_field)
    {
        return $this->gsiSql($table, $data, $user_id)
            ->getQuery()
            ->selectRaw(implode(', ', [
                'id',
                $lat_field . ' as lat',
                $lng_field . ' as lng',
                $icn_field . ' as icon'
            ]))
            ->limit($this->max_rows)
            ->get();
    }

    /**
     * @param Table $table
     * @param string $from_reference
     * @return mixed|Table
     */
    protected function gsiFinder(Table $table, string $from_reference = 'map_position_refid')
    {
        $rc = null;
        if ($table->{$from_reference}) {
            $rc = (new TableRefConditionRepository())->loadRefCondWithRelations($table->{$from_reference});
        }
        return $rc ? $rc->_ref_table : $table; //From what table we use fields
    }

    /**
     * @param Table $table
     * @param $row_id
     * @param $user_id
     * @return TableDataQuery
     */
    protected function markerSql(Table $table, $row_id, $user_id)
    {
        $db = $table->db_name;
        //get SQL
        $sql = new TableDataQuery($table, true, $user_id);
        //join other table (via ref conditions)
        if ($table->map_position_refid) {
            $rc = (new TableRefConditionRepository())->loadRefCondWithRelations($table->map_position_refid);
            $sql->joinRefCondition($rc);
            $db = $rc->_ref_table->db_name;
        }
        $sql->getQuery(false)->where($db.'.id', $row_id, $row_id);
        return $sql;
    }

    /**
     * @param Table $table
     * @param array $data
     * @param $user_id
     * @return TableDataQuery
     */
    protected function gsiSql(Table $table, Array $data, $user_id)
    {
        if ($table->map_position_refid) {
            $rc = (new TableRefConditionRepository())->loadRefCondWithRelations($table->map_position_refid);
            //Query for referenced Table
            $ref_sql = new TableDataQuery($rc->_ref_table, true, $user_id);
            //apply linked via RC 'radius search'
            $ref_sql->radiusSearch($data);
            unset($data['radius_search']);
            //apply searching from currect table as SubQuery
            $ref_sql->getRowsForRefCondition($rc, $data);
            return $ref_sql;
        } else {
            //Query for current Table
            $sql = new TableDataQuery($table, true, $user_id);
            //apply searching, filtering, row rules to getRows
            $sql->testViewAndApplyWhereClauses($data, $user_id);
            return $sql;
        }
    }

    /**
     * Get marker's bounds for table rows.
     *
     * @param Collection $rows
     * @param $lat_field
     * @param $long_field
     * @return array
     */
    protected function getMapBounds(Collection $rows, $lat_field, $long_field)
    {
        return [
            'top' => $rows->where('lat')->max('lat') ?: 1,
            'bottom' => $rows->where('lat')->min('lat') ?: -1,
            'left' => $rows->where('lng')->max('lng') ?: 1,
            'right' => $rows->where('lng')->min('lng') ?: -1,
        ];
    }

    /**
     * @param Table $table
     * @param string $search
     * @param array $columns
     * @param array $special
     * @return array
     */
    public function searchDataInMap(Table $table, string $search, array $columns, array $special = [])
    {
        $sys_f = $this->service->system_fields;
        $columns = array_filter($columns, function ($col) use ($sys_f) {
            return !in_array($col, $sys_f);
        });
        $data = [
            'search_words' => [
                ['type' => 'and', 'word' => $search]
            ],
            'search_columns' => $columns,
            'special_params' => $special,
        ];

        $uid = $special['_user_id'] ?? auth()->id();
        $sql = new TableDataQuery($table, false, $uid);
        $sql->testViewAndApplyWhereClauses($data, $uid);
        if ($table->map_position_refid) {
            $rc = (new TableRefConditionRepository())->loadRefCondWithRelations($table->map_position_refid);
            $sql->joinRefCondition($rc);
        }

        $rows = $sql->getQuery()
            ->limit($table->search_results_len ?: 10)
            ->get()
            ->toArray();

        $finder = $this->gsiFinder($table);
        $lat_fld = $finder->_fields->where('is_lat_field', '=', 1)->first();
        $long_fld = $finder->_fields->where('is_long_field', '=', 1)->first();

        $res = [];
        foreach ($rows as $row) {
            $lat = ($lat_fld ? $row[$lat_fld->field] : 0);
            $long = ($long_fld ? $row[$long_fld->field] : 0);
            $res[] = [
                'id' => $row['id'],
                'text' => $this->getTextBySearchDisplay($table, $row, $columns, $search) . ' (lat:' . $lat . ', long:' . $long . ')',
                'row' => $row,
                'lat' => $lat,
                'long' => $long,
            ];
        }
        return ['results' => $res];
    }

    /**
     * Show columns where 'Search Display' is active
     *
     * @param Table $table
     * @param array $row
     * @param array $columns
     * @param string $search
     * @return mixed|string
     */
    protected function getTextBySearchDisplay(Table $table, array $row, array $columns, string $search)
    {
        $text = '';
        if ($table->_fields->where('is_search_autocomplete_display', '=', 1)->count()) {
            $cols = [];
            foreach ($table->_fields as $col) {
                if ($col->is_search_autocomplete_display && !empty($row[$col->field])) {
                    $cols[] = $row[$col->field];
                }
            }
            $text = implode(', ', $cols);
        } else {
            $text = $this->getRowText($search, $row, $columns);
        }
        return $text;
    }

    /**
     * Show columns in which 'search' term is found
     *
     * @param string $search
     * @param array $row
     * @param array $columns
     * @return string
     */
    protected function getRowText(string $search, array $row, array $columns)
    {
        $text = '';
        foreach ($columns as $field) {
            if ($row[$field] && preg_match('/' . $search . '/i', $row[$field])) {
                $text = $row[$field];
                break;
            }
        }
        return $text;
    }

    /**
     * Get markers from table rows.
     *
     * @param Collection $rows
     * @param array $data
     * @return array
     */
    protected function getMapMarkers(Collection $rows, Array $data)
    {
        $gr = 5; //grouping in 32x32 pixel square (2^5);

        $res = [];
        $zoom = $data['map_bounds']['zoom'] ?? 0;
        $zoom = min($zoom, 21);

        foreach ($rows as $row) {
            $row->lat = floatval(preg_replace('/[^\d\.-]/i', '', $row->lat));
            $row->lng = floatval(preg_replace('/[^\d\.-]/i', '', $row->lng));

            $lat = $this->latToY($row->lat) >> ((21 - $zoom) + $gr);
            $lng = $this->lonToX($row->lng) >> ((21 - $zoom) + $gr);
            if ($zoom >= 15) {
                //Disabled Clustering
                if (!empty($res[$lat . '_' . $lng])) {
                    $rndx = rand(10,99); $rndy = rand(10,99); $t = 100000;
                    $lat = $lat . $rndx;
                    $lng = $lng . $rndy;
                    $res[$lat . '_' . $lng] = $this->directMarker($row, $rndx/$t, $rndy/$t);
                } else {
                    $res[$lat . '_' . $lng] = $this->directMarker($row);
                }
            } else {
                //Enabled Clustering
                if (!empty($res[$lat . '_' . $lng])) {
                    $res[$lat . '_' . $lng] = $this->clusterMarker($row, $res[$lat . '_' . $lng], $lat, $lng, $zoom, $gr);
                } else {
                    $res[$lat . '_' . $lng] = $this->directMarker($row);
                }
            }
        }

        //Chunks temporary removed
        /*$len = $sql->count();
        $chunk_len = 75000;
        for ($i = 0; $i * $chunk_len < $len; $i++) {

            $rows = (clone $sql)
                ->offset($i * $chunk_len)
                ->limit($chunk_len)
                ->get();

            //Chunk
        }*/

        return [
            'markers' => $res,
            'warning' => $rows->count() >= $this->max_rows ? 'Map markers limits is reached ('.$this->max_rows.')' : '',
        ];
    }

    /**
     * @param $row
     * @param null $rndx
     * @param null $rndy
     * @return array
     */
    protected function directMarker($row, $rndx = null, $rndy = null)
    {
        return [
            'cnt' => 1,
            'lat' => $rndx ? $row->lat+$rndx : $row->lat,
            'lng' => $rndy ? $row->lng+$rndy : $row->lng,
            'icon' => $row->icon,
            'id' => $row->id,
        ];
    }

    /**
     * @param $row
     * @param array $marker
     * @param float $lat
     * @param float $lng
     * @param int $zoom
     * @param int $gr
     * @return array
     */
    protected function clusterMarker($row, array $marker, float $lat, float $lng, int $zoom, int $gr)
    {
        $marker['cnt']++;
        if ($marker['id']) {
            $marker['id'] = null;
            $marker['icon'] = $this->getClusterIcon($marker['cnt']);
            //get center of clustering squares
            $marker['lat'] = $this->YToLat(($lat + 0.5) * pow(2, (21 - $zoom) + $gr));
            $marker['lng'] = $this->XToLon(($lng + 0.5) * pow(2, (21 - $zoom) + $gr));
        }
        return $marker;
    }

    /**
     * map convert functions
     * LAT/LNG to Google Map's Pixels
     * source of idea: https://appelsiini.net/2008/introduction-to-marker-clustering-with-google-maps/
     */
    protected function latToY($lat)
    {
        return round(268435456 - 85445659.4471 * log((1 + sin($lat * pi() / 180)) / (1 - sin($lat * pi() / 180))) / 2);
    }

    protected function lonToX($lon)
    {
        return round(268435456 + 85445659.4471 * $lon * pi() / 180);
    }

    protected function getClusterIcon($cnt)
    {
        return '/images/m' . min(strlen($cnt), 5) . '.png';
    }

    protected function YToLat($Y)
    {
        return asin((exp((268435456 - $Y) / 85445659.4471 * 2) - 1) / (exp((268435456 - $Y) / 85445659.4471 * 2) + 1)) / pi() * 180;
    }

    protected function XToLon($X)
    {
        return ($X - 268435456) / 85445659.4471 / pi() * 180;
    }

    /**
     * @param Table $table
     * @param array $present
     * @return array
     */
    protected function markerFields(Table $table, array $present)
    {
        $table_fields = (new TableFieldService())->getWithSettings($table, auth()->id());
        $table_fields->load('_links');
        foreach ($table_fields as $fld) {
            if ($fld->info_box && !$fld->is_info_header_field) {
                $present[] = $fld;
            }
        }
        return $present;
    }

    /**
     * @param Table $table
     * @param int $row_id
     * @return array
     */
    public function getMarkerPopup(Table $table, int $row_id)
    {
        $finder = $this->gsiFinder($table);

        $row = $this->markerSql($table, $row_id, auth()->id())
            ->getQuery()
            ->first();
        $collect = (new TableDataRowsRepository())->attachSpecialFields(collect([$row]), $table, auth()->id());
        $row = $collect->first();

        $marker_fields = [];
        if ($table->map_position_refid) {
            $marker_fields = $this->markerFields($table, []);
            $rc = (new TableRefConditionRepository())->loadRefCondWithRelations($table->map_position_refid);
            $marker_fields = array_merge($marker_fields, $this->markerFields($rc->_ref_table, $marker_fields));
            $collect = (new TableDataRowsRepository())->attachSpecialFields(collect([$row]), $finder, auth()->id());
            $row = $collect->first();
        }

        return [
            'marker_row' => $row ? $row->toArray() : [],
            'marker_fields_tb' => ['_fields' => $marker_fields],
            'marker_hdr_tb' => $this->popupHeaderTb($table),
        ];
    }

    /**
     * @param Table $table
     * @return array
     */
    protected function popupHeaderTb(Table $table)
    {
        $header_tb = $this->gsiFinder($table, 'map_popup_hdr_id');
        $header_tb->load([
            '_fields' => function ($q) {
                $q->with('_links');
            }
        ]);
        $header_tb->_fields = $header_tb->_fields->where('is_info_header_field', '=', 1)->values();
        return $header_tb->attributesToArray();
    }
}