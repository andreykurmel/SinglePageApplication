<?php

namespace Vanguard\AppsModules\StimWid\CreateRls;


use Illuminate\Http\Request;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\Model3dService;
use Vanguard\AppsModules\StimWid\StimSettingsRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\TableDataService;

class RlConnCreator
{
    /**
     * @var StimSettingsRepository
     */
    protected $stimRepo;
    /**
     * @var TableDataService
     */
    protected $dataServ;
    /**
     * @var array
     */
    protected $supRL;
    /**
     * @var array
     */
    protected $supRLnodes;

    /**
     * @var string
     */
    protected $eqpt_table = 'EQPT_RL';

    /**
     * @var string
     */
    protected $rl_table = 'SUPPORT_RL';

    /**
     * @var string
     */
    protected $node_table = 'SUPPORT_RL_NODES';

    /**
     * RlConnCreator constructor.
     */
    public function __construct()
    {
        $this->dataServ = new TableDataService();
        $this->stimRepo = new StimSettingsRepository();
        $this->eqptTB = DataReceiver::app_table_and_fields($this->eqpt_table);
        $this->supRL = DataReceiver::app_table_and_fields($this->rl_table);
        $this->supRLnodes = DataReceiver::app_table_and_fields($this->node_table);
        if (!$this->supRL || !$this->supRLnodes) {
            throw new \Exception('Correspondence Support Tables are not found!');
        }
    }

    /**
     * @param string $type
     * @return array
     */
    public function get(string $type = 'rl')
    {
        return $type == 'rl' ? $this->supRL : $this->supRLnodes;
    }

    /**
     * @param Request $request
     * @return bool|null|string
     */
    public function store_eqpt_local_id(Request $request)
    {
        if (!$request->eqpt_id || !$request->local_id) {
            return 'Incorrect request';
        }

        $model = [ 'local_id' => $request->local_id, ];
        $tablda_meta = (new TableRepository())->getTableByDB($this->eqptTB['data_table']);
        $receiver = (new Model3dService($this->eqptTB['app_table']))->queryReceiver();
        $tablda_row = $this->stimRepo->convertReceiverToTablda($this->eqptTB['_app_maps'], $model);

        return $this->dataServ->updateRow($tablda_meta, (int)$request->eqpt_id, $tablda_row, auth()->id(), ['nohandler' => true]);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function delete_rl(Request $request)
    {
        if (!$request->master_model) {
            return 'Incorrect request';
        }

        $master = self::rl_master($request->master_model);
        $local_id = intval($request->local_id);
        $this->delete_in_db($this->supRL, $master, $local_id ?: null);
        $this->delete_in_db($this->supRLnodes, $master, $local_id ?: null);
        return '';
    }

    /**
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function store_rl(Request $request)
    {
        if (!$request->attach_idx || !$request->eqpt_lc || !$request->master_model || !$request->rl_node_eqpt || !$request->rl_node_member) {
            return 'Incorrect request';
        }

        $master = self::rl_master($request->master_model);
        $this->create_rl_node($master, $request->rl_node_member, $request->eqpt_lc, $request->attach_idx, 'M');
        $this->create_rl_node($master, $request->rl_node_eqpt, $request->eqpt_lc, $request->attach_idx, 'E');
        $this->create_rl($master, $request->eqpt_lc, $request->attach_idx, floatval($request->distance));
        return '';
    }

    /**
     * @param array $master_model
     * @return array
     */
    public static function rl_master(array $master_model)
    {
        return [
            'usergroup' => $master_model['usergroup'],
            'mg_name' => $master_model['structure'],
        ];
    }

    /**
     * @param array $eqpt
     * @param int $idx
     * @param string $type
     * @return string
     */
    protected function node_name(array $eqpt, int $idx, string $type = 'M')
    {
        return 'N_' . $eqpt['local_id'] . '_' . $idx . '_' . $type;
    }

    /**
     * @param array $master
     * @param array $node
     * @param array $eqpt
     * @param int $idx
     * @param string $type
     * @return bool|int|null
     */
    protected function create_rl_node(array $master, array $node, array $eqpt, int $idx, string $type = 'M')
    {
        $node_model = [
            'usergroup' => $master['usergroup'],
            'mg_name' => $master['mg_name'],
            'name' => $this->node_name($eqpt, $idx, $type),
            'x' => $node['x'],
            'y' => $node['y'],
            'z' => $node['z'],
        ];
        return $this->store_to_db($this->supRLnodes, $node_model);
    }

    /**
     * @param array $master
     * @param array $eqpt
     * @param int $idx
     * @param float $dist
     * @return bool|int|null
     */
    protected function create_rl(array $master, array $eqpt, int $idx, float $dist = 0)
    {
        $rl_model = [
            'usergroup' => $master['usergroup'],
            'mg_name' => $master['mg_name'],
            'name' => 'RL_' . $eqpt['local_id'] . '_' . $idx,
            'member' => $eqpt['mbr_name'],
            'equipment' => $eqpt['equipment'],
            'mbr_node' => $this->node_name($eqpt, $idx, 'M'),
            'eqpt_node' => $this->node_name($eqpt, $idx, 'E'),
            'len' => $dist,
            'dist_idx' => $idx,
            'dist_from' => $eqpt['attach_from_'.$idx],
            'dist_value' => $eqpt['attach_value_'.$idx],
        ];
        return $this->store_to_db($this->supRL, $rl_model);
    }

    /**
     * @param array $stim
     * @param array $model
     * @return bool|int|null
     * @throws \Exception
     */
    protected function store_to_db(array $stim, array $model)
    {
        $tablda_meta = (new TableRepository())->getTableByDB($stim['data_table']);
        $receiver = (new Model3dService($stim['app_table']))->queryReceiver();
        $found = $receiver->where('name', '=', $model['name'])->first();
        $tablda_row = $this->stimRepo->convertReceiverToTablda($stim['_app_maps'], $model);
        if ($found) {
            $res = $this->dataServ->updateRow($tablda_meta, (int)$found['_id'], $tablda_row, auth()->id(), ['nohandler' => true]);
        } else {
            $res = $this->dataServ->insertRow($tablda_meta, $tablda_row, auth()->id());
        }
        return $res;
    }

    /**
     * @param array $stim
     * @param array $master
     * @param int|null local_id
     * @return bool
     */
    protected function delete_in_db(array $stim, array $master, int $local_id = null)
    {
        $tablda_meta = (new TableRepository())->getTableByDB($stim['data_table']);
        $receiver = (new Model3dService($stim['app_table'], true))->queryReceiver();

        $receiver->where('usergroup', '=', $master['usergroup'])
            ->where('mg_name', '=', $master['mg_name']);

        if ($local_id > 0) {
            $receiver->where('name', 'like', 'N_'.$local_id.'_%');
        }

        return $receiver->delete();
    }

}