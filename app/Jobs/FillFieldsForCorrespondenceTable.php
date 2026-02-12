<?php

namespace Vanguard\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\Correspondences\CorrespTable;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;

class FillFieldsForCorrespondenceTable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $corr_table_id;

    /**
     * @param int $corr_table_id
     */
    public function __construct(int $corr_table_id)
    {
        $this->corr_table_id = $corr_table_id;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        if (!$this->corr_table_id) {
            return '';
        }

        $corrTable = CorrespTable::find($this->corr_table_id);
        if ($corrTable && ! $corrTable->_fields()->count()) {

            $service = new HelperService();
            $tble = (new TableRepository())->findByDbOrId($corrTable->data_table);
            foreach ($tble->_fields as $field) {
                if (! in_array($field->field, $service->system_fields)) {

                    $dataFld = preg_replace('/[\s]+/i', '_', $field->name);
                    $dataFld = preg_replace('/[^\w\d_]/i', '', $dataFld);

                    CorrespField::create([
                        'user_id' => $corrTable->user_id,
                        'correspondence_app_id' => $corrTable->correspondence_app_id,
                        'correspondence_table_id' => $corrTable->id,
                        'app_field' => strtolower($dataFld),
                        'data_field' => $field->field,
                        'options' => '["show:true"]',
                    ]);
                }
            }

        }

        return 'done';
    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}
