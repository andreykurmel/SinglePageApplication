<?php

namespace Vanguard\Modules\Ocr;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Log;
use Ramsey\Uuid\Uuid;
use SplFileObject;
use Vanguard\Support\FileHelper;

class ExtracttableOcr
{
    /**
     * @var string
     */
    protected $key;
    /**
     * @var float|int
     */
    protected $mb4 = 4 * 1024 * 1024;
    /**
     * @var float|int
     */
    protected $max_limit = 10 * 1024 * 1024;
    /**
     * @var string
     */
    protected $store_folder = '';

    /**
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->store_folder = 'ocr_result/';
    }

    /**
     * @return array
     * @example [
     *  "usage" => [
     *      "credits" => 10,
     *      "used" => 0,
     *      "queued" => 0
     *  ],
     *  "keyType" => "FREE_TRIAL",
     *  "plan" => "FREE_TRIAL"
     * ]
     */
    public function validateKey(): array
    {
        $curl = new Client();
        try {
            $response = $curl->get('https://validator.extracttable.com/', [
                'headers' => [
                    'x-api-key' => $this->key,
                ],
            ]);
        } catch (Exception $e) {
            throw new Exception('Invalid API Key', 1);
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $full_filepath
     * @return array<string> - array of 'filepaths' for recognized tables.
     * @throws Exception
     */
    public function sendFileToOcr(string $full_filepath): array
    {
        $file = new SplFileObject($full_filepath);
        $size = $file->getSize();
        if ($size > $this->max_limit) {
            throw new Exception('File limit for OCR: 64mb.', 1);
        }

        $content = $file->fread($size);
        if ($size >= $this->mb4) {
            $aws_auth = $this->bigFileAuth($full_filepath);
            $this->bigFileUpload($aws_auth, $full_filepath);
            $triggerred = $this->triggerFile('signed_filename', $aws_auth['fields']['key']);
        } else {
            $triggerred = $this->triggerFile('input', fopen($full_filepath, 'r'));
        }

        $result = $this->retrieveResult($triggerred);

        return $this->saveTables($result['Tables']);
    }

    /**
     * @param string $full_filepath
     * @return array
     */
    protected function bigFileAuth(string $full_filepath): array
    {
        $curl = new Client();
        $response = $curl->post('https://bigfile.extracttable.com', [
            'headers' => [
                'x-api-key' => $this->key,
            ],
            'multipart' => [
                [
                    'name' => 'filename',
                    'contents' => FileHelper::name($full_filepath),
                ],
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array $auth
     * @param string $full_filepath
     * @return array
     */
    protected function bigFileUpload(array $auth, string $full_filepath): void
    {
        $curl = new Client();
        $response = $curl->post('https://et-presign-target.s3.amazonaws.com/', [
            'multipart' => [
                [
                    'name' => 'AWSAccessKeyId',
                    'contents' => $auth['fields']['AWSAccessKeyId'],
                ],
                [
                    'name' => 'Content-Type',
                    'contents' => $auth['fields']['Content-Type'],
                ],
                [
                    'name' => 'acl',
                    'contents' => $auth['fields']['acl'],
                ],
                [
                    'name' => 'key',
                    'contents' => $auth['fields']['key'],
                ],
                [
                    'name' => 'policy',
                    'contents' => $auth['fields']['policy'],
                ],
                [
                    'name' => 'signature',
                    'contents' => $auth['fields']['signature'],
                ],
                [
                    'name' => 'x-amz-security-token',
                    'contents' => $auth['fields']['x-amz-security-token'],
                ],
                [
                    'name' => 'file',
                    'contents' => fopen($full_filepath, 'r')
                ],
            ],
        ]);
    }

    /**
     * @param string $key
     * @param $content
     * @return array
     */
    protected function triggerFile(string $key, $content): array
    {
        $curl = new Client();
        $response = $curl->post('https://trigger.extracttable.com', [
            'headers' => [
                'x-api-key' => $this->key,
            ],
            'multipart' => [
                [
                    'name' => $key,
                    'contents' => $content
                ],
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array $triggered
     * @return array
     * @throws Exception
     */
    protected function retrieveResult(array $triggered): array
    {
        while (strtolower($triggered['JobStatus'] ?? '') == 'processing') {
            sleep(5);
            $triggered = $this->checkResult($triggered);
        }

        if (empty($triggered['Tables'])) {
            throw new Exception('Empty Tables! - ' . json_encode($triggered));
        }

        return $triggered;
    }

    /**
     * @param array $triggered
     * @return array
     * @throws Exception
     */
    protected function checkResult(array $triggered): array
    {
        if (empty($triggered['JobId'])) {
            throw new Exception('Empty JobId!');
        }

        $curl = new Client();
        $response = $curl->get('https://getresult.extracttable.com/?JobId=' . $triggered['JobId'], [
            'headers' => [
                'x-api-key' => $this->key,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array $tables
     * @return array
     * @throws Exception
     */
    protected function saveTables(array $tables): array
    {
        $saved_names = [];
        foreach ($tables as $table_array) {
            $name = Uuid::uuid4() . '.csv';
            $content = '';
            foreach ($table_array['TableJson'] as $row) {
                $safe_row = array_map(function ($el) {
                    return str_replace('"', "'", $el);
                }, $row);
                $content .= '"' . join('","', $safe_row) . '"' . "\r\n";
            }
            $filepath = FileHelper::tmpImportFolder($this->store_folder . $name, false);
            Storage::put($filepath, $content);
            $saved_names[] = $this->store_folder . $name;
        }
        return $saved_names;
    }
}
