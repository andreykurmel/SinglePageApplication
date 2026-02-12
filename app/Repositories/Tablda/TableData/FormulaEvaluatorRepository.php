<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Vanguard\Classes\DropdownHelper;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\FormulaFuncService;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;

class FormulaEvaluatorRepository
{
    protected $helper;
    protected $rowRepo;
    protected $evaluator;
    protected $table;
    protected $userId;
    protected $formula_fields = [];
    protected $field_name_links = [];
    protected $changed_fields = [];

    protected $avail_functions = [
        //#app_avail_formulas
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::sqlCount' => 'count',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::sqlCountUnique' => 'countunique',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::sqlSum' => 'sum',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::sqlMin' => 'min',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::sqlMax' => 'max',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::sqlMean' => 'mean',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::sqlAvg' => 'avg',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::sqlVariance' => 'var',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::sqlStd' => 'std',

        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayCount' => 'acount',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayCountUnique' => 'acountunique',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arraySum' => 'asum',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayMin' => 'amin',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayMax' => 'amax',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayMean' => 'amean',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayAvg' => 'aavg',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayVariance' => 'avar',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayStd' => 'astd',

        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::ddloption' => 'ddloption',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::ifAnd' => 'andx',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::ifOr' => 'orx',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::isEmpty' => 'isempty',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::ifCondition' => 'if',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::switchCondition' => 'switch',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::aiCreate' => 'ai_create',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::aiExtract' => 'ai_extract',

        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::timeChange' => 'timechange',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDuration' => 'duration',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getToday' => 'today',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getTomorrow' => 'tomorrow',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getYesterday' => 'yesterday',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDateDay' => 'yrday',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDayMonth' => 'moday',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getWeekDay' => 'wkday',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDateWeek' => 'week',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDateMonth' => 'month',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDateYear' => 'year',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDate' => 'date',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getTime' => 'time',

        'sqrt' => 'sqrt',
        'pow' => 'pow',
    ];
    protected $changed = false;

    /**
     * FormulaEvaluatorRepository constructor.
     * @param Table|null $table
     * @param int|null $user_id
     * @param bool $light
     * @param array $changed_fields
     */
    public function __construct(Table $table = null, int $user_id = null, bool $light = false, array $changed_fields = [])
    {
        $this->userId = $user_id;
        $this->changed_fields = $changed_fields;

        $this->helper = new HelperService();
        $this->rowRepo = new TableDataRowsRepository();
        if ($table) {
            if (!$light) {
                $table->loadMissing('_ref_conditions');
                $table->_ref_conditions->loadMissing([
                    '_items' => function ($q) {
                        $q->with('_compared_field', '_field');
                    },
                    '_ref_table' => function ($q) {
                        $q->with('_fields');
                    },
                ]);
            }

            //table
            $this->table = $table;

            //fields
            $table_fields = $table->_fields()
                ->joinHeader($user_id, $table)
                ->get();

            $this->formula_fields = $table_fields->where('input_type', '=', 'Formula');
            $this->field_name_links = [];
            foreach ($table_fields as $tf) {
                $this->field_name_links[$this->tf_name($tf->name)] = $tf;
                if ($tf->formula_symbol) {
                    $this->field_name_links[$this->tf_name($tf->formula_symbol)] = $tf;
                }
            }
            //virtual column 'static_hash' can be used in the formula
            $this->field_name_links[$this->tf_name('static_hash')] = new TableField(['field' => 'static_hash', 'name' => 'static_hash']);
            //---
        }

        //instantiate FormulaParser
        $this->evaluator = new ExpressionLanguage();
        foreach ($this->avail_functions as $phpName => $funcName) {
            $this->evaluator->addFunction($this->ExpressionFormula($phpName, $funcName));
        }
    }

    /**
     * @param Collection $rows
     * @return void
     */
    protected function needAttachRefs(Collection $rows)
    {
        $should = false;
        foreach ($this->formula_fields as $frm) {
            if ($should) {
                continue;
            }
            if ($frm->is_uniform_formula) {
                $should = !!$this->getActiveFields($frm->f_formula ?: '', true);
            } else {
                $should = !!$rows
                    ->filter(function ($row) use ($frm) {
                        $formula_str = $this->tf_name($row[$frm->field . '_formula'] ?? '', true);
                        return !!$this->getActiveFields($formula_str, true);
                    })
                    ->count();
            }
        }

        if ($should) {
            $this->rowRepo->attachSpecialFields($rows, $this->table, null, ['refs']);
        } else {
            $rows->each(function ($row) {
                $row['_rc_attached'] = true;
            });
        }
    }

    /**
     * @param array $row
     * @return array
     */
    protected function rowAsNeeded(array $row): array
    {
        if (empty($row['_rc_attached']) && !$this->table->is_system) {
            $col = collect([ (new TableData($row)) ]);
            $this->needAttachRefs($col);
            $row = $col->first()->toArray();
        }
        return $row;
    }

    /**
     * @param $name
     * @param bool $allstring
     * @return mixed|string
     */
    protected function tf_name($name, $allstring = false)
    {
        if ($allstring) {
            return preg_replace('/[\\\]/ui', '', $name);//sanitize
        } else {
            return strtolower(preg_replace('/[^\p{L}\d]/ui', '', $name));
        }
    }

    /**
     * @param $phpFunctionName
     * @param $name
     * @return ExpressionFunction
     */
    protected function ExpressionFormula($phpFunctionName, $name)
    {
        $compiler = function () use ($phpFunctionName) {
            return sprintf('\%s(%s)', $phpFunctionName, implode(', ', \func_get_args()));
        };

        $evaluator = function () use ($phpFunctionName) {
            return $phpFunctionName(...\array_slice(\func_get_args(), 1));
        };

        return new ExpressionFunction($name, $compiler, $evaluator);
    }

    /**
     * Recalculate formulas for One|All rows in TableData.
     *
     * @param array $row_ids
     * @param int $job_id
     */
    public function recalcTableFormulas(array $row_ids = [], int $job_id = 0)
    {
        if (!$this->formula_fields->count()) {
            $this->setJobId($job_id, ['status' => 'done']);
            return;
        }

        $rows = (new TableDataQuery($this->table))->getQuery();
        if ($row_ids) {
            $rows->whereIn('id', $row_ids);
        }
        $rows_count = $rows->count();

        $this->changed = false;
        for ($chunk = 0; ($chunk * 100) < $rows_count; $chunk++) {
            $rows_pack = (clone $rows)->offset($chunk * 100)->limit(100)->get();
            $this->needAttachRefs($rows_pack);

            $this->setJobId($job_id, ['complete' => (int)((($chunk * 100) / $rows_count) * 100)]);

            foreach ($rows_pack as $row) {
                $this->recalcRowFormulas($row->toArray());
            }
        }

        $this->setJobId($job_id, ['status' => 'done']);

        //update hashes
        if ($this->changed) {
            (new TableDataService())->newTableVersion($this->table);
        }
    }

    /**
     * @param int $job_id
     * @param array $arr
     * @return void
     */
    protected function setJobId(int $job_id = 0, array $arr = [])
    {
        if ($job_id) {
            DB::connection('mysql')->table('imports')->where('id', '=', $job_id)->update($arr);
        }
    }

    /**
     * Recalculate formulas for selected Row.
     *
     * @param array $row
     * @param bool $save_in_db
     * @param array $dcr_rows_linked
     * @return array
     */
    public function recalcRowFormulas(array $row, bool $save_in_db = true, array $dcr_rows_linked = [])
    {
        $row = $this->rowAsNeeded($row);

        FormulaFuncService::$dcr_rows_linked = $dcr_rows_linked;
        FormulaFuncService::$userId = $this->userId ?: auth()->id() ?: $this->table->user_id;
        FormulaFuncService::$currentRow = $row;
        FormulaFuncService::$metaTable = $this->table;

        $changed_row = false;
        foreach ($this->formula_fields as $field) {
            FormulaFuncService::$currentField = $field->toArray();
            $formula_str = $this->tf_name($row[$field->field . '_formula'] ?? '', true);
            if (!$formula_str && $row[$field->field] != '') {
                $row[$field->field] = '';
                $row['row_hash'] = Uuid::uuid4();
                $changed_row = true;
                continue;
            }

            $formula_str = $this->formulaReplaceVars($row, $formula_str, true);

            if ($row[$field->field] != $formula_str) {
                $row[$field->field] = substr($formula_str, 0, 255);
                $row['row_hash'] = Uuid::uuid4();
                $changed_row = true;
            }
        }

        if ($save_in_db && $changed_row) {
            try {
                foreach (FormulaFuncService::$afterUpdates as $fld => $val) {
                    $row[$fld] = $val;
                }
                TableDataQuery::getTableDataSql($this->table)
                    ->where('id', '=', $row['id'])
                    ->update($this->helper->delSystemFields($row, true));
                $this->changed = true;
            } catch (\Exception $e) {
            }
        }

        return $row;
    }

    /**
     * Prepare formulaString for calculation (replace {fields} to values and prepare functions calls)
     *
     * @param array $row
     * @param string $formula_str
     * @param bool $with_calc
     * @return string
     */
    public function formulaReplaceVars(array $row, string $formula_str, bool $with_calc = false)
    {
        $row = $this->rowAsNeeded($row);

        $formula_str = $this->removeNotPrintable($formula_str);
        $formula_str = $this->preparserFormula($formula_str, $with_calc);
        $active_fields = $this->getActiveFields($formula_str);
        $active_ddl_shows = $this->getActiveFields($formula_str, true);
        foreach ($active_fields as $act_field) { // $act_field = '{FIELD_NAME}'
            $tf_act_field = $this->tf_name($act_field);
            $fld = $this->field_name_links[$tf_act_field] ?? null;
            if ($fld) {
                $row_val = $row[$fld->field] ?? '';
                if (in_array($act_field, $active_ddl_shows)) {
                    $row_val = DropdownHelper::valOrShow($fld->field, $row);
                }
                //parser rules
                $data = preg_replace('/["]/i', '', $row_val);//sanitize
                $data = preg_replace('/(\d),(\d)/i', '$1$2', $data);//prepare for number calcs
                if ($with_calc) {
                    $data = preg_match('/[^\d\.]/i', $data)
                        ? ($data ?: '') //String if not only digits
                        : ($data ?: 0); //Number if only digits
                }
                //---

                //remove zero for concatenation
                if (preg_match('/~/', $formula_str) && !$data) {
                    $data = '"0"';
                }

                $formula_str = preg_replace($this->actToReplace($act_field), $data, $formula_str);
            } else {
                $formula_str = '"Field ' . $act_field . ' not found"';
            }
        }

        if ($with_calc) {
            //case insensetive functions
            $formula_str = preg_replace_callback('/([\p{L}]+\()/ui', function ($word) {
                return strtolower($word[1]);
            }, $formula_str);

            return $this->justCalc($formula_str);
        } else {
            return $formula_str;
        }
    }

    /**
     * @param string $formula_str
     * @return mixed|string
     */
    public function asSqlString(string $formula_str)
    {
        $formula_str = $this->preparserFormula($formula_str, true);
        $active_fields = $this->getActiveFields($formula_str);
        foreach ($active_fields as $act_field) { // $act_field = '{FIELD_NAME}'
            $act_field = $this->tf_name($act_field);
            $fld = $this->field_name_links[$act_field] ?? null;
            if ($fld) {
                $formula_str = preg_replace('/\{'.$act_field.'\}/i', '`'.$fld->field.'`', $formula_str);
            } else {
                $formula_str = '"Field ' . $act_field . ' not found"';
            }
        }
        return preg_replace('/[^\w_\/\*-+`,.]/i', '', $formula_str);
    }

    /**
     * @param string $formula_str
     * @return string
     */
    protected function preparserFormula(string $formula_str, bool $with_calc = false): string
    {
        $formula_str = str_replace('`', '\'', $formula_str);
        $formula_str = str_replace('&nbsp;', ' ', $formula_str);
        $formula_str = str_replace('&', '~', $formula_str);
        //wrap {fields} in Reference functions by `` (to prevent field replacing with value)
        $references = ['count','countunique','sum','min','max','mean','avg','var','std'];
        foreach ($references as $ref) {
            $findings = ['^'.$ref.'\([^\)]+\)', '[^a]'.$ref.'\([^\)]+\)'];
            $formula_str = preg_replace_callback('/'.join('|', $findings).'/i', function ($subformula) use ($ref) {
                $frm_string = preg_replace('/\s/i', '', $subformula[0]);//remove spaces
                $frm_string = preg_replace('/,(\{[^\}]*\})/i', ',`$1`', $frm_string); //wrap {fields}
                return preg_replace('/\(([^"\'`]+),/i', '(`$1`,', $frm_string); // wrap RefCond name in `` if needed
            }, $formula_str);
        }
        //wrap {fields} into "{fields}" if they are not in strings with quotes
        if ($with_calc) {
            $formula_str = preg_replace('/(\{[^\}]+\})(?=(?:[^\'"`]*[\'"`][^\'"`]*[\'"`])*[^\'"`]*$)/i', '"$1"', $formula_str);
        }
        return $formula_str;
    }

    /**
     * @param string $formula_str
     * @param bool $ddlid
     * @return array
     */
    protected function getActiveFields(string $formula_str, bool $ddlid = false)
    {
        $active_fields = [];
        $formula_str = preg_replace('/(\{)/i', ' $1', $formula_str);//add spaces for correct $active_fields parsing
        $parser = $ddlid
            ? 'ddloption\(\s?(\{[^\}]*\})\s?,\s?"show"\s?\)|ddloption\(\s?(\{[^\}]*\})\s?,\s?"show"\s?,\s?"\d"\s?\)'
            : '[^`](\{[^\}]*\})[^`]|^(\{[^\}]*\})|(\{[^\}]*\})$';
        preg_match_all('/'.$parser.'/i', $formula_str, $active_fields);
        $active_fields[0] = [];
        return array_filter(array_unique(Arr::flatten($active_fields)));
    }

    /**
     * @param string $formula_str
     * @return string
     */
    public function justCalc(string $formula_str)
    {
        try {
            $formula_str = preg_replace('/`([^`]*)`/i', '\'$1\'', $formula_str);//`{variable}` => '{variable}' (remove system replace restrictors)
            $formula_str = preg_replace('/[\'"]([\d.]+)[\'"]/i', '$1', $formula_str);//"5" => 5 (remove number quotes)
            $formula_str = $this->removeNotPrintable($formula_str);
            $result = $formula_str ? (string)$this->evaluator->evaluate($formula_str) : '';
        } catch (\Throwable $e) {
            $result = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param string $formula_str
     * @return string
     */
    protected function removeNotPrintable(string $formula_str): string
    {
        //Remove {non-printable} and {spaces} from the string (ignore strings in quotes: "some data {non-latin1}" + {non-latin2} ===>>> "some data {non-latin1}")
        $formula_str = preg_replace('/[^[:print:]](?=(?:[^\'\"]*(?:\'|\")[^\'\"]*(?:\'|\"))*[^\'\"]*$)/ui', '', $formula_str);
        $formula_str = preg_replace('/\s(?=(?:[^\'\"]*(?:\'|\")[^\'\"]*(?:\'|\"))*[^\'\"]*$)/i', ' ', $formula_str);
        $formula_str = preg_replace('/\s/u', ' ', $formula_str);
        return trim($formula_str);
    }

    /**
     * @param string $act_field
     * @return string
     */
    protected function actToReplace(string $act_field): string
    {
        return '/\{' . preg_replace('/[\{\}\/]/i', '', $act_field) . '\}/i';
    }
}