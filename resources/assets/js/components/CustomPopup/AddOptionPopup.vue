<template>
    <div class="popup-wrapper" @click.self="hide()">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            Add New Option - DDL: {{ ddl.name }} @ {{ linkTableMeta ? linkTableMeta.name : '' }}
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>

                <div class="popup-content" v-if="selected_ddl_ref < 0">
                    <div class="popup-main">
                        <div class="form-group">
                            <label>Select a Referencing Condition:</label>
                            <select class="form-control" v-model="selected_ddl_ref" @change="getLinkMeta()">
                                <option v-for="ref in ddl._references" :value="ref.id">{{ getRefName(ref.table_ref_condition_id) }}</option>
                            </select>
                            <span>Go to Settings / DDL if to add new RC</span>
                        </div>
                        <div>
                            <label>Or enter a new option:</label>
                            <div class="flex flex--center form-group">
                                <label class="opt-label">Apply to RGRP:</label>
                                <select class="form-control" v-model="extraOptions.apply_target_row_group_id">
                                    <option v-for="rg in tableMeta._row_groups" :value="rg.id">{{ rg.name }}</option>
                                </select>
                            </div>
                            <div class="flex flex--center form-group">
                                <label class="opt-label">Option:</label>
                                <input class="form-control" v-model="optionVal"/>
                            </div>
                            <div class="flex flex--center form-group">
                                <label class="opt-label">Image:</label>
                                <input class="form-control" ref="inline_input" type="file" accept="image/*" @change="uploadItemFile()"/>
                            </div>

                            <div class="right-txt">
                                <button class="btn btn-success"
                                        :disabled="!optionVal"
                                        @click="postNewOption()"
                                >Add</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="popup-content" v-else="" :class="[canAddRC ? 'flex__elem-remain' : '']">
                    <div class="flex__elem__inner">
                        <div class="popup-main full-height">
                            <div class="flex flex--col" v-if="canAddRC">
                                <div class="flex__elem-remain">
                                    <div class="flex__elem__inner popup-overflow" v-if="linkTableMeta">
                                        <vertical-table
                                                class="vert-table"
                                                :td="$root.tdCellComponent(linkTableMeta.is_system)"
                                                :global-meta="linkTableMeta"
                                                :table-meta="linkTableMeta"
                                                :settings-meta="settingsMeta"
                                                :table-row="objectForAdd"
                                                :user="user"
                                                :cell-height="$root.cellHeight"
                                                :max-cell-rows="$root.maxCellRows"
                                                :forbidden-columns="$root.systemFields"
                                                :is-add-row="true"
                                                @updated-cell="checkRowAutocomplete"
                                                @show-src-record="showSrcRecord"
                                        ></vertical-table>
                                    </div>
                                </div>
                                <div class="">
                                    <div class="right-txt">
                                        <button class="btn btn-success" @click="postNewOption()">Add</button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex" v-else-if="linkTableMeta">
                                <h2 style="text-align: center;">No “Add New Record” permission for the referred data table.</h2>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';
    import LinkEmptyObjectMixin from "../_Mixins/LinkEmptyObjectMixin";
    import CheckRowBackendMixin from "../_Mixins/CheckRowBackendMixin";

    import VerticalTable from "../CustomTable/VerticalTable";

    export default {
        name: "AddOptionPopup",
        components: {
            VerticalTable
        },
        mixins: [
            PopupAnimationMixin,
            LinkEmptyObjectMixin,
            CheckRowBackendMixin,
        ],
        data: function () {
            return {
                selected_ddl_ref: -1,
                optionVal: '',
                extraOptions: {
                    apply_target_row_group_id: null,
                    image_path: null,
                },
                extraFields: {},

                linkTableMeta: null,
                //PopupAnimationMixin
                idx: 0,
            }
        },
        props:{
            tableHeader: Object,
            tableRow: Object,
            tableMeta: Object,
            settingsMeta: Object,
            user: Object,
        },
        computed: {
            ddl() {
                return _.find(this.tableMeta._ddls, {id: Number(this.tableHeader.ddl_id)}) || {};
            },
            ddlHasRefs() {
                return (this.ddl && this.ddl._references && this.ddl._references.length);
            },
            getPopupWidth() {
                return 600;//(!this.ddlHasRefs || this.selected_ddl_ref < 0) ? 300 : 600;
            },
            getPopupHeight() {
                return (!this.ddlHasRefs || this.selected_ddl_ref < 0 || !this.canAddRC) ? 'auto' : '80%';
            },
            canAddRC() {
                return this.linkTableMeta && (
                    this.linkTableMeta._is_owner
                    ||
                    (this.linkTableMeta._current_right && this.linkTableMeta._current_right.can_add)
                );
            },
        },
        methods: {
            hide() {
                this.$emit('hide');
            },
            getRefName(ref_id) {
                let ref = _.find(this.tableMeta._ref_conditions, {id: Number(ref_id)});
                return ref ? ref.name : '';
            },
            postNewOption() {
                if (this.selected_ddl_ref > -1) {
                    let sel_ref_ddl = _.find(this.ddl._references, {id: Number(this.selected_ddl_ref)});
                    let tar_fld = _.find(this.linkTableMeta._fields, {id: Number(sel_ref_ddl ? sel_ref_ddl.target_field_id : 0)});
                    this.optionVal = this.objectForAdd[tar_fld ? tar_fld.field : 'id'];
                }

                axios.post('/ajax/ddl/add-option', {
                    ddl_id: this.tableHeader.ddl_id,
                    new_val: this.optionVal || '$id',
                    ddl_ref_id: this.selected_ddl_ref,
                    fields: this.objectForAdd,
                    extra_options: this.extraOptions,
                }).then(({ data }) => {
                    if (data.items) {
                        this.ddl._items = data.items;
                    }
                    //update Value
                    this.tableRow[this.tableHeader.field] = this.optionVal || data.row_id;
                    this.$emit('updated-row', this.tableRow, this.tableHeader);

                    this.hide();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },

            getLinkMeta() {
                let sel_ref_ddl = _.find(this.ddl._references, {id: Number(this.selected_ddl_ref)});
                if (sel_ref_ddl) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/table-data/get-headers', {
                        ref_cond_id: sel_ref_ddl.table_ref_condition_id,
                        user_id: !this.$root.user.see_view ? this.$root.user.id : null,
                    }).then(({ data }) => {
                        this.linkTableMeta = data;
                        let ref_cond = _.find(this.tableMeta._ref_conditions, {id: Number(sel_ref_ddl.table_ref_condition_id)});
                        this.createObjectForAdd();
                        this.checkRowAutocomplete();
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
            //src record
            showSrcRecord(lnk, field, cloneRow) {
                this.$emit('show-src-record', lnk, field, cloneRow);
            },

            //backend autocomplete
            checkRowAutocomplete() {
                let sel_ref_ddl = _.find(this.ddl._references, {id: Number(this.selected_ddl_ref)});
                let ref_cond = _.find(this.tableMeta._ref_conditions, {id: Number(sel_ref_ddl.table_ref_condition_id)});
                this.checkRowOnBackend(
                    this.linkTableMeta.id,
                    this.objectForAdd,
                    this.getLinkParams(ref_cond._items, this.tableRow)
                );
            },

            //file upload
            uploadItemFile() {
                let data = new FormData();
                let file = this.$refs.inline_input ? this.$refs.inline_input.files[0] : null;
                data.append('table_id', this.tableMeta.id);
                data.append('table_field_id', this.tableHeader.id);
                data.append('row_id', 'ddl_'+window.uuidv4());
                data.append('file', file);
                data.append('clear_before', 1);

                if (file) {
                    this.$root.sm_msg_type = 1;
                    axios.post('/ajax/files', data, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                    }).then(({ data }) => {
                        this.extraOptions.image_path = data.filepath + data.filename;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
        },
        mounted() {
            this.runAnimation();
            /*this.selected_ddl_ref = (this.ddl._references && this.ddl._references.length === 1
                ? this.ddl._references[0].id
                : -1);

            if (this.selected_ddl_ref > -1) {
                this.getLinkMeta();
            }*/
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {
        z-index: 2500;

        .popup {
            .popup-content {

                .popup-main {
                    padding: 10px;
                    font-size: 16px;
                }

                .right-txt {
                    text-align: right;
                    margin-top: 15px;
                }

                .opt-label {
                    margin: 0;
                    width: 160px;
                }

            }
        }
    }
</style>