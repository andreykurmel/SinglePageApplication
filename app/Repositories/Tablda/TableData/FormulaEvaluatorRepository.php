<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\FormulaFuncService;

class FormulaEvaluatorRepository
{
    protected $evaluator;
    protected $table;
    protected $formula_fields = [];
    protected $field_name_links = [];

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

        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arraySum' => 'asum',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayMin' => 'amin',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayMax' => 'amax',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayMean' => 'amean',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayAvg' => 'aavg',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayVariance' => 'avar',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::arrayStd' => 'astd',

        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::ifCondition' => 'if',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::switchCondition' => 'switch',

        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::timeChange' => 'timechange',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDuration' => 'duration',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getToday' => 'today',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDateDay' => 'yrday',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDayMonth' => 'moday',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getWeekDay' => 'wkday',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDateWeek' => 'week',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDateMonth' => 'month',
        '\\Vanguard\\Services\\Tablda\\FormulaFuncService::getDateYear' => 'year',

        'sqrt' => 'sqrt',
        'pow' => 'pow',
    ];

    /**
     * FormulaEvaluatorRepository constructor.
     * @param Table $table
     * @param int|null $user_id
     * @param bool $light
     */
    public function __construct(Table $table, int $user_id = null, bool $light = false)
    {
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
        //---

        //instantiate FormulaParser
        $this->evaluator = new ExpressionLanguage();
        foreach ($this->avail_functions as $phpName => $funcName) {
            $this->evaluator->addFunction($this->ExpressionFormula($phpName, $funcName));
        }
    }

    /**
     * @param $name
     * @param bool $allstring
     * @return mixed|string
     */
    private function tf_name($name, $allstring = false)
    {
        if ($allstring) {
            return preg_replace('/[\\\]/i', '', $name);//sanitize
        } else {
            return strtolower(preg_replace('/[^\w\d]/i', '', $name));
        }
    }

    /**
     * @param $phpFunctionName
     * @param $name
     * @return ExpressionFunction
     */
    private function ExpressionFormula($phpFunctionName, $name)
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
            $this->setJobId($job_id, 'done');
            return;
        }

        $rows = (new TableDataQuery($this->table))->getQuery();
        if ($row_ids) {
            $rows->whereIn('id', $row_ids);
        }
        $rows_count = $rows->count();

        for ($chunk = 0; ($chunk * 100) < $rows_count; $chunk++) {
            $rows_pack = (clone $rows)->offset($chunk * 100)->limit(100)->get()->toArray();

            $this->setJobId($job_id, (int)((($chunk * 100) / $rows_count) * 100));

            foreach ($rows_pack as $row) {
                $this->recalcRowFormulas($row);
            }
        }

        $this->setJobId($job_id, 'done');

        //update hashes
        (new TableRepository())->onlyUpdateTable($this->table->id, [
            'version_hash' => Uuid::uuid4()
        ]);
    }

    /**
     * @param int $job_id
     * @param $val
     */
    private function setJobId(int $job_id = 0, $val)
    {
        if ($job_id) {
            $arr = $val == 'done' ? ['status' => 'done'] : ['complete' => $val];
            DB::connection('mysql')->table('imports')->where('id', '=', $job_id)->update($arr);
        }
    }

    /**
     * Recalculate formulas for selected Row.
     *
     * @param array $row
     * @param bool $save_in_db
     * @return array
     */
    public function recalcRowFormulas(array $row, $save_in_db = true)
    {
        FormulaFuncService::$currentRow = $row;
        FormulaFuncService::$metaTable = $this->table;

        $changed_row = false;
        foreach ($this->formula_fields as $field) {
            FormulaFuncService::$currentField = $field->toArray();
            $formula_str = $this->tf_name($row[$field->field . '_formula'] ?? '', true);
            if (!$formula_str) {
                $row[$field->field] = '';
                $row['row_hash'] = Uuid::uuid4();
                $changed_row = true;
                continue;
            }

            $formula_str = $this->formulaReplaceVars($row, $formula_str);

            try {
                $formula_str = (string)$this->evaluator->evaluate($formula_str);
            } catch (\Exception $e) {
                $formula_str = $e->getMessage();
            }

            if ($row[$field->field] != $formula_str) {
                $row[$field->field] = substr($formula_str, 0, 255);
                $row['row_hash'] = Uuid::uuid4();
                $changed_row = true;
            }
        }

        if ($save_in_db && $changed_row) {
            try {
                (new TableDataQuery($this->table))->getQuery()
                    ->where('id', '=', $row['id'])
                    ->update($row);
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
        $active_fields = [];

        $formula_str = preg_replace('/(\{)/i', ' $1', $formula_str);//add spaces for correct $active_fields parsing
        preg_match_all('/[^"](\{[^\}]*\})[^"]|^(\{[^\}]*\})|(\{[^\}]*\})$/i', $formula_str, $active_fields);
        $active_fields[0] = [];
        $active_fields = array_filter(array_unique(array_flatten($active_fields)));

        foreach ($active_fields as $idx => $act_field) { // $act_field = '{FIELD_NAME}'
            $act_field = $this->tf_name($act_field);
            $fld = $this->field_name_links[$act_field] ?? null;
            if ($fld) {
                //parser rules
                $data = preg_replace('/["]/i', '', $row[$fld->field] ?? '');//sanitize
                $data = preg_replace('/(\d),(\d)/i', '$1$2', $data);//prepare for number calcs
                $data = preg_match('/[^\d\.]/i', $data)
                    ? '"' . ($data ?: '') . '"' //String if not only digits
                    : ($data ?: 0); //Number if only digits
                //---

                //remove zero for concatenation
                if (preg_match('/~/', $formula_str) && !$data) {
                    $data = '""';
                }

                $formula_str = preg_replace('/\{' . $act_field . '\}/i', $data, $formula_str);
            } else {
                $formula_str = '"Field ' . $act_field . ' not found"';
            }
        }

        //case insensetive functions
        $formula_str = preg_replace_callback('/([\w]+\()/i', function ($word) {
            return strtolower($word[1]);
        }, $formula_str);

        return $with_calc ? $this->justCalc($formula_str) : $formula_str;
    }

    /**
     * @param string $formula_str
     * @return string
     */
    protected function justCalc(string $formula_str)
    {
        try {
            $result = (string)$this->evaluator->evaluate($formula_str);
        } catch (\Exception $e) {
            $result = $formula_str;
        }
        return $result;
    }
}