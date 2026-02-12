
<script>
import {SpecialFuncs} from '../../../../classes/SpecialFuncs';
import {Endpoints} from "../../../../classes/Endpoints";

import {eventBus} from '../../../../app';

/**
 * needed to have:
 * - tableMeta: Object
 * - requestParams: Object
 * - add_click: Number
 * - drawAddon(): Function
 * - currentPageRows: Array - not required
 * - redrawOnEdit: Boolean - not required
 * - updateField: String - not required
 */
export default {
    data: function () {
        return {
            editPopupRow: null,
            editPopupRole: 'add',
            tableRows: null,
            skipRedraw: false,
        }
    },
    watch: {
        add_click(val) {
            this.editPopupRow = this.$root.emptyObject(this.tableMeta);
            this.editPopupRole = 'add';
        },
    },
    methods: {
        cloneReqParam() {
            let reqParam = _.cloneDeep(this.requestParams);
            reqParam.special_params.for_list_view = true;
            return reqParam;
        },
        //Storing
        insertRow(tableRow, selected_row) {
            Endpoints.insertRow(this.tableMeta, tableRow, this.cloneReqParam()).then((data) => {
                this.listViewSync('list-view-insert-row-sync', data, false, selected_row);

                _.each(data.rows, (row) => {
                    if (this.tableRows && _.findIndex(this.tableRows, {id: row.id}) === -1) {
                        this.tableRows.splice(0, 0, row);
                    }
                });

                if (this.redrawOnEdit) {
                    this.drawAddon('insert');
                }
            });
        },
        copyRow(tableRow) {
            Endpoints.massCopyRows(this.tableMeta.id, [tableRow.id]).then((data) => {
                this.listViewSync('list-view-copy-row-sync', data);
                if (this.redrawOnEdit) {
                    this.drawAddon('copy');
                }
            });
        },
        updateRow(tableRow) {
            Endpoints.massUpdateRows(this.tableMeta, [tableRow], this.cloneReqParam()).then((data) => {
                this.listViewSync('list-view-update-row-sync', data);
                if (this.redrawOnEdit || tableRow._changed_field === this.updateField) {
                    this.drawAddon('update');
                }
            });
        },
        deleteRow(tableRow) {
            let rowId = tableRow.id;
            Endpoints.deleteRow(this.tableMeta, tableRow).then((data) => {
                this.listViewSync('list-view-delete-row-sync', data, rowId);

                let idx = _.findIndex(this.tableRows, {id: rowId});
                if (idx > -1) {
                    this.tableRows.splice(idx, 1);
                }

                if (this.redrawOnEdit) {
                    this.drawAddon('delete');
                }
            });
        },
        listViewSync(key, data, rowId, additional) {
            this.skipRedraw = true;
            eventBus.$emit(key, data, rowId, additional);
            this.skipRedraw = false;
        },
        //Popup
        showSrcRecord(lnk, header, tableRow) {
            this.$emit('show-src-record', lnk, header, tableRow);
        },
        popupClick(id) {
            this.editPopupRow = _.find(this.tableRows, {id: Number(id)});
            this.editPopupRole = 'update';
        },
        closePopUp() {
            this.editPopupRow = null;
        },
        //Getting
        getRows(data_range, addon_name, addon_key, additionals) {
            if (this.skipRedraw) {
                return;
            }

            if (!data_range || data_range === '0') {
                this.tableRows = this.currentPageRows || this.$root.listTableRows;
                this.drawAddon();
            } else {
                let params = SpecialFuncs.dataRangeRequestParams(data_range, this.tableMeta.id, this.requestParams, additionals);
                params.special_params.for_list_view = true; //Load filters
                params.ref_cond_id = 'access';
                if (this.$root.addonFilters[this.$root.selectedAddon.code] && this.$root.addonFilters[this.$root.selectedAddon.code][this.$root.selectedAddon.sub_id]) {
                    params.applied_filters = this.$root.addonFilters[this.$root.selectedAddon.code][this.$root.selectedAddon.sub_id];
                }
                Endpoints.getOnlyRows(params).then((data) => {
                    this.tableRows = data.rows;
                    this.$set(this.$root.addonFilters[addon_name], addon_key, data.filters);
                    this.drawAddon();
                });
            }
        },
    },
}
</script>