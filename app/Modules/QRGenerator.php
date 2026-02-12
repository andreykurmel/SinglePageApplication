<?php

namespace Vanguard\Modules;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class QRGenerator
{
    /**
     * @var QrCode
     */
    protected $qrcode;
    protected $qrlogo = null;
    protected $qrlabel = null;
    /**
     * @var string
     */
    protected $hash_dcr;
    /**
     * @var string
     */
    protected $hash_mrv;

    /**
     * @param string $data
     */
    public function __construct(string $data = '')
    {
        $this->qrcode = new QrCode($data, new Encoding('UTF-8'), ErrorCorrectionLevel::High);
    }

    /**
     * @param string $hash_dcr
     * @param string $label
     * @return $this
     */
    public function forDCR(string $hash_dcr, string $label = ''): self
    {
        $this->hash_dcr = $hash_dcr;

        $data = config('app.url').'/dcr/'.$hash_dcr.'?v='.Uuid::uuid4();
        $this->qrcode = new QrCode($data, new Encoding('UTF-8'), ErrorCorrectionLevel::High);

        $this->setLogo();
        $this->setLabel($label);
        return $this;
    }

    /**
     * @param string $hash_mrv
     * @param string $label
     * @return $this
     */
    public function forMRV(string $hash_mrv, string $label = ''): self
    {
        $this->hash_mrv = $hash_mrv;

        $data = config('app.url').'/mrv/'.$hash_mrv.'?v='.Uuid::uuid4();
        $this->qrcode = new QrCode($data, new Encoding('UTF-8'), ErrorCorrectionLevel::High);

        $this->setLogo();
        $this->setLabel($label);
        return $this;
    }

    /**
     * @return void
     */
    protected function setLogo(): void
    {
        $this->qrlogo = new Logo(public_path('assets/img/DCR_QR1.png'), 100, 100);
    }

    /**
     * @param string $label
     * @return void
     */
    protected function setLabel(string $label): void
    {
        if ($label) {
            $this->qrlabel = new Label(
                text: $label,
                font: new Font(__DIR__ . '/NotoSansSC-Regular.ttf', 16),
                margin: new Margin(0, 0, 15, 0)
            );
        }
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
        if (!Storage::exists('public/mrv_qrs')) {
            Storage::put('public/mrv_qrs/init.txt', 'initialized');
        }

        if ($this->hash_dcr) {
            Storage::delete('public/dcr_qrs/'.$this->hash_dcr.'.png');
            $png_link = config('app.url') . '/storage/dcr_qrs/' . $this->hash_dcr . '.png';
            $writer->write($this->qrcode, $this->qrlogo, $this->qrlabel)
                ->saveToFile(storage_path('app/public/dcr_qrs/'.$this->hash_dcr.'.png'));
        } elseif ($this->hash_mrv) {
            Storage::delete('public/mrv_qrs/'.$this->hash_mrv.'.png');
            $png_link = config('app.url') . '/storage/mrv_qrs/' . $this->hash_mrv . '.png';
            $writer->write($this->qrcode, $this->qrlogo, $this->qrlabel)
                ->saveToFile(storage_path('app/public/mrv_qrs/'.$this->hash_mrv.'.png'));
        } else {
            $png_link = $file_path;
            $writer->write($this->qrcode, $this->qrlogo, $this->qrlabel)
                ->saveToFile($file_path);
        }

        return $png_link;
    }
}