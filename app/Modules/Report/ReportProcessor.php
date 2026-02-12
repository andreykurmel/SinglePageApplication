<?php

namespace Vanguard\Modules\Report;

use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\TemplateProcessor;
use Vanguard\Modules\CloudBackup\GoogleApiModule;
use Vanguard\Support\FileHelper;

class ReportProcessor
{
    /**
     * @var TemplateProcessor|null
     */
    protected $docxProcessor = null;
    /**
     * @var string
     */
    protected $fileContent = '';

    /**
     * @param string $filePath
     * @throws \Exception
     */
    public function __construct(string $filePath, string $docType)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('Report template file not found!', 1);
        }

        try {
            if ($docType == 'gdoc' || $docType == 'ms_word') {
                $this->docxProcessor = new TemplateProcessor($filePath);
            } else {
                $this->fileContent = file_get_contents($filePath);
            }
        } catch (\Exception $exception) {
            \Log::error("Report Processor: " . $exception->getMessage());
            throw new \Exception('Report template processor could not be created. Check your report file!', 1);
        }
    }

    /**
     * @param string $variable
     * @param $replaced
     * @return void
     */
    public function replaceVariable(string $variable, $replaced): void
    {
        if ($this->docxProcessor) {
            $this->docxProcessor->setValue($variable, $replaced ?? '');
        } else {
            $this->fileContent = str_replace($variable, $replaced, $this->fileContent);
        }
    }

    /**
     * @param string $variable
     * @param string $imgPath
     * @return void
     */
    public function replaceVarImage(string $variable, string $imgPath): void
    {
        if ($this->docxProcessor) {
            $this->docxProcessor->setImageValue($variable, [
                'path' => $imgPath,
                'width' => 500,
                'height' => 500,
                'ratio' => true
            ]);
        }
        //FileContent doesn't support images. Skip.
    }

    /**
     * @param string $variable
     * @param $block
     * @return void
     */
    public function replaceVarTable(string $variable, $block): void
    {
        if ($this->docxProcessor) {
            $this->docxProcessor->setComplexBlock($variable, $block);
        }
        //FileContent doesn't support images. Skip.
    }

    /**
     * @return string
     * @throws Exception
     */
    public function savedContent(): string
    {
        if ($this->docxProcessor) {
            return file_get_contents($this->docxProcessor->save());
        } else {
            return $this->fileContent;
        }
    }

    /**
     * @return array
     */
    public function allVariables(): array
    {
        if ($this->docxProcessor) {
            $variables = $this->docxProcessor->getVariables();
        } else {
            $matches = [];
            preg_match_all('/\$\{[^\}]+\}/i', $this->fileContent, $matches);
            $variables = $matches[0] ?? [];
        }

        foreach ($variables as &$var) {
            $var = preg_replace('/[^\w\d\s_]/i','', $var);
            $var = str_replace('DOCPROPERTY','', $var);
            $var = str_replace('MERGEFORMAT','', $var);
            $var = trim($var);
        }

        return $variables;
    }
}