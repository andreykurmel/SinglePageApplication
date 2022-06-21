<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Support\Facades\Storage;
use Vanguard\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CkeditorController extends Controller
{

    /**
     * CkeditorController constructor.
     */
    public function __construct()
    {}

    /**
     * @param Request $request
     * @return array
     */
    public function uploadFile(Request $request) {
        $filename = $request->upload->store('public/ckeditors');
        $file = new \SplFileInfo($filename);
        return [
            "uploaded" => 1,
            "fileName" => $file->getBasename(),
            "url" => '/storage/ckeditors/'.$file->getBasename()
        ];
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function browseFile(Request $request) {
        $files = scandir( storage_path('app/public/ckeditors') );
        $files = array_diff($files, ['.','..']);

        foreach ($files as &$file) {
            $file = [
                'path' => '/storage/ckeditors/' . $file,
                'name' => $file,
            ];
        }

        return view('tablda.statics.ckeditor_browse', ['files' => $files]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function deleteFile(Request $request) {
        Storage::delete(storage_path('app/public/ckeditors/') . $request->fname);
        return 'deleted';
    }
}
