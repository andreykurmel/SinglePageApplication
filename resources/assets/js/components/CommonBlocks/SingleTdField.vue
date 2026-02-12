<template>
    <table :style="tbStyle" v-if="tableMeta && tableHeader && info_row">
        <tr>
            <td v-if="draw_links && info_row.id">
                <template v-for="lnk in tableHeader._links">
                    <link-object :table-meta="tableMeta"
                                 :global-meta="tableMeta"
                                 :table-header="tableHeader"
                                 :table-row="info_row"
                                 :cell-value="tdValue"
                                 :link="lnk"
                                 :user="$root.user"
                                 @show-src-record="showSrcRecord"
                    ></link-object>
                </template>
            </td>

            <td :is="td_component"
                :global-meta="tableMeta"
                :table-meta="tableMeta"
                :settings-meta="$root.settingsMeta"
                :table-row="info_row"
                :table-header="tableHeader"
                :cell-value="tdValue"
                :user="$root.user"
                :cell-height="1"
                :max-cell-rows="0"
                :row-index="-1"
                :table_id="tableMeta.id"
                :behavior="'list_view'"
                :is_td_single="{'draw_links': draw_links}"
                :is-add-row="true"
                :no_width="no_width"
                :with_edit="with_edit"
                :open_edit="open_edit"
                :force_edit="force_edit"
                :extra-style="tbStyle"
                :class="tableHeader.f_type !== 'Boolean' ? 'edit-cell' : ''"
                @updated-cell="updatedCell"
                @show-src-record="showSrcRecord"
                @edit-opened="editOpened"
                @edit-closed="editClosed"
            ></td>
        </tr>
    </table>
</template>

<script>
    import CustomCellTableData from '../CustomCell/CustomCellTableData.vue';
    import CustomCellSystemTableData from '../CustomCell/CustomCellSystemTableData.vue';
    import CustomCellCorrespTableData from '../CustomCell/CustomCellCorrespTableData.vue';
    import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
    import LinkObject from "../CustomCell/InCell/LinkObject.vue";

    export default {
        name: "SingleTdField",
        mixins: [
        ],
        components: {
            LinkObject,
            CustomCellTableData,
            CustomCellSystemTableData,
            CustomCellCorrespTableData,
            CustomCellCondFormat,
        },
        data: function () {
            return {
                info_row: {},
            }
        },
        props:{
            ext_component: String,
            tableMeta: Object,
            tableHeader: Object,
            tdValue: String|Number,
            fixedWidth: Number,
            no_width: Boolean,
            with_edit: Boolean,
            open_edit: Boolean,
            force_edit: Boolean,
            draw_links: Boolean,
            extRow: Object,
        },
        watch: {
            tdValue: {
                handler(val) {
                    this.info_row[this.tableHeader.field] = this.tdValue;
                    if (this.extRow) {
                        this.info_row = _.cloneDeep(this.extRow);
                    } else {
                        axios.post('/ajax/table-data/info-row', {
                            table_id: this.tableMeta.id,
                            table_row: this.info_row,
                        }).then(({data}) => {
                            this.info_row = data.row || {};
                        });
                    }
                    this.info_row[this.tableHeader.field] = this.tdValue;
                },
                immediate: true,
            }
        },
        computed: {
            td_component() {
                return this.ext_component || this.$root.tdCellComponent(this.tableMeta.is_system);
            },
            tbStyle() {
                return this.fixedWidth
                    ? {
                        tableLayout: 'fixed',
                        width: this.fixedWidth+'px',
                    }
                    : null;
            },
        },
        methods: {
            updatedCell(tableRow, header, ddl_option) {
                this.user_field = null;
                this.$emit('updated-td-val', tableRow[this.tableHeader.field], header, ddl_option);
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            editOpened() {
                this.$emit('edit-opened');
            },
            editClosed() {
                this.$emit('edit-closed');
            },
        },
        created() {
            if (this.draw_links) {
                console.log(this.info_row.id, this.tableHeader._links);
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style scoped>
</style>