<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\UploadingFileFormats;

class UploadingFileFormatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Image formats
        if (!UploadingFileFormats::where('format', '=', 'jpg')->count()) {
            UploadingFileFormats::create([
                'category' => 'Image/Picture',
                'format' => 'jpg',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'jpeg')->count()) {
            UploadingFileFormats::create([
                'category' => 'Image/Picture',
                'format' => 'jpeg',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'png')->count()) {
            UploadingFileFormats::create([
                'category' => 'Image/Picture',
                'format' => 'png',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'tiff')->count()) {
            UploadingFileFormats::create([
                'category' => 'Image/Picture',
                'format' => 'tiff',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'gif')->count()) {
            UploadingFileFormats::create([
                'category' => 'Image/Picture',
                'format' => 'gif',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'eps')->count()) {
            UploadingFileFormats::create([
                'category' => 'Image/Picture',
                'format' => 'eps',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'webp')->count()) {
            UploadingFileFormats::create([
                'category' => 'Image/Picture',
                'format' => 'webp',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'svg')->count()) {
            UploadingFileFormats::create([
                'category' => 'Image/Picture',
                'format' => 'svg',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'heif')->count()) {
            UploadingFileFormats::create([
                'category' => 'Image/Picture',
                'format' => 'heif',
            ]);
        }

        //Document formats
        if (!UploadingFileFormats::where('format', '=', 'pdf')->count()) {
            UploadingFileFormats::create([
                'category' => 'Document',
                'format' => 'pdf',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'doc')->count()) {
            UploadingFileFormats::create([
                'category' => 'Document',
                'format' => 'doc',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'docx')->count()) {
            UploadingFileFormats::create([
                'category' => 'Document',
                'format' => 'docx',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'tiff')->count()) {
            UploadingFileFormats::create([
                'category' => 'Document',
                'format' => 'tiff',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'xls')->count()) {
            UploadingFileFormats::create([
                'category' => 'Document',
                'format' => 'xls',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'xlsx')->count()) {
            UploadingFileFormats::create([
                'category' => 'Document',
                'format' => 'xlsx',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'xlsm')->count()) {
            UploadingFileFormats::create([
                'category' => 'Document',
                'format' => 'xlsm',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'txt')->count()) {
            UploadingFileFormats::create([
                'category' => 'Document',
                'format' => 'txt',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'dat')->count()) {
            UploadingFileFormats::create([
                'category' => 'Document',
                'format' => 'dat',
            ]);
        }

        //Audio/video formats
        if (!UploadingFileFormats::where('format', '=', 'mp3')->count()) {
            UploadingFileFormats::create([
                'category' => 'Audio/Video',
                'format' => 'mp3',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'mp4')->count()) {
            UploadingFileFormats::create([
                'category' => 'Audio/Video',
                'format' => 'mp4',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'mpeg')->count()) {
            UploadingFileFormats::create([
                'category' => 'Audio/Video',
                'format' => 'mpeg',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'wmv')->count()) {
            UploadingFileFormats::create([
                'category' => 'Audio/Video',
                'format' => 'wmv',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'mov')->count()) {
            UploadingFileFormats::create([
                'category' => 'Audio/Video',
                'format' => 'mov',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'avi')->count()) {
            UploadingFileFormats::create([
                'category' => 'Audio/Video',
                'format' => 'avi',
            ]);
        }

        //Compressed formats
        if (!UploadingFileFormats::where('format', '=', 'zip')->count()) {
            UploadingFileFormats::create([
                'category' => 'Compressed',
                'format' => 'zip',
            ]);
        }
        if (!UploadingFileFormats::where('format', '=', 'tar')->count()) {
            UploadingFileFormats::create([
                'category' => 'Compressed',
                'format' => 'tar',
            ]);
        }

    }
}
