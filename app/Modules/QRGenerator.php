<?php

namespace Vanguard\Modules;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QRGenerator
{
    /**
     * @var QrCode
     */
    protected $qrcode;
    /**
     * @var string
     */
    protected $hash_dcr;

    /**
     * @param string $data
     */
    public function __construct(string $data = '')
    {
        $this->qrcode = new QrCode($data);
        $this->qrcode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
    }

    /**
     * @param string $hash_dcr
     * @return $this
     */
    public function forDCR(string $hash_dcr): self
    {
        $this->hash_dcr = $hash_dcr;
        $this->qrcode->setText(config('app.url').'/dcr/'.$hash_dcr);

        $this->qrcode->setLogoPath(public_path('assets/img/vanguard-logo-no-text.png'));
        $this->qrcode->setLogoSize(75, 75);

        return $this;
    }

    /**
     * @param string $file_path
     * @return string
     */
    public function asPNG(string $file_path = ''): string
    {
        $png_link = '';
        $writer = new PngWriter();

        if (!Storage::exists('public/dcr_qrs')) {
            Storage::put('public/dcr_qrs/init.txt', 'initialized');
        }

        if ($this->hash_dcr) {
            $png_link = config('app.url') . '/storage/dcr_qrs/' . $this->hash_dcr . '.png';
            $writer->writeFile($this->qrcode, storage_path('app/public/dcr_qrs/'.$this->hash_dcr.'.png'));
        } else {
            $png_link = $file_path;
            $writer->writeFile($this->qrcode, $file_path);
        }

        return $png_link;
    }
}