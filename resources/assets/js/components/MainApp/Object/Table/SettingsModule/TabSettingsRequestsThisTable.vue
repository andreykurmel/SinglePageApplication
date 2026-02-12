<template>
    <div class="flex flex--col relative"
         :style="$root.themeMainBgStyle"
    >
        <div class="vertical-buttons">
            <button class="btn btn-default btn-sm" :class="{active : activeColThisTbTab === 'col_group'}" :style="textSysStyle" @click="setTab('col_group')">
                Availability/Permission
            </button>
            <button class="btn btn-default btn-sm mr5" :class="{active : activeColThisTbTab === 'tb_display'}" :style="textSysStyle" @click="setTab('tb_display')">
                Display
            </button>
            <button class="btn btn-default btn-sm mr5" :class="{active : activeColThisTbTab === 'default'}" :style="textSysStyle" @click="setTab('default')">
                Default Values
            </button>
        </div>
        <div class="absolute-frame" style="left: 32px; z-index: 100; background: #FFF; border-left: 1px solid #CCC; overflow: auto;">
            <custom-table
                v-if="dcrObject"
                v-show="activeColThisTbTab === 'col_group'"
                :cell_component_name="'custom-cell-settings-dcr'"
                :global-meta="tableMeta"
                :table-meta="$root.settingsMeta['table_data_requests_2_table_column_groups']"
                :all-rows="dcrObject._data_request_columns"
                :rows-count="dcrObject._data_request_columns.length"
                :cell-height="1"
                :max-cell-rows="0"
                :is-full-width="true"
                :use_theme="true"
                :with_edit="withEdit"
                :adding-row="canAddingRow ? {active: true, position: 'bottom'} : {active: false}"
                :user="$root.user"
                :show-info-key="'add_notes'"
                :behavior="'dcr_group'"
                :forbidden-columns="$root.systemFields"
                :excluded_row_values="[{ field: 'view', excluded: [0] }]"
                @added-row="addGroupColumnPermis"
                @updated-row="updateGroupColumnPermis"
                @delete-row="deleteGroupColumnPermis"
            ></custom-table>

            <tab-settings-requests-display-wrap
                v-if="dcrObject && activeColThisTbTab === 'tb_display'"
                :table-meta="tableMeta"
                :selected-dcr="dcrObject"
                :with-edit="$root.ownerOf(tableMeta) && withEdit"
                @check-row="dcrSettCheck"
            ></tab-settings-requests-display-wrap>

            <default-fields-table
                v-if="dcrObject && activeColThisTbTab === 'default'"
                :table-dcr="dcrObject"
                :user-group-id="null"
                :table-meta="tableMeta"
                :default-fields="dcrObject._default_fields"
                :user="$root.user"
                :with_edit="withEdit"
                :cell-height="1"
                :max-cell-rows="0"
                :extra-pivot-fields="dcrObject._fields_pivot"
                style="padding: 10px 15px;"
            ></default-fields-table>
        </div>
    </div>
</template>

<script>
import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

import CustomTable from '../../../../CustomTable/CustomTable';
import DefaultFieldsTable from '../../../../CustomPopup/DefaultFieldsTable';
import TabSettingsRequestsDisplayWrap from "./TabSettingsRequestsDisplayWrap";

export default {
    name: "TabSettingsRequestsThisTable",
    components: {
        TabSettingsRequestsDisplayWrap,
        CustomTable,
        DefaultFieldsTable,
    },
    mixins: [
        CellStyleMixin,
    ],
    data: function () {
        return {
            activeColThisTbTab: 'col_group',
        }
    },
    props: {
        tableMeta: Object,
        table_id: Number|null,
        withEdit: Boolean,
        dcrObject: Object,
        canAddingRow: Boolean,
    },
    computed: {
    },
    watch: {
        table_id: function(val) {
            this.setTab('col_group');
        }
    },
    methods: {
        setTab(key) {
            this.activeColThisTbTab = key;
            this.$emit('subtab-change', this.activeColThisTbTab);
        },
        //User Group Columns Functions
        addGroupColumnPermis(tableColumn) {
            this.updateGroupColumn(tableColumn.table_column_group_id, 1, 0);
        },
        updateGroupColumnPermis(tableColumn) {
            this.updateGroupColumn(tableColumn.table_column_group_id, tableColumn.view, tableColumn.edit);
        },
        deleteGroupColumnPermis(tableColumn) {
            this.updateGroupColumn(tableColumn.table_column_group_id, 0, 0);
        },
        updateGroupColumn(tb_column_group_id, viewed, edited) {

            let req_columns = this.dcrObject._data_request_columns;
            let columnGr = _.find(req_columns, {table_column_group_id: Number(tb_column_group_id)});

            if (columnGr) {
                viewed = viewed === undefined ? columnGr.view : viewed;
                edited = edited === undefined ? columnGr.edit : edited;
            }

            this.$root.sm_msg_type = 1;
            axios.post('/ajax/table-data-request/column', {
                table_data_request_id: this.dcrObject.id,
                table_column_group_id: tb_column_group_id,
                view: viewed ? 1 : 0,
                edit: edited ? 1 : 0,
            }).then(({ data }) => {
                let idx = _.findIndex(req_columns, (el) => {
                    return el.table_column_group_id == tb_column_group_id;
                });
                if (idx > -1) {
                    req_columns.splice(idx, 1 ,data);
                } else {
                    req_columns.push( data );
                }
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                this.$root.sm_msg_type = 0;
            });
        },

        //Fields settings
        dcrSettCheck(field, val) {
            this.$emit('check-row', field, val);
        },
    },
    mounted() {
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
    @import "./TabSettingsPermissions";

    .vertical-buttons {
        transform: rotate(-90deg);
        transform-origin: top right;
        right: calc(100% - 5px);
        position: absolute;
        white-space: nowrap;
        top: 5px;
        display: flex;
        flex-direction: row-reverse;

        .btn {
            outline: none;
            background-color: #CCC;
        }
        .btn.active {
            background-color: #FFF;
        }
    }
</style>