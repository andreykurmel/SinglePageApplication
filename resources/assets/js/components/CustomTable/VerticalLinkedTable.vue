<template>
    <div class="">

        <custom-table
            v-if="linkedTbMeta && loadedRows && linkedRowsObject[dcrLinkedTable.linked_table_id]"
            :cell_component_name="$root.tdCellComponent(linkedTbMeta.is_system)"
            :global-meta="linkedTbMeta"
            :table-meta="linkedTbMeta"
            :settings-meta="$root.settingsMeta"
            :all-rows="linkedRowsObject[dcrLinkedTable.linked_table_id]"
            :rows-count="linkedRowsObject[dcrLinkedTable.linked_table_id].length"
            :cell-height="1"
            :max-cell-rows="0"
            :is-full-width="true"
            :user="$root.user"
            :with_edit="with_edit"
            :behavior="'dcr_linked_tb'"
            :adding-row="addingRow"
            :table_id="linkedTbMeta.id"
            @added-row="insertLinked"
            @updated-row="updateLinked"
            @delete-row="deleteLinked"
        ></custom-table>

        <div v-else="" class="full-height flex flex--center">
            <img height="75" src="/assets/img/Loading_icon.gif">
        </div>

    </div>
</template>

<script>
import {SpecialFuncs} from "../../classes/SpecialFuncs";

import CheckRowBackendMixin from './../_Mixins/CheckRowBackendMixin.vue';

import CustomTable from "./CustomTable";

export default {
        name: "VerticalLinkedTable",
        mixins: [
            CheckRowBackendMixin,
        ],
        components: {
            CustomTable,
        },
        data: function () {
            return {
                loadedRows: false,
                linkedTbMeta: null,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
            }
        },
        props:{
            parentRowId: Number,
            dcrLinkedTable: {
                type: Object,
                required: true,
            },
            linkedRowsObject: {
                type: Object,//each fields is Array
                required: true,
            },
            with_edit: {
                type: Boolean,
                default: true
            },
        },
        methods: {
            loadLinkedMeta() {
                axios.post('/ajax/table-data/get-headers', {
                    table_id: this.dcrLinkedTable.linked_table_id,
                    user_id: this.$root.user.id,
                    special_params: SpecialFuncs.specialParams('', this.dcrLinkedTable.id),
                }).then(({ data }) => {
                    this.linkedTbMeta = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            loadDcrRows() {
                if (this.parentRowId) {
                    axios.post('/ajax/table-data/get-dcr-rows', {
                        special_params: SpecialFuncs.specialParams('', this.dcrLinkedTable.id),
                        parent_row_id: this.parentRowId,
                    }).then(({data}) => {
                        this.linkedRowsObject[this.dcrLinkedTable.linked_table_id] = data.rows;
                        this.loadedRows = true;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    });
                } else {
                    this.loadedRows = true;
                }
            },
            insertLinked(tableRow) {
                this.linkedRowsObject[this.dcrLinkedTable.linked_table_id].push(tableRow);
                this.redraw();
            },
            updateLinked(tableRow) {
                this.$nextTick(() => {
                    let special_params = SpecialFuncs.specialParams('', this.dcrLinkedTable.id);
                    this.checkRowOnBackend(this.linkedTbMeta.id, tableRow, '', special_params, true).then((data) => {
                        if (data.row) {
                            this.$root.assignObject(data.row, tableRow);
                            this.redraw();
                        }
                    });
                });
            },
            deleteLinked(tableRow, idx) {
                if (idx > -1) {
                    this.linkedRowsObject[this.dcrLinkedTable.linked_table_id].splice(idx, 1);
                    this.redraw();
                }
            },
            redraw() {
                this.loadedRows = false;
                this.$nextTick(() => {
                    this.loadedRows = true;
                    this.$emit('linked-update');
                });
            },
        },
        mounted() {
            this.loadLinkedMeta();
            this.loadDcrRows();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
</style>