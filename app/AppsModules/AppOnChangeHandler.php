<?php

namespace Vanguard\AppsModules;


use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Correspondences\CorrespTable;
use Vanguard\Models\Table\Table;

class AppOnChangeHandler
{
    /**
     * @var Table
     */
    protected $table;
    /**
     * @var DirectCallOut
     */
    protected $output;
    /**
     * @var string
     */
    protected $namespace = 'Vanguard\Http\Controllers\Web\Tablda\Applications\\';

    /**
     * AppOnChangeHandler constructor.
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @param array $row
     * @param array $old_row
     * @return array
     */
    public function testRow(array $row, array $old_row = [])
    {
        try {
            $app_tb = CorrespTable::where('data_table', $this->table->db_name)
                ->isActive()
                ->where('on_change_app_id', '>', 0)
                ->first();
            if ($app_tb) {
                $corr_app = CorrespApp::onlyActive()->where('id', $app_tb->on_change_app_id)->first();
                if ($corr_app && $corr_app->controller) {
                    $controller = app($this->namespace . $corr_app->controller);

                    $input = new DirectCallInput();
                    $input->setTable($this->table);
                    $input->setOldRow($old_row);
                    $input->setRow($row);

                    $this->output = $controller->direct_call($input);
                    if ($this->output && $this->output->getRow()) {
                        $row = array_merge($row, $this->output->getRow());
                    }
                }
            }
        } catch (\Exception $e) {
            $row['__error_exception'] = $e->getMessage();
        }
        return $row;
    }
}