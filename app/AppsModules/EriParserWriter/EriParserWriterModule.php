<?php

namespace Vanguard\AppsModules\EriParserWriter;


use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\File;
use Vanguard\Models\RemoteFile;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\TableFieldLinkEriField;
use Vanguard\Models\Table\TableFieldLinkEriPart;
use Vanguard\Models\Table\TableFieldLinkEriPartActive;
use Vanguard\Models\Table\TableFieldLinkEriTable;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\FileService;
use Vanguard\Services\Tablda\TableDataService;

class EriParserWriterModule
{
    /** @var TableDataService */
    protected $dataService;

    /** @var array */
    protected $masterRow = [];

    /**
     * @param Table $table
     * @param TableFieldLink $link
     */
    public function __construct(Table $table, TableFieldLink $link)
    {
        $this->table = $table;
        $this->link = $link;

        $this->dataService = new TableDataService();
    }

    /**
     * @param int $row_id
     * @return array
     */
    public function getParts(int $row_id): array
    {
        $parser = CorrespApp::where('code', '=', 'eri_parser')->first();
        $writer = CorrespApp::where('code', '=', 'eri_writer')->first();

        $key = '';
        $parts = [];
        $actives = collect([]);

        if ($this->link->table_app_id == $parser->id) {
            $key = 'parser_active';
            $parts = $this->link->_eri_parts?->toArray() ?: [];
            $actives = $this->retrieveActiveParts($this->link, $row_id, $key);
        }
        if ($this->link->table_app_id == $writer->id) {
            $key = 'writer_active';
            $parserLink = (new TableFieldLinkRepository())->getLink($this->link->eri_parser_link_id);
            $parts = $parserLink->_eri_parts?->toArray() ?: [];
            $actives = $this->retrieveActiveParts($parserLink, $row_id, $key);
        }

        $actives = $actives->mapWithKeys(function ($act) use ($key) {
            return [$act->eri_part_id => $act[$key]];
        })->toArray();

        return array_map(function ($part) use ($actives) {
            return [
                'id' => $part['id'],
                'name' => $part['part'],
                'checked' => $actives[$part['id']] ?? false,
            ];
        }, $parts);
    }

    /**
     * @param TableFieldLink $link
     * @param int $row_id
     * @param string $key
     * @return Collection
     */
    protected function retrieveActiveParts(TableFieldLink $link, int $row_id, string $key): Collection
    {
        $actives = TableFieldLinkEriPartActive::where('link_id', '=', $link->id)
            ->where('row_id', '=', $row_id)
            ->get();

        if (! $actives->count()) {
            foreach ($link->_eri_parts as $part) {
                $act = TableFieldLinkEriPartActive::create([
                    'link_id' => $link->id,
                    'eri_part_id' => $part->id,
                    'row_id' => $row_id,
                    'parser_active' => 1,
                    'writer_active' => 1,
                ]);
                $actives->push($act);
            }
        }

        return $actives;
    }

    /**
     * @param TableFieldLink $link
     * @param int $row_id
     * @param string $key
     * @param array $active_parts
     * @return void
     */
    protected function storeActiveParts(TableFieldLink $link, int $row_id, string $key, array $active_parts): void
    {
        $actives = $this->retrieveActiveParts($link, $row_id, $key);
        foreach ($actives as $act) {
            $act->update([
                $key => in_array($act->eri_part_id, $active_parts),
            ]);
        }
    }

    /**
     * @param int $row_id
     * @param array $active_part_ids
     * @return string
     * @throws Exception
     */
    public function parse(int $row_id, array $active_part_ids): string
    {
        $this->masterRow = $this->dataService->getDirectRow($this->table, $row_id, ['none'])->toArray();
        $this->storeActiveParts($this->link, $row_id, 'parser_active', $active_part_ids);

        //Receive JSON
        $content = (new FileService())->getContent($this->table->id, $this->link->eri_parser_file_id, $row_id);
        if (!$content) {
            throw new Exception('ERI file for parsing was not found!', 1);
        }

        //Load relations
        $this->loadLinkRelations($this->link);

        if ($this->link->eri_remove_prev_records) {
            $this->removeRecordsByInheritedValues();
        }

        //Parse rows
        $this->parseContent($content, $row_id, $active_part_ids);

        return 'Parsing completed!';
    }

    /**
     * @param int $row_id
     * @param array $active_part_ids
     * @return string
     * @throws Exception
     */
    public function export(int $row_id, array $active_part_ids): string
    {
        $this->masterRow = $this->dataService->getDirectRow($this->table, $row_id, ['none'])->toArray();

        //Load link with relations
        $link = (new TableFieldLinkRepository())->getLink($this->link->eri_parser_link_id);
        if (!$link) {
            throw new Exception('ERI parser link was not found!', 1);
        }
        $this->loadLinkRelations($link);
        $this->storeActiveParts($link, $row_id, 'writer_active', $active_part_ids);

        //Receive JSON
        try {
            $fileService = new FileService();
            $content = $fileService->getContent($this->table->id, $this->link->eri_writer_file_id, $row_id);
            $file = $fileService->getFile($this->table->id, $this->link->eri_writer_file_id, $row_id);
            if (!$content) {
                $content = $fileService->getContent($link->_field->table_id, $link->eri_parser_file_id, $row_id);
                $file = $fileService->getFile($link->_field->table_id, $link->eri_parser_file_id, $row_id);
            }
        } catch (Exception $e) {
            $content = '';
            $file = null;
        }

        //Export rows
        $content = $this->exportToContent($link, $content, $row_id, $active_part_ids);
        $eriFile = $this->eriFileName($file);
        (new FileRepository())->insertFileAlias($this->table->id, $this->link->eri_writer_file_id, $row_id, $eriFile, $content);

        return 'Export completed!';
    }

    /**
     * @param TableFieldLink $link
     * @return void
     */
    protected function loadLinkRelations(TableFieldLink $link): void
    {
        $link->load([
            '_eri_tables' => function ($query) {
                $query->with([
                    '_eri_table',
                    '_eri_fields' => function ($sub) {
                        $sub->with(['_field', '_master_field', '_conversions']);
                    }
                ]);
            },
            '_eri_parts' => function ($query) {
                $query->with(['_part_variables']);
            },
        ]);
    }

    /**
     * @param RemoteFile|File|null $file
     * @return string
     */
    protected function eriFileName($file): string
    {
        $fieldRepo = new TableFieldRepository();

        $array = [];
        if ($this->link->eri_writer_filename_fields) {
            $fields = $this->link->eri_writer_filename_fields;
            $fields = is_array($fields) ? $fields : json_decode($fields, true);
            foreach ($fields as $fieldId) {
                $fld = $fieldRepo->getField($fieldId);
                if ($fld) {
                    $array[] = preg_replace('/[^\w\d]/i', '_', $this->masterRow[$fld->field] ?? $fld->name);
                }
            }
        }
        if ($this->link->eri_writer_filename_year) {
            $array[] = Carbon::now()->format('Ymd');
        }
        if ($this->link->eri_writer_filename_time) {
            $array[] = Carbon::now()->format('His');
        }

        return $array
            ? implode('_', $array) . '.eri'
            : ($file ? $file->filename : 'exported.eri');
    }

    /**
     * @param TableFieldLink $link
     * @param string $content
     * @param int $row_id
     * @param array $active_part_ids
     * @return string
     * @throws Exception
     */
    protected function exportToContent(TableFieldLink $link, string $content, int $row_id, array $active_part_ids): string
    {
        foreach ($link->_eri_tables as $eriTable) {
            if ($eriTable->is_active && $eriTable->_eri_part && in_array($eriTable->eri_part_id, $active_part_ids)) {
                $part = $eriTable->_eri_part;

                $contentKeys = $part->section_r_identifier ? $this->getVariable($content, $part->section_r_identifier) : [];
                $rows = $this->dataService->getEriRows($eriTable->_eri_table, $this->generalEriKey($row_id, $link));

                $recNum = $part->section_q_identifier;
                $recIdent = $part->section_r_identifier;
                if ($recIdent && $recNum) {
                    //Replace present in the file records - by records from the table
                    $rowsString = "\n" . $part->section_q_identifier . "=" . count($rows) . "\n\n";
                    foreach ($rows as $idx => $row) {
                        $rowsString .= "$recIdent=" . ($idx + 1) . "\n";
                        foreach (EriVariables::getAllFor($recIdent) as $eriVar) {
                            $eriField = $eriTable->_eri_fields->where('eri_variable', '=', $eriVar)->first();
                            if ($eriField && $eriField->is_active && $eriField->_field) {
                                $newValue = $this->getNewValue($row, $eriField);
                                $rowsString .= "$eriVar=" . $newValue . "\n";
                            } else {
                                $rowsString .= "$eriVar=\n";
                            }
                        }
                        $rowsString .= "\n";
                    }

                    if (preg_match("/[\|\n]$recNum=/i", $content)) {
                        $lastVar = EriVariables::getLastFor($recIdent);
                        $lastVar = preg_match("/[\|\n]$lastVar=/i", $content) ? ".*$lastVar=" : '';
                        $content = preg_replace("/[\|\n]$recNum=" . $lastVar . "[^\|\n]*[\|\n]/ims", $rowsString, $content);
                    } else {
                        $content .= "\n" . $rowsString;
                    }
                } else {
                    //Update Records
                    foreach ($rows as $row) {
                        $identifier = explode('_', $row['request_id'] ?? '');
                        $identifier = Arr::last($identifier);
                        foreach ($eriTable->_eri_fields as $eriField) {
                            if ($identifier && $eriField->is_active && $eriField->eri_variable && $eriField->_field) {

                                $idx = 0;
                                $variable = $eriField->eri_variable;
                                $newValue = $this->getNewValue($row, $eriField);
                                $content = preg_replace_callback("#[\|\n]$variable=(?P<value>.*?)[\|\n]#mi", function ($matches) use (&$idx, $contentKeys, $identifier, $newValue) {
                                    $num = $contentKeys ? ($contentKeys[$idx] ?? -1) : $idx;

                                    $idx += 1;
                                    if ($num == $identifier) {
                                        return str_replace($matches[1], $newValue, $matches[0]);
                                    } else {
                                        return $matches[0];
                                    }
                                }, $content);

                            }
                        }
                    }
                }

                //Update Row's Identifiers
                foreach ($rows as $idx => $row) {
                    $identifier = explode('_', $row['request_id'] ?? '');
                    $identifier = Arr::last($identifier);
                    if ($identifier != ($idx + 1)) {
                        $this->dataService->updateRow(
                            $eriTable->_eri_table,
                            $row['id'],
                            ['request_id' => $this->generalEriKey($row_id, $link) . ($idx + 1)],
                            $eriTable->_eri_table->user_id
                        );
                    }
                }
            }
        }

        return $content;
    }

    /**
     * @param $row
     * @param TableFieldLinkEriField $eriField
     * @return string
     */
    protected function getNewValue($row, TableFieldLinkEriField $eriField): string
    {
        $newValue = $eriField->_master_field
            ? ($this->masterRow[$eriField->_master_field->field] ?? '')
            : ($row[$eriField->_field->field] ?? '');
        return $this->convert($eriField, $newValue ?? '', true);
    }

    /**
     * @return void
     */
    protected function removeRecordsByInheritedValues(): void
    {
        foreach ($this->link->_eri_tables as $eriTable) {
            if ($eriTable->is_active && $eriTable->eri_table_id != $this->table->id) {
                $conditions = [];
                foreach ($eriTable->_eri_fields as $eriField) {
                    if ($eriField->is_active && $eriField->_master_field && $eriField->_field) {
                        $conditions[$eriField->_field->field] = $this->masterRow[$eriField->_master_field->field] ?? '';
                    }
                }

                if ($conditions) {
                    $this->dataService->removeByParams($eriTable->_eri_table, $conditions);
                }
            }
        }
    }

    /**
     * @param string $content
     * @param int $row_id
     * @param array $active_part_ids
     * @return void
     * @throws Exception
     */
    protected function parseContent(string $content, int $row_id, array $active_part_ids): void
    {
        foreach ($this->link->_eri_tables as $eriTable) {
            if ($eriTable->is_active && $eriTable->_eri_part && in_array($eriTable->eri_part_id, $active_part_ids)) {
                $part = $eriTable->_eri_part;
                $partContent = $part->type == '1D' ? $this->getPartContent($content, $part) : $content;

                $rows = [];
                foreach ($eriTable->_eri_fields as $eriField) {
                    if ($eriField->is_active && $eriField->eri_variable && $eriField->_field) {
                        $values = $this->getVariable($partContent, $eriField->eri_variable);
                        foreach ($values as $idx => $value) {
                            if (empty($rows[$idx])) {
                                $rows[$idx] = [];
                            }
                            $rows[$idx][$eriField->_field->field] = $this->convert($eriField, $value ?? '');
                        }
                    }
                }

                if ($part->section_r_identifier) {
                    $values = $this->getVariable($partContent, $part->section_r_identifier);
                    foreach ($values as $idx => $value) {
                        if (empty($rows[$idx])) {
                            $rows[$idx] = [];
                        }
                        $rows[$idx][$part->section_r_identifier] = $value;
                    }
                }

                foreach ($rows as $idx => $row) {
                    if (empty($row['request_id'])) {
                        $rows[$idx]['request_id'] = $this->eriKey($row_id, $row, $eriTable, $idx+1);
                    }
                    foreach ($eriTable->_eri_fields as $eriField) {
                        if ($eriField->is_active && $eriField->_master_field && $eriField->_field) {
                            $rows[$idx][$eriField->_field->field] = $this->convert($eriField, $this->masterRow[$eriField->_master_field->field] ?? '');
                        }
                    }
                }

                $this->storeRows($eriTable, $rows, $row_id);
            }
        }
    }

    /**
     * @param TableFieldLinkEriTable $eriTable
     * @param array $rows
     * @param int $row_id
     * @return void
     * @throws Exception
     */
    protected function storeRows(TableFieldLinkEriTable $eriTable, array $rows, int $row_id): void
    {
        foreach ($rows as $idx => $row) {
            $eriKey = $this->eriKey($row_id, $row, $eriTable, $idx+1);
            $present = $this->dataService->getRowBy($eriTable->_eri_table, 'request_id', $eriKey);

            if ($eriTable->eri_table_id == $this->table->id) {
                $present = $this->masterRow;
            }

            $fields = $row;
            unset($fields[$eriTable->_eri_part->section_r_identifier]);

            if ($present) {
                $this->dataService->updateRow($eriTable->_eri_table, $present['id'], $fields, $eriTable->_eri_table->user_id);
            } else {
                $this->dataService->insertRow($eriTable->_eri_table, $fields);
            }
        }
    }

    /**
     * @param string $content
     * @param TableFieldLinkEriPart $part
     * @return string
     */
    protected function getPartContent(string $content, TableFieldLinkEriPart $part): string
    {
        $ma = [];
        $variable = preg_replace('#\[|\]#i', '', $part->section_q_identifier);
        preg_match_all("#\[$variable\](?P<value>[^\[]*)#i", $content, $ma);
        $result = Arr::first($ma['value'] ?? ['']);

        if (! $result) {
            $ma = [];
            $variable = $part->part;
            preg_match_all("#\[$variable\](?P<value>[^\[]*)#i", $content, $ma);
            $result = Arr::first($ma['value'] ?? ['']);
        }

        return $result ?: '';
    }

    /**
     * @param string $content
     * @param string $variable
     * @return array
     */
    protected function getVariable(string $content, string $variable): array
    {
        if ($variable == '[ALL]') {
            return [
                0 => trim($content)
            ];
        }

        $ma = [];
        preg_match_all("#[\|\n]$variable=(?P<value>.*?)[\|\n]#i", $content, $ma);
        return $ma['value'] ?? [];
    }

    /**
     * @param TableFieldLinkEriField $eriField
     * @param string $value
     * @param bool $reverse
     * @return string
     */
    protected function convert(TableFieldLinkEriField $eriField, string $value, bool $reverse = false): string
    {
        $fromKey = $reverse ? 'tablda_convers' : 'eri_convers';
        $toKey = $reverse ? 'eri_convers' : 'tablda_convers';
        $conv = $eriField->_conversions->where($fromKey, '=', $value)->first();
        return $conv ? $conv->{$toKey} : $value;
    }

    /**
     * @param int $row_id
     * @param array $row
     * @param TableFieldLinkEriTable $eriTable
     * @param int $idx
     * @return string
     */
    protected function eriKey(int $row_id, array $row, TableFieldLinkEriTable $eriTable, int $idx): string
    {
        $identifier = $row && $eriTable ? ($row[$eriTable->_eri_part->section_r_identifier] ?? $idx) : $idx;
        return $this->generalEriKey($row_id) . $identifier;
    }

    /**
     * @param int $row_id
     * @return string
     */
    protected function generalEriKey(int $row_id, TableFieldLink $link = null): string
    {
        $id = $link ? $link->id : $this->link->id;
        return 'eri_'
            . $id
            . '_'
            . $row_id
            . '_';
    }
}