<?php

namespace Vanguard\Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Vanguard\Models\Table\Table;

class BiPivotMaker
{
    protected $maxLvl = 5;

    protected $table = null;
    protected $all_settings = [];
    protected $about_1 = [];
    protected $about_2 = [];
    protected $about_3 = [];

    protected $all_verticals = [];
    protected $all_horizontals = [];

    protected $pivot_vert = [];
    protected $pivot_hor = [];

    protected $fld_keys = [];
    protected $sorted_values = [];
    protected $corr_dbs = [];
    protected $all_variants = [
        'usrs' => null,
        'rcs' => null,
    ];
    protected $syskeys = [
        '__show',
        '__span_len',
        '__sub_total'
    ];

    protected $rows = [];
    protected $columns = [];

    /**
     * @param Table $table
     * @param array $all_settings
     * @param array $pivot_data
     * @param array $pivot_data2
     * @param array $pivot_data3
     */
    public function __construct(Table $table, array $all_settings, array $pivot_data, array $pivot_data2 = [], array $pivot_data3 = [])
    {
        $this->table = $table;
        $this->all_settings = $all_settings;
        $this->about_1 = $this->filterData($pivot_data, true);
        $this->about_2 = $this->filterData($pivot_data2, true);
        $this->about_3 = $this->filterData($pivot_data3, true);

        $this->pivot_vert = $all_settings['pivot_table']['vertical'] ?? [];
        $this->pivot_hor = $all_settings['pivot_table']['horizontal'] ?? [];

        $this->prepareData( $this->filterData($pivot_data, false) );
    }

    /**
     * Prepare columns and rows
     */
    protected function prepareData(array $input): void
    {
        $objarr = [];
        $objstr = [];
        $objobj = [];
        for ($i = 1; $i <= $this->maxLvl; $i++) {
            array_push($this->fld_keys, 'vert_l' . $i);
            array_push($this->fld_keys, 'hor_l' . $i);
            $objarr['vert_l' . $i] = [];
            $objarr['hor_l' . $i] = [];
            $objstr['vert_l' . $i] = '';
            $objstr['hor_l' . $i] = '';
            $objobj['vert_l' . $i] = [];
            $objobj['hor_l' . $i] = [];
        }
        $this->sorted_values = $objarr;
        $this->corr_dbs = $objarr;
        $this->all_variants['usrs'] = $objobj;
        $this->all_variants['rcs'] = $objobj;

        foreach ($this->fld_keys as $key) {
            $this->corr_dbs[$key] = Arr::first($input)['_db_' . $key] ?? '';
            $this->sorted_values[$key] = Arr::sort(array_unique(Arr::pluck($input, $key)));

            $usrs = [];
            $rcs = [];
            foreach ($input as $row) {
                foreach ($row['_u_' . $key] ?? [] as $k => $item) {
                    $usrs[$k] = $item;
                }
                foreach ($row['_rc_' . $key] ?? [] as $k => $item) {
                    $rcs[$k] = $item;
                }
            }
            $this->all_variants['usrs'][$key] = $usrs;
            $this->all_variants['rcs'][$key] = $rcs;
        }

        $allvert = array_merge($this->about_1, $this->about_2, $this->about_3);
        $this->all_verticals = $this->addZeroValues($allvert, 'vert');
        $allhors = array_merge($this->about_1, $this->about_2, $this->about_3);
        $this->all_horizontals = $this->addZeroValues($allhors, 'hor');

        $this->subtotalToSorts();

        $this->rows = $this->groupViewedData($this->all_verticals, 'vert');
        $this->columns = $this->groupViewedData($this->all_horizontals, 'hor');
    }

    /**
     * @param array $input
     * @param string $key
     * @return array
     */
    protected function addZeroValues(array $input, string $key): array
    {
        $key = $key === 'vert' ? 'vert' : 'hor';
        //APPLY OR NOT "Hide Col If Empty" - For showing combination of all L1/L2/L3/L4/L5 levels. (Add zero values).
        $sett = $key === 'vert' ? $this->pivot_vert : $this->pivot_hor;
        foreach ($this->sorted_values[$key . '_l1'] as $v1) {

            if (!$sett['l1_hide_empty'] || $this->subTotal(1, $key . '_l1', $v1)) {

                foreach ($this->sorted_values[$key . '_l2'] as $v2) {

                    if (!$sett['l2_hide_empty'] || $this->subTotal(1, $key . '_l1', $v1, $key . '_l2', $v2)) {
                        foreach ($this->sorted_values[$key . '_l3'] as $v3) {

                            if (!$sett['l3_hide_empty'] || $this->subTotal(1, $key . '_l1', $v1, $key . '_l2', $v2, $key . '_l3', $v3)) {
                                foreach ($this->sorted_values[$key . '_l4'] as $v4) {

                                    if (!$sett['l4_hide_empty'] || $this->subTotal(1, $key . '_l1', $v1, $key . '_l2', $v2, $key . '_l3', $v3, $key . '_l4', $v4)) {
                                        foreach ($this->sorted_values[$key . '_l5'] as $v5) {

                                            if (!$sett['l5_hide_empty'] || $this->subTotal(1, $key . '_l1', $v1, $key . '_l2', $v2, $key . '_l3', $v3, $key . '_l4', $v4, $key . '_l5', $v5)) {
                                                $empt = ['y' => 0];
                                                $empt[$key . '_l1'] = $v1;
                                                $empt[$key . '_l2'] = $v2;
                                                $empt[$key . '_l3'] = $v3;
                                                $empt[$key . '_l4'] = $v4;
                                                $empt[$key . '_l5'] = $v5;
                                                array_push($input, $empt);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $input;
    }

    /**
     * @param $about
     * @param $type1
     * @param $key1
     * @param null $type2
     * @param null $key2
     * @param null $type3
     * @param null $key3
     * @param null $type4
     * @param null $key4
     * @param null $type5
     * @param null $key5
     * @param null $type6
     * @param null $key6
     * @param null $type7
     * @param null $key7
     * @param null $type8
     * @param null $key8
     * @return float
     */
    protected function subTotal($about, $type1, $key1, $type2 = null, $key2 = null, $type3 = null, $key3 = null, $type4 = null, $key4 = null, $type5 = null, $key5 = null, $type6 = null, $key6 = null, $type7 = null, $key7 = null, $type8 = null, $key8 = null): float
    {
        $sum = 0.0;
        $key = 'about_' . $about;
        $data = $this->$key;
        foreach ($data as $el) {
            if (
                $this->cellIsActive($el)
                && (!$type1 || $key1 === null || $this->isSubTot($key1) || $el[$type1] == $key1)
                && (!$type2 || $key2 === null || $this->isSubTot($key2) || $el[$type2] == $key2)
                && (!$type3 || $key3 === null || $this->isSubTot($key3) || $el[$type3] == $key3)
                && (!$type4 || $key4 === null || $this->isSubTot($key4) || $el[$type4] == $key4)
                && (!$type5 || $key5 === null || $this->isSubTot($key5) || $el[$type5] == $key5)
                && (!$type6 || $key6 === null || $this->isSubTot($key6) || $el[$type6] == $key6)
                && (!$type7 || $key7 === null || $this->isSubTot($key7) || $el[$type7] == $key7)
                && (!$type8 || $key8 === null || $this->isSubTot($key8) || $el[$type8] == $key8)
            ) {
                $sum += floatval($el['y']);
            }
        }
        return $sum;
    }

    /**
     * @param $el
     * @return bool
     */
    protected function cellIsActive($el): bool
    {
        return true;//can be excluded functions from front-end
    }

    /**
     * @param $val
     * @return bool
     */
    protected function isSubTot($val): bool
    {
        return $val == '__sub_total';
    }

    /**
     * Subtotals
     */
    protected function subtotalToSorts(): void
    {
        //Add computed columns 'SubTotal'
        for ($i = 1; $i <= $this->maxLvl; $i++) {
            if (count($this->sorted_values['vert_l' . $i])) {
                ($this->pivot_vert['sub_tot_pos'] == 'top')
                    ? array_unshift($this->sorted_values['vert_l' . $i], '__sub_total')
                    : array_push($this->sorted_values['vert_l' . $i], '__sub_total');
            }
            if (count($this->sorted_values['hor_l' . $i])) {
                ($this->pivot_hor['sub_tot_pos'] == 'front')
                    ? array_unshift($this->sorted_values['hor_l' . $i], '__sub_total')
                    : array_push($this->sorted_values['hor_l' . $i], '__sub_total');
            }
        }
    }

    /**
     * @param array $input
     * @param string $key
     * @param int $lvl
     * @param bool $allshow
     * @return bool[]
     */
    protected function groupViewedData(array $input, string $key, int $lvl = 1, bool $allshow = false): array
    {
        $empobj = [
            '__show' => true,
            '__span_len' => 1,
        ];
        $key = $key === 'vert' ? 'vert' : 'hor';
        $sett = $key === 'vert' ? $this->pivot_vert : $this->pivot_hor;
        $grouped = collect($input)->groupBy($key . '_l' . $lvl)->toArray();

        $len = 0;
        $resu = ['__show' => true];
        //Group Data
        foreach ($grouped as $idx => $sub_group) {
            $resu[''.$idx] = ($lvl >= $this->maxLvl ? $empobj : $this->groupViewedData($sub_group, $key, $lvl + 1));
            $len += $resu[''.$idx]['__span_len'] || 1;
        }

        //Add computed: 'SubTotal'
        $tmp = [];
        $tmp[$key . '_l' . ($lvl + 1)] = '__sub_total';
        $resu['__sub_total'] = ($lvl >= $this->maxLvl ? $empobj : $this->groupViewedData([$tmp], $key, $lvl + 1, true));
        $resu['__sub_total']['__show'] = $allshow || !!$sett['l' . $lvl . '_sub_total'] || $lvl == 1;
        $len += $resu['__sub_total']['__show'] ? 1 : 0;

        //For computed 'SubTotal' Col/Row span=1
        if (count($this->dataKeys($resu)) == 0 && $resu['__sub_total']) {
            $len = 1;
        }

        $resu['__span_len'] = $len;
        return $resu;
    }

    /**
     * @param array $sorted_vals
     * @param array $level_object
     * @return array
     */
    protected function filterVals(array $sorted_vals, array $level_object): array
    {
        $keys = $this->dataKeys($level_object);
        if ($level_object['__sub_total'] && $level_object['__sub_total']['__show']) {
            $keys[] = '__sub_total';
        }
        return collect($sorted_vals)
            ->filter(function($k) use ($keys) {
                return in_array($k, $keys);
            })
            ->toArray();
    }

    /**
     * @param array $resu
     * @return array
     */
    protected function dataKeys(array $resu): array
    {
        return collect(array_keys($resu))
            ->filter(function ($key) {
                return !in_array($key, $this->syskeys);
            })
            ->toArray();
    }

    /**
     * @return array
     */
    public function getHeaderNames(): array
    {
        $headers = [];

        for ($i = 1; $i < $this->maxLvl; $i++) {
            if ($field = $this->corr_dbs['vert_l'.$i]) {
                $fld = $this->table->_fields->where('field', '=', $field)->first();
                if ($fld) {
                    $headers[] = 'Row Description,'.$fld->name;
                }
            } else {
                if ($i == 1) {
                    $headers[] = 'Row Description';
                }
            }
        }

        $this->headerLevel($headers, 1);

        return $headers;
    }

    /**
     * @param $headers
     * @param int $idx
     * @param array|null[] $hors
     * @param array $vals
     * @return bool
     */
    protected function headerLevel(&$headers, int $idx, array $hors = [], array $vals = []): bool
    {
        if ($idx > 3) {
            return false;
        }
        $hors = $hors ?: [1=>null, 2=>null, 3=>null];

        $added = false;
        foreach ($this->sorted_values['hor_l'.$idx] as $hor) {
            $hors[$idx] = $hor;
            if ($this->showVal('hor', $hors[1], $hors[2], $hors[3]) && $this->corr_dbs['hor_l'.$idx]) {
                $vals[$idx] = join(';', $this->showHead('hor', $hors[1], $hors[2], $hors[3]));
                $added = $this->headerLevel($headers, $idx+1, $hors, $vals);
                if (!$added) {
                    $value = [];
                    for ($i = 1; $i <= $idx; $i++) {
                        $value[] = $vals[$i];
                    }
                    $headers[] = join(',', $value);
                    $added = true;
                }
            }
        }
        return $added;
    }

    /**
     * @return array
     */
    public function getDataRows(): array
    {
        $dataRows = [];
        foreach ($this->filterVals($this->sorted_values['vert_l1'], $this->rows) as $i1 => $vl1) {
            foreach ($this->filterVals($this->sorted_values['vert_l2'], $this->rows[$vl1]) as $i2 => $vl2) {
                foreach ($this->filterVals($this->sorted_values['vert_l3'], $this->rows[$vl1][$vl2]) as $i3 => $vl3) {
                    foreach ($this->filterVals($this->sorted_values['vert_l4'], $this->rows[$vl1][$vl2][$vl3]) as $i4 => $vl4) {
                        foreach ($this->filterVals($this->sorted_values['vert_l5'], $this->rows[$vl1][$vl2][$vl3][$vl4]) as $i5 => $vl5) {
                            $row = [];
                            for ($i = 1; $i < $this->maxLvl; $i++) {
                                if ($this->corr_dbs['vert_l'.$i] || $i==1) {
                                    switch ($i) {
                                        case 1: $row[] = join(';', $this->showHead('vert', $vl1)); break;
                                        case 2: $row[] = join(';', $this->showHead('vert', $vl1, $vl2)); break;
                                        case 3: $row[] = join(';', $this->showHead('vert', $vl1, $vl2, $vl3)); break;
                                        case 4: $row[] = join(';', $this->showHead('vert', $vl1, $vl2, $vl3, $vl4)); break;
                                        case 5: $row[] = join(';', $this->showHead('vert', $vl1, $vl2, $vl3, $vl4, $vl5)); break;
                                    }
                                }
                            }
                            $this->dataCell($row, 1, [1=>$vl1, 2=>$vl2, 3=>$vl3, 4=>$vl4, 5=>$vl5]);
                            $dataRows[] = $row;
                        }
                    }
                }
            }
        }
        return $dataRows;
    }

    /**
     * @param $row
     * @param int $idx
     * @param array $verts
     * @param array $hors
     * @return bool
     */
    protected function dataCell(&$row, int $idx, array $verts, array $hors = []): bool
    {
        if ($idx > 3) {
            return false;
        }
        $hors = $hors ?: [1=>null, 2=>null, 3=>null];

        $added = false;
        foreach ($this->sorted_values['hor_l'.$idx] as $hor) {
            $hors[$idx] = $hor;
            if ($this->showVal('hor', $hors[1], $hors[2], $hors[3]) && $this->corr_dbs['hor_l'.$idx]) {
                $added = $this->dataCell($row, $idx+1, $verts, $hors);
                if (!$added) {
                    $row[] = $this->cellValue($verts[1], $verts[2], $verts[3], $verts[4], $verts[5], $hors[1], $hors[2], $hors[3]);
                    $added = true;
                }
            }
        }
        return $added;
    }

    /**
     * @param $vl1
     * @param $vl2
     * @param $vl3
     * @param $vl4
     * @param $vl5
     * @param $hor1
     * @param $hor2
     * @param $hor3
     * @return float
     */
    protected function cellValue($vl1, $vl2, $vl3, $vl4, $vl5, $hor1, $hor2, $hor3)
    {
        if ($this->isSubTot($hor1)) {
            return $this->subTotal(1, 'vert_l1', $vl1, 'vert_l2', $vl2, 'vert_l3', $vl3, 'vert_l4', $vl4, 'vert_l5', $vl5);
        } elseif ($this->isSubTot($hor2)) {
            return $this->subTotal(1, 'vert_l1', $vl1, 'vert_l2', $vl2, 'vert_l3', $vl3, 'vert_l4', $vl4, 'vert_l5', $vl5, 'hor_l1', $hor1);
        } elseif ($this->isSubTot($hor3)) {
            return $this->subTotal(1, 'vert_l1', $vl1, 'vert_l2', $vl2, 'vert_l3', $vl3, 'vert_l4', $vl4, 'vert_l5', $vl5, 'hor_l1', $hor1, 'hor_l2', $hor2);
        } else {
            return $this->subTotal(1, 'vert_l1', $vl1, 'vert_l2', $vl2, 'vert_l3', $vl3, 'vert_l4', $vl4, 'vert_l5', $vl5, 'hor_l1', $hor1, 'hor_l2', $hor2, 'hor_l3', $hor3);
        }
    }

    /**
     * @param $type
     * @param $l1
     * @param null $l2
     * @param null $l3
     * @param null $l4
     * @param null $l5
     * @return bool
     */
    protected function showVal($type, $l1, $l2 = null, $l3 = null, $l4 = null, $l5 = null): bool
    {
        $el = $this->getmatrix($type, $l1, $l2, $l3, $l4, $l5);
        return $el && $el['__show'];
    }

    /**
     * @param $type
     * @param null $l1
     * @param null $l2
     * @param null $l3
     * @param null $l4
     * @param null $l5
     * @return array|mixed
     */
    protected function getmatrix($type, $l1 = null, $l2 = null, $l3 = null, $l4 = null, $l5 = null)
    {
        $matrix = $type === 'hor' ? $this->columns : $this->rows;
        if ($l1 === null) return ($matrix ?? []);
        if ($l2 === null) return ($matrix[$l1] ?? []);
        if ($l3 === null) return ($matrix[$l1][$l2] ?? []);
        if ($l4 === null) return ($matrix[$l1][$l2][$l3] ?? []);
        if ($l5 === null) return ($matrix[$l1][$l2][$l3][$l4] ?? []);
        return ($matrix[$l1][$l2][$l3][$l4][$l5] ?? []);
    }

    /**
     * @param $type
     * @param $l1
     * @param null $l2
     * @param null $l3
     * @param null $l4
     * @param null $l5
     * @return array
     */
    protected function showHead($type, $l1 = null, $l2 = null, $l3 = null, $l4 = null, $l5 = null): array
    {
        $key = $type .
            ($l5 !== null ? '_l5'
                : ($l4 !== null ? '_l4'
                    : ($l3 !== null ? '_l3'
                        : ($l2 !== null ? '_l2'
                            : '_l1'))));
        $str = ($l5 !== null ? $l5
            : ($l4 !== null ? $l4
                : ($l3 !== null ? $l3
                    : ($l2 !== null ? $l2
                        : $l1))));
        $str = '' . $str . '';

        return collect(MselConvert::getArr($str))
            ->map(function ($el) use ($key) {
                return $this->headElConv($key, trim($el));
            })
            ->toArray();
    }

    /**
     * @param $key
     * @param $str_val
     * @return mixed
     */
    protected function headElConv($key, $str_val)
    {
        $usr = $this->all_variants['usrs'][$key][$str_val] ?? [];
        if ($usr) {
            return $usr['first_name'] . ($usr['last_name'] ? ' ' . $usr['last_name'] : '');
        }
        $rc = $this->all_variants['rcs'][$key][$str_val] ?? [];
        if ($rc) {
            return $rc['show_val'];
        }
        return $str_val == '__sub_total' ? 'Sub Total' : $str_val;
    }

    /**
     * @param array $input
     * @param bool $noempty
     * @return array
     */
    protected function filterData(array $input, bool $noempty = false): array
    {
        foreach ($input as &$el) { //All data should be 'string' for correct working of grouping
            for ($i = 1; $i <= $this->maxLvl; $i++) {
                if ($el['hor_l' . $i] ?? '') $el['hor_l' . $i] = '' . $el['hor_l' . $i] . '';
                if ($el['vert_l' . $i] ?? '') $el['vert_l' . $i] = '' . $el['vert_l' . $i] . '';

                if ($el['_rc_vert_l' . $i] ?? '') {
                    $tmp = [];
                    foreach ($el['_rc_vert_l' . $i] as $k => $item) {
                        $tmp[''.$k] = ($item instanceof Model ? $item->toArray() : (array)$item);
                    }
                    $el['_rc_vert_l' . $i] = $tmp;
                };
                if ($el['_u_vert_l' . $i] ?? '') {
                    $tmp = [];
                    foreach ($el['_u_vert_l' . $i] as $k => $item) {
                        $tmp[''.$k] = ($item instanceof Model ? $item->toArray() : (array)$item);
                    }
                    $el['_u_vert_l' . $i] = $tmp;
                };

                if ($el['_rc_hor_l' . $i] ?? '') {
                    $tmp = [];
                    foreach ($el['_rc_hor_l' . $i] as $k => $item) {
                        $tmp[''.$k] = ($item instanceof Model ? $item->toArray() : (array)$item);
                    }
                    $el['_rc_hor_l' . $i] = $tmp;
                };
                if ($el['_u_hor_l' . $i] ?? '') {
                    $tmp = [];
                    foreach ($el['_u_hor_l' . $i] as $k => $item) {
                        $tmp[''.$k] = ($item instanceof Model ? $item->toArray() : (array)$item);
                    }
                    $el['_u_hor_l' . $i] = $tmp;
                };
            }
        }
        return $noempty
            ? collect($input)
                ->filter(function ($el) {
                    return floatval($el['y']);
                })
                ->toArray()
            : $input;
    }
}