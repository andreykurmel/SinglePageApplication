<?php

namespace Vanguard\Repositories\Tablda;


use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Models\Table\Table;

class SrvRepository
{
    /**
     * TableDataRequestRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param Table $table
     * @param UploadedFile $upload_file
     * @return string|null
     * @throws Exception
     */
    public function insertBgiFile(Table $table, UploadedFile $upload_file)
    {
        if ($table->single_view_bg_img) {
            Storage::delete($table->single_view_bg_img);
        }

        $fileRepo = app()->make(FileRepository::class);
        $filePath = $fileRepo->getStorageTable($table) . '/';
        $fileName = 'srv_bgi_' . Uuid::uuid4();
        $upload_file->storeAs('public/' . $filePath, $fileName);

        $table->single_view_bg_img = $filePath . $fileName;
        $table->save();
        return $table->single_view_bg_img;
    }

    /**
     * @param Table $table
     * @return bool
     */
    public function deleteBgiFile(Table $table)
    {
        Storage::delete($table->single_view_bg_img);

        return $table->update([
            'single_view_bg_img' => null
        ]);
    }
}