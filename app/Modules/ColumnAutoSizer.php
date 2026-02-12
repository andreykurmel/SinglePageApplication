<?php

namespace Vanguard\Modules;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\ImportRepository;

class ColumnAutoSizer
{
    /**
     * @var ImportRepository
     */
    protected $impRepo;

    /**
     *
     */
    public function __construct()
    {
        $this->impRepo = new ImportRepository();
    }

    /**
     * @param Table $table
     * @param TableField $hdr
     * @param $val
     * @param string $frmla
     * @return int
     */
    public function increaseIfNeeded(Table $table, TableField $hdr, $val, string $frmla = ''): int
    {
        //SmartSize functionality
        if ($this->notEnoughSize($hdr->f_type, $hdr->f_size, $val)) {
            $this->calc_fsize($hdr, $val);
            $hdr->save();
            $this->increaseColSize($table, $hdr);
        }

        //Formula col functionality
        if ($hdr->input_type === 'Formula') {
            if (strlen($frmla) > 255) {
                $this->IncFormulaSize($table, $hdr->field . '_formula', strlen($frmla));
            }
        }
        return in_array($hdr->f_type, ['Text', 'Long Text', 'Vote', 'HTML'])
            ? 16
            : intval($hdr->f_size) + ($hdr->input_type === 'Formula' ? 255 : 0);
    }

    /**
     * @param string $f_type
     * @param string $f_size
     * @param $val
     * @return bool
     */
    protected function notEnoughSize(string $f_type, string $f_size, $val)
    {
        if (in_array($f_type, ['Decimal', 'Currency', 'Percentage', 'Progress Bar'])) {
            $sizes = self::get_col_size($f_size, true);
            $arrs = self::get_col_size($val, true, true);
            return $sizes[0] < $arrs[0] || $sizes[1] < $arrs[1];
        } else {
            return floatval($f_size) <= strlen($val);
        }
    }

    /**
     * @param TableField $hdr
     * @param $val
     * @param int $depth
     */
    protected function calc_fsize(TableField $hdr, $val, $depth = 1)
    {
        $hdr->f_size = $this->increaseSize($hdr->f_type, $hdr->f_size, $val);
        if ($hdr->f_size > 2048) {
            $hdr->f_type = 'Text';
        }
        if ($hdr->f_size > 32768) {
            $hdr->f_type = 'Long Text';
        }

        if ($depth <= 3 && $this->notEnoughSize($hdr->f_type, $hdr->f_size, $val)) {
            $this->calc_fsize($hdr, $val, $depth + 1);
        }
    }

    /**
     * @param string $f_type
     * @param string $f_size
     * @param $val
     * @return float
     */
    protected function increaseSize(string $f_type, string $f_size, $val)
    {
        if (in_array($f_type, ['Decimal', 'Currency', 'Percentage', 'Progress Bar'])) {
            $sizes = self::get_col_size($f_size, true);
            $arrs = self::get_col_size($val, true, true);
            $sizes[0] = $sizes[0] < $arrs[0] ? $arrs[0] : $sizes[0];
            $sizes[1] = $sizes[1] < $arrs[1] ? $arrs[1] : $sizes[1];
            return floatval(implode('.', $sizes));
        } else {
            return floatval($f_size) * 2;
        }
    }

    /**
     * @param Table $table
     * @param TableField $hdr
     * @return void
     */
    protected function increaseColSize(Table $table, TableField $hdr)
    {
        Schema::connection('mysql_data')->table($table->db_name, function (Blueprint $bp_table) use ($hdr) {
            $col_size = self::get_col_size($hdr['f_size']);
            $t = $this->impRepo->defineColumnType($hdr->toArray(), $bp_table, $col_size);
            $t->change();
        });
    }

    /**
     * @param Table $table
     * @param string $formula_fld
     * @param int $size
     */
    protected function IncFormulaSize(Table $table, string $formula_fld, int $size)
    {
        if (!$formula_fld || !$size) {
            return;
        }
        Schema::connection('mysql_data')->table($table->db_name, function (Blueprint $bp_table) use ($table, $formula_fld, $size) {
            $cursize = DB::connection('mysql_data')->getDoctrineColumn($table->db_name, $formula_fld)->getLength();
            if ($cursize < $size) {
                $bp_table->string($formula_fld, $size)->change();
            }
        });
    }

    /**
     * @param $f_size
     * @param bool $nosum
     * @param bool $len
     * @return array
     */
    public static function get_col_size($f_size, bool $nosum = false, bool $len = false)
    {
        $col_size = $f_size > 0 ? explode('.', $f_size) : [];
        if ($len) {
            $col_size[0] = strlen($col_size[0] ?? '');
            $col_size[1] = strlen($col_size[1] ?? '');
        }

        if (!isset($col_size[0])) $col_size[0] = 8;
        if (!isset($col_size[1])) $col_size[1] = 2;

        if (!$nosum) {
            $col_size[0] += $col_size[1];
        }
        return $col_size;
    }
}