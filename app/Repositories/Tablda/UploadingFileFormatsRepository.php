<?php

namespace Vanguard\Repositories\Tablda;


use Vanguard\Models\UploadingFileFormats;

class UploadingFileFormatsRepository
{
    /**
     * @return array
     */
    public function loadAttachmentsFormats(): array
    {
        $all = UploadingFileFormats::all()->groupBy('category');
        $frontend = [];
        foreach ($all as $category => $group) {
            $frontend[] = (object)[
                'val' => null,
                'html' => $category,
                'hasGroup' => $group->pluck('format'),
            ];
            foreach ($group as $item) {
                $frontend[] = (object)[
                    'val' => $item->format,
                    'html' => '&nbsp;&nbsp;&nbsp;'.ucfirst($item->format),
                ];
            }
        }
        return $frontend;
    }
}