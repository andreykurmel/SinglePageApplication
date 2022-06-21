<?php

namespace Vanguard\Modules\Permissions;


use Vanguard\Models\Dcr\TableDataRequest;

class PermissionDcr extends PermissionObject
{

    /**
     * @param TableDataRequest $dcr
     */
    public function setTableDcr(TableDataRequest $dcr)
    {
        $this->setBasicsDcr($dcr);
        $this->setEditDcr($dcr);
        $this->setAdditionalsDcr($dcr);
    }

    /**
     * @param TableDataRequest $dcr
     */
    protected function setBasicsDcr(TableDataRequest $dcr)
    {
        $this->can_add = 1;
    }

    /**
     * @param TableDataRequest $dcr
     */
    protected function setEditDcr(TableDataRequest $dcr)
    {
        $view_columns = $dcr->_column_groups->where('_link.view', 1);
        $edit_columns = $dcr->_column_groups->where('_link.edit', 1);

        $this->edit_fields = collect([]);
        $this->view_fields = collect([]);

        $this->view_row_groups = collect([]);
        $this->edit_row_groups = collect([]);

        //get rights for columns
        //edit
        foreach ($edit_columns as $edit_col_group) {
            $this->edit_fields = $this->edit_fields->merge( $edit_col_group->_fields->pluck('field') );
        }
        //view
        foreach ($view_columns as $view_col_group) {
            $this->view_fields = $this->view_fields->merge( $view_col_group->_fields->pluck('field') );
        }
    }

    /**
     * @param TableDataRequest $dcr
     */
    protected function setAdditionalsDcr(TableDataRequest $dcr)
    {
        //add default values
        foreach ($dcr->_default_fields as $df) {
            $this->default_values = $this->default_values->merge([
                $df->_field->field => [
                    'input_type' => $df->_field->input_type,
                    'default' => $df->default,
                ]
            ]);
        }
    }
}