<?php

namespace Vanguard\TabldaApps;


use Illuminate\Support\Collection;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\Data\UserPermisQuery;
use Vanguard\AppsModules\StimWid\Model3dService;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;

class Risa3dWriterRLS
{
    protected $usergroup;
    protected $mg_name;
    protected $app;

    /**
     * @var Collection
     */
    protected $all_nodes;
    /**
     * @var Collection
     */
    protected $all_members;
    /**
     * @var Collection
     */
    protected $rl_members;
    /**
     * @var Collection
     */
    protected $all_designs;
    /**
     * @var Collection
     */
    protected $all_supps;
    /**
     * @var Collection
     */
    protected $all_details;

    /**
     * Risa3dWriterRLS constructor.
     * @param string $usergroup
     * @param string $mg_name
     * @param CorrespApp $correspApp
     */
    public function __construct(string $usergroup, string $mg_name, CorrespApp $correspApp)
    {
        $this->usergroup = $usergroup;
        $this->mg_name = $mg_name;
        $this->app = $correspApp;
    }

    /**
     * @param string $file_path
     * @return string
     */
    public function writeRLs(string $file_path)
    {
        $content = file_get_contents($file_path);
        if (!$content) {
            return 'R3D file not found!';
        }
        $parser = new Risa3dParser($content);

        $this->all_nodes = $parser->subsegment_parse('NODES', true);
        $this->all_nodes = collect($this->all_nodes);
        $this->all_nodes = $this->r3dNodes($this->all_nodes);

        $this->all_members = $parser->subsegment_parse('MEMBERS_MAIN_DATA', true);
        $this->all_members = collect($this->all_members);
        $this->all_members = $this->r3dRLs($this->all_members, $this->all_nodes);

        $this->all_designs = $parser->subsegment_parse('MEMBERS_DESIGN_PARAMETERS', true);
        $this->all_designs = collect($this->all_designs);
        $this->all_designs = $this->r3dRLsDesign($this->all_members, $this->all_designs);

        $this->all_supps = $parser->subsegment_parse('MEMBERS_SUPPLEMENTARY_DATA', true);
        $this->all_supps = collect($this->all_supps);
        $this->all_supps = $this->r3dRLsSupplementary($this->all_members, $this->all_supps);

        $this->all_details = $parser->subsegment_parse('MEMBERS_DETAILING_DATA', true);
        $this->all_details = collect($this->all_details);
        $this->all_details = $this->r3dRLsDetail($this->all_members, $this->all_details);

        $content = $this->replaceData($content);
        file_put_contents($file_path, $content);

        return '';
    }

    /**
     * @param string $content
     * @return string
     */
    protected function replaceData(string $content)
    {
        //Nodes
        $nod_content = "[NODES] <".$this->all_nodes->count().">\r\n";
        foreach ($this->all_nodes as $n) {
            $n['NODE_LABEL'] = $this->str_form($n['NODE_LABEL']);
            $n['a_aa'] = $this->num_form($n['a_aa']);
            $n['b_bb'] = $this->num_form($n['b_bb']);
            $n['c_cc'] = $this->num_form($n['c_cc']);
            $nod_content .= implode(" ", $n) . ";\r\n";
        }
        $nod_content .= "[END_NODES]";

        $content = preg_replace('#\[NODES\](.*?)\[END_NODES]#s', $nod_content, $content);


        //Members
        $rl_content = "[.MEMBERS_MAIN_DATA] <".$this->all_members->count().">\r\n";
        foreach ($this->all_members as $n) {
            $n['MEMBER_LABEL'] = $this->str_form($n['MEMBER_LABEL']);
            $n['DESIGN_LIST'] = $this->str_form($n['DESIGN_LIST']);
            $n['SHAPE_LABEL'] = $this->str_form($n['SHAPE_LABEL']);
            $rl_content .= implode(" ", $n) . ";\r\n";
        }
        $rl_content .= "[.END_MEMBERS_MAIN_DATA]";

        $content = preg_replace('#\[.MEMBERS_MAIN_DATA\](.*?)\[.END_MEMBERS_MAIN_DATA]#s', $rl_content, $content);

        $rl_content = "[.MEMBERS_DESIGN_PARAMETERS] <".$this->all_designs->count().">\r\n";
        foreach ($this->all_designs as $n) {
            $n['MEMBER_LABEL'] = $this->str_form($n['MEMBER_LABEL']);
            $n['x1'] = $this->str_form($n['x1']);
            $n['x2'] = $this->str_form($n['x2']);
            $rl_content .= implode(" ", $n) . ";\r\n";
        }
        $rl_content .= "[.END_MEMBERS_DESIGN_PARAMETERS]";

        $content = preg_replace('#\[.MEMBERS_DESIGN_PARAMETERS\](.*?)\[.END_MEMBERS_DESIGN_PARAMETERS]#s', $rl_content, $content);

        $rl_content = "[.MEMBERS_SUPPLEMENTARY_DATA] <".$this->all_supps->count().">\r\n";
        foreach ($this->all_supps as $n) {
            $n['label'] = $this->str_form($n['label']);
            $rl_content .= implode(" ", $n) . ";\r\n";
        }
        $rl_content .= "[.END_MEMBERS_SUPPLEMENTARY_DATA]";

        $content = preg_replace('#\[.MEMBERS_SUPPLEMENTARY_DATA\](.*?)\[.END_MEMBERS_SUPPLEMENTARY_DATA]#s', $rl_content, $content);

        $rl_content = "[.MEMBERS_DETAILING_DATA] <".$this->all_details->count().">\r\n";
        foreach ($this->all_details as $n) {
            $n['MEMBER_LABEL'] = $this->str_form($n['MEMBER_LABEL']);
            $rl_content .= implode(" ", $n) . ";\r\n";
        }
        $rl_content .= "[.END_MEMBERS_DETAILING_DATA]";

        $content = preg_replace('#\[.MEMBERS_DETAILING_DATA\](.*?)\[.END_MEMBERS_DETAILING_DATA]#s', $rl_content, $content);


        return $content;
    }

    /**
     * @param Collection $nodes
     * @return Collection
     */
    protected function r3dNodes(Collection $nodes)
    {
        $stim = DataReceiver::app_table_and_fields('SUPPORT_RL_NODES');
        $tablda_meta = (new TableRepository())->getTableByDB($stim['data_table']);
        $receiver = (new Model3dService($stim['app_table'], true))->queryReceiver();
        $receiver->where('usergroup', '=', $this->usergroup)
            ->where('mg_name', '=', $this->mg_name);
        $rows = $receiver->get();

        foreach ($rows as $r) {
            if (!$this->presentInCollection($nodes,'NODE_LABEL', $r['name'])) {
                $nodes->push([
                    'NODE_LABEL' => $r['name'],
                    'a_aa' => ($r['x'] ?: 0),
                    'b_bb' => ($r['y'] ?: 0),
                    'c_cc' => ($r['z'] ?: 0),
                    'd_dd' => 0,
                    'rest' => "65535 0 0 -1 -1 0",
                ]);
            }
        }

        return $nodes;
    }

    /**
     * @param Collection $rls
     * @param Collection $nodes
     * @return Collection
     */
    protected function r3dRLs(Collection $rls, Collection $nodes)
    {
        $stim = DataReceiver::app_table_and_fields('SUPPORT_RL');
        $tablda_meta = (new TableRepository())->getTableByDB($stim['data_table']);
        $receiver = (new Model3dService($stim['app_table'], true))->queryReceiver();
        $receiver->where('usergroup', '=', $this->usergroup)
            ->where('mg_name', '=', $this->mg_name);
        $rows = $receiver->get();
        $this->rl_members = collect($rows);

        foreach ($rows as $r) {
            if (!$this->presentInCollection($rls,'MEMBER_LABEL', $r['name'])) {
                $mbr = $nodes->where('NODE_LABEL', '=', $r['mbr_node'])->first();
                $eqpt = $nodes->where('NODE_LABEL', '=', $r['eqpt_node'])->first();
                $rls->push([
                    'MEMBER_LABEL' => $r['name'],
                    'DESIGN_LIST' => 'None',
                    'SHAPE_LABEL' => '',
                    'a' => $nodes->search(function ($item, $key) use ($r) { return $item['NODE_LABEL'] == $r['mbr_node']; }),
                    'b' => $nodes->search(function ($item, $key) use ($r) { return $item['NODE_LABEL'] == $r['eqpt_node']; }),
                    'c' => '0',
                    'd_dd' => '0',
                    'rest' => "1 0 0 0 7 0 0 0 1 0 19 0 -1 -1 -1 1 0 0 0 0 0 0 0 0 0 0;",
                    // 'rest' => "1 0 0 0 7 0 0 0 1 0 19 0 -1 -1 -1 1 0 0 0 0 0 0 0 0 0 0; // ".$mbr['NODE_LABEL']." ".$eqpt['NODE_LABEL']." RIGID",
                ]);
            }
        }

        return $rls;
    }

    /**
     * @param Collection $rls
     * @param Collection $designs
     * @return Collection
     */
    protected function r3dRLsDesign(Collection $rls, Collection $designs)
    {
        foreach ($rls as $r) {
            if (!$this->presentInCollection($designs,'MEMBER_LABEL', $r['MEMBER_LABEL'])) {
                $designs->push([
                    'MEMBER_LABEL' => $r['MEMBER_LABEL'],
                    'rest1' => "0 -1 -1 -4 -1 -1 -1 -1 -1 -1 -1 -1 -1 0 0 0 -1 -1 -1 -1 0 0",
                    'x1' => "Optimize",
                    'x2' => "Optimize",
                    'rest2' => "-1 0 -1 0 0",
                ]);
            }
        }
        return $designs;
    }

    /**
     * @param Collection $rls
     * @param Collection $supps
     * @return Collection
     */
    protected function r3dRLsSupplementary(Collection $rls, Collection $supps)
    {
        foreach ($rls as $r) {
            if (!$this->presentInCollection($supps,'label', $r['MEMBER_LABEL'])) {
                $supps->push([
                    'label' => $r['MEMBER_LABEL'],
                    'rest' => "1 0 0 -1 -1 65535 -1 \" \" 0",
                ]);
            }
        }
        return $supps;
    }

    /**
     * @param Collection $rls
     * @param Collection $details
     * @return Collection
     */
    protected function r3dRLsDetail(Collection $rls, Collection $details)
    {
        foreach ($rls as $r) {
            if (!$this->presentInCollection($details,'MEMBER_LABEL', $r['MEMBER_LABEL'])) {
                $details->push([
                    'MEMBER_LABEL' => $r['MEMBER_LABEL'],
                    'rest' => "10 0 0 0 10 0 0 0",
                ]);
            }
        }
        return $details;
    }

    /**
     * @param Collection $coll
     * @param string $field
     * @param $search
     * @return bool
     */
    protected function presentInCollection(Collection $coll, string $field, $search)
    {
        foreach ($coll as $el) {
            if (trim($el[$field] ?? '') == trim($search ?? '')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $val
     * @return mixed
     */
    protected function num_form($val)
    {
        $res = sprintf("%1.12e", $val);
        $res = preg_replace( '/([-+])(\d)$/', '$1 0$2', $res);
        return preg_replace('/\s/', '', $res);
    }

    /**
     * @param $val
     * @param int $len
     * @return string
     */
    protected function str_form($val, $len = 32)
    {
        return '"' . str_pad(substr($val, 0, $len), $len) . '"';
    }
}