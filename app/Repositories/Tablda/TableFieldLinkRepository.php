<?php

namespace Vanguard\Repositories\Tablda;


use Illuminate\Support\Collection;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\TableFieldLinkParam;
use Vanguard\Models\Table\TableFieldLinkToDcr;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class TableFieldLinkRepository
{
    protected $service;

    /**
     * TableFieldLinkRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Group of Rows.
     *
     * @param $link_id
     * @return mixed
     */
    public function getLink($link_id)
    {
        return TableFieldLink::where('id', '=', $link_id)->first();
    }

    /**
     * @param $link_param_id
     * @return mixed
     */
    public function getLinkByParam($link_param_id)
    {
        return TableFieldLink::whereHas('_params', function ($q) use ($link_param_id) {
            $q->where('id', '=', $link_param_id);
        })->first();
    }

    /**
     * @param $link_dcr_id
     * @return mixed
     */
    public function getLinkByToDcr($link_dcr_id)
    {
        return TableFieldLink::whereHas('_to_dcrs', function ($q) use ($link_dcr_id) {
            $q->where('id', '=', $link_dcr_id);
        })->first();
    }

    /**
     * @param array $fields_ids
     * @return mixed
     */
    public function getRortForFields(array $fields_ids)
    {
        return TableFieldLink::whereIn('table_field_id', $fields_ids)
            ->whereIn('link_type', ['Record','RorT'])
            ->get();
    }

    /**
     * Add Group of Rows.
     *
     * @param $data
     * [
     *  +table_field_id: int,
     *  +link_type: string,
     *  +icon: string,
     *  -tooltip: string,
     *  -table_ref_condition_id: int,
     *  -listing_field_id: int,
     *  -address_field_id: int,
     * ]
     * @return mixed
     */
    public function addLink($data)
    {
        //activate Links for TableField.
        TableField::where('id', $data['table_field_id'])->update([
            'active_links' => 1
        ]);
        $data['link_preview_show_flds'] = 1;

        $link = TableFieldLink::create( array_merge(
            $this->service->delSystemFields($data),
                $this->service->getModified(),
                $this->service->getCreated()
        ) );

        return $this->loadLink([$link->id]);
    }

    /**
     * Update Group of Rows.
     *
     * @param TableFieldLink $link
     * @param $data
     * [
     *  +table_field_id: int,
     *  -link_type: string,
     *  -icon: string,
     *  -tooltip: string,
     *  -table_ref_condition_id: int,
     *  -listing_field_id: int,
     *  -address_field_id: int,
     * ]
     * @return TableFieldLink
     */
    public function updateLink(TableFieldLink $link, $data)
    {
        if (isset($data['table_app_id']) && $data['table_app_id'] != $link->table_app_id) {
            $this->copyLinkParamsFromApp($link, $data['table_app_id']);
        }

        TableFieldLink::where('id', $link->id)
            ->update( array_merge($this->service->delSystemFields($data), $this->service->getModified()) );

        return $this->loadLink([$link->id]);
    }

    /**
     * Delete Group of Rows.
     *
     * @param int $link_id
     * @return mixed
     */
    public function deleteLink($link_id)
    {
        return TableFieldLink::where('id', $link_id)->delete();
    }

    /**
     * @param array $ids
     * @return mixed
     */
    protected function loadLink(array $ids)
    {
        $links = TableFieldLink::whereIn('id', $ids)
            ->with('_params')
            ->get();

        return $links->count() <= 1 ? $links->first() : $links;
    }

    /**
     * Copy Params from App settings.
     *
     * @param TableFieldLink $link
     * @param $new_app_id
     */
    protected function copyLinkParamsFromApp(TableFieldLink $link, $new_app_id)
    {
        $link->_params()->delete();

        $params = CorrespField::where('correspondence_app_id', $new_app_id)
            ->whereHas('_table', function ($q) {
                $q->where('app_table', 'CALLING_URL_PARAMETERS');
            })
            ->get();

        $mass = [];
        foreach ($params as $p) {
            $mass[] = [
                'table_field_link_id' => $link->id,
                'param' => $p->app_field,
            ];
        }

        TableFieldLinkParam::insert($mass);
    }

    /**
     * Add Link Param.
     *
     * @param $data
     * [
     *  +table_field_link_id: int,
     *  +param: string,
     *  -value: string,
     *  -column_id: int,
     * ]
     * @return mixed
     */
    public function addLinkParam($data)
    {
        return TableFieldLinkParam::create( $this->service->delSystemFields($data) );
    }

    /**
     * Update Link Param.
     *
     * @param int $link_id
     * @param $data
     * [
     *  +table_field_link_id: int,
     *  +param: string,
     *  -value: string,
     *  -column_id: int,
     * ]
     * @return array
     */
    public function updateLinkParam($link_id, $data)
    {
        return TableFieldLinkParam::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete Link Param.
     *
     * @param int $link_id
     * @return mixed
     */
    public function deleteLinkParam($link_id)
    {
        return TableFieldLinkParam::where('id', '=', $link_id)->delete();
    }

    /**
     * Add Link To Dcr Limit.
     *
     * @param $data
     * @return mixed
     */
    public function addLinkToDcr($data)
    {
        $data['status'] = 1;
        $data['add_limit'] = $data['add_limit'] ?? 1;
        return TableFieldLinkToDcr::create( $this->service->delSystemFields($data) );
    }

    /**
     * Update Link To Dcr Limit.
     *
     * @param int $link_id
     * @param $data
     * @return array
     */
    public function updateLinkToDcr($link_id, $data)
    {
        return TableFieldLinkToDcr::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete Link To Dcr Limit.
     *
     * @param int $link_id
     * @return mixed
     */
    public function deleteLinkToDcr($link_id)
    {
        return TableFieldLinkToDcr::where('id', '=', $link_id)->delete();
    }

    /**
     * @param $ref_cond_id
     * @return mixed
     */
    public function syncRefCond($ref_cond_id)
    {
        return TableFieldLink::where('table_ref_condition_id', '=', $ref_cond_id)->update([
            'listing_field_id' => null,
            'address_field_id' => null,
        ]);
    }
}