<?php

namespace Vanguard\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Helpers\ColorHelper;
use Vanguard\Models\DDLReferenceColor;
use Vanguard\Modules\RemoteFilesModule;
use Vanguard\Repositories\Tablda\DDLRepository;

class FillMissedDdlRefColors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $ddl_reference_id;
    protected bool $remove;

    /**
     * @param int $ddl_reference_id
     * @param bool $remove
     */
    public function __construct(int $ddl_reference_id, bool $remove)
    {
        $this->ddl_reference_id = $ddl_reference_id;
        $this->remove = $remove;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $repo = new DDLRepository();
        $reference = $repo->getDDLReference($this->ddl_reference_id);
        $count = $reference->get_ref_clr_img_count();
        for ($i = 0; $i*100 < $count; $i++) {
            $rand = rand(0, 0x101010);
            foreach ($repo->allRefColors($reference->id, $i+1) as $idx => $refColor) {
                $brightness = floor($idx / 20) * $rand;
                DDLReferenceColor::where('id', '=', $refColor->id)->update([
                    'color' => !$this->remove ? '#' . ColorHelper::autoHex($brightness) : null,
                ]);
            }
        }
    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}
