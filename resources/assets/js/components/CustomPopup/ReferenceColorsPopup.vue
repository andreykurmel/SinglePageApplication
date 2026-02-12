<template>
    <div class="popup-wrapper" v-if="tableMeta && show_popup && ddl && ddl_ref" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            Detail: {{ ddl.name }} - #{{ refIdx() }}
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>

                <div class="popup-content flex__elem-remain">
                    <div class="popup-main full-height flex flex--col" style="padding: 10px;">
                        <div>
                            <label style="margin-bottom: 7px;">Select a source table field:</label>
                            <div class="flex flex--center-v form-group">
                                <label>for color:&nbsp;</label>
                                <select class="form-control"
                                        v-model="ddl_ref.color_field_id"
                                        @change="updateReferenceRow('color_field_id')"
                                        :style="textSysContentSt"
                                        style="width: 200px;"
                                >
                                    <option :value="null"></option>
                                    <option v-for="fld in fields_from_condition"
                                            v-if="fld.f_type === 'Color'"
                                            :value="fld.id"
                                    >{{ fld.name }}</option>
                                </select>
                                <span>&nbsp;&nbsp;&nbsp;</span>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check checkbox-input"
                                          @click="updateReferenceRow('has_individ_colors', true)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="ddl_ref.has_individ_colors" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <label>&nbsp;Allow local color editing.</label>

                                <button v-if="ddl_ref.has_individ_colors"
                                        class="btn btn-sm btn-primary blue-gradient"
                                        :style="$root.themeButtonStyle"
                                        @click="createLoadRefColors('clear_color')"
                                >Clear</button>
                                <button v-if="ddl_ref.has_individ_colors"
                                        class="btn btn-sm btn-primary blue-gradient"
                                        :style="$root.themeButtonStyle"
                                        @click="createLoadRefColors('auto_color')"
                                >Auto Fill</button>
                            </div>
                            <div class="flex flex--center-v form-group">
                                <label>for image:&nbsp;</label>
                                <select class="form-control"
                                        v-model="ddl_ref.image_field_id"
                                        @change="updateReferenceRow('image_field_id')"
                                        :style="textSysContentSt"
                                        style="width: 200px;"
                                >
                                    <option :value="null"></option>
                                    <option v-for="fld in fields_from_condition"
                                            v-if="fld.f_type === 'Attachment'"
                                            :value="fld.id"
                                    >{{ fld.name }}</option>
                                </select>
                                <span>&nbsp;&nbsp;&nbsp;</span>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check checkbox-input"
                                          @click="updateReferenceRow('has_individ_images', true)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="ddl_ref.has_individ_images" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <label>&nbsp;Allow local image editing.</label>
                            </div>
                            <div class="flex flex--center-v form-group">
                                <label>for max selections:&nbsp;</label>
                                <select class="form-control"
                                        v-model="ddl_ref.max_selections_field_id"
                                        @change="updateReferenceRow('max_selections_field_id')"
                                        :style="textSysContentSt"
                                        style="width: 200px;"
                                >
                                    <option :value="null"></option>
                                    <option v-for="fld in fields_from_condition"
                                            v-if="['Decimal', 'Currency', 'Percentage', 'Integer', 'String'].indexOf(fld.f_type) > -1"
                                            :value="fld.id"
                                    >{{ fld.name }}</option>
                                </select>
                                <span>&nbsp;&nbsp;&nbsp;</span>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check checkbox-input"
                                          @click="updateReferenceRow('has_individ_max_selections', true)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="ddl_ref.has_individ_max_selections" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <label>&nbsp;Allow local max selections editing.</label>
                            </div>
                            <label style="margin-bottom: 7px;">Changing of color/image/max selections field will recreate DDL options.</label>
                        </div>

                        <div class="flex__elem-remain">
                            <custom-table
                                v-if="ddl_ref._reference_clr_img"
                                :cell_component_name="'custom-cell-settings-ddl'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['ddl_reference_colors']"
                                :settings-meta="$root.settingsMeta"
                                :all-rows="ddl_ref._reference_clr_img || []"
                                :rows-count="ddl_ref._ref_clr_img_count || 0"
                                :page="page"
                                :cell-height="1.5"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :adding-row="addingRow"
                                :parent-row="ddl_ref"
                                :is-pagination="true"
                                :behavior="'settings_ddl_items'"
                                :user="$root.user"
                                :available-columns="['ref_value','image_ref_path','color','max_selections']"
                                :use_theme="true"
                                :style="{paddingBottom: '32px'}"
                                @added-row="addRefColorRow"
                                @updated-row="updateRefColorRow"
                                @delete-row="deleteRefColorRow"
                                @change-page="changePage"
                            ></custom-table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import {eventBus} from "../../app";

import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';
import CellStyleMixin from './../_Mixins/CellStyleMixin';

import CustomTable from "../CustomTable/CustomTable";
import {RefCondHelper} from "../../classes/helpers/RefCondHelper";

export default {
        name: "ReferenceColorsPopup",
        components: {
            CustomTable,
        },
        mixins: [
            PopupAnimationMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                page: 1,
                rdrawer: 0,
                loading: false,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                ddl: null,
                ddl_ref: null,
                show_popup: false,
                //PopupAnimationMixin
                transition_ms: 0,
                getPopupWidth: 710,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
        },
        methods: {
            refIdx() {
                return _.findIndex(this.ddl._references, {id: Number(this.ddl_ref.id)}) + 1;
            },
            //Load RefColors
            createLoadRefColors(behavior) {
                this.$root.sm_msg_type = 1;
                this.loading = true;
                axios.post('/ajax/ddl/reference/color/create-and-load', {
                    ddl_ref_id: this.ddl_ref.id,
                    behavior: behavior,
                    page: this.page,
                }).then(({ data }) => {
                    this.loading = false;
                    this.ddl_ref._reference_clr_img = data.colors;
                    this.ddl_ref._ref_clr_img_count = data.count;
                    this.rdrawer += 1;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Update DdlReference
            updateReferenceRow(key, is_bool) {
                if (is_bool) {
                    this.ddl_ref[key] = !this.ddl_ref[key] ? 1 : 0;
                }

                this.$root.sm_msg_type = 1;

                let row_id = this.ddl_ref.id;
                let fields = _.cloneDeep(this.ddl_ref);//copy object
                this.$root.deleteSystemFields(fields);
                RefCondHelper.setUses(this.tableMeta);

                axios.put('/ajax/ddl/reference', {
                    table_id: this.tableMeta.id,
                    ddl_id: this.ddl.id,
                    ddl_ref_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    this.ddl._references = data;
                    if (!is_bool) {//refill after changing 'color_field_id'/'image_field_id'
                        this.createLoadRefColors('changed_field');
                    }
                    this.rdrawer += 1;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Work with RefColors
            addRefColorRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/ddl/reference/color', {
                    ddl_ref_id: this.ddl_ref.id,
                    fields: fields,
                    page: this.page,
                }).then(({ data }) => {
                    this.ddl_ref._reference_clr_img = data.colors;
                    this.ddl_ref._ref_clr_img_count = data.count;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateRefColorRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/ddl/reference/color', {
                    ddl_ref_color_id: tableRow.id,
                    fields: fields,
                    page: this.page,
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteRefColorRow(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/ddl/reference/color', {
                    params: {
                        ddl_ref_color_id: tableRow.id,
                        page: this.page,
                    }
                }).then(({ data }) => {
                    this.ddl_ref._reference_clr_img = data.colors;
                    this.ddl_ref._ref_clr_img_count = data.count;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            changePage(page) {
                this.page = page;
                this.createLoadRefColors('first_create');
            },

            //Show/hide form
            hide() {
                this.show_popup = false;
                this.$root.tablesZidxDecrease();
                eventBus.$emit('reload-page', this.tableMeta.id);
            },
            showRefValueColorsPopup(ddl, ddl_ref, fields_from_condition) {
                this.ddl = ddl;
                this.ddl_ref = ddl_ref;
                this.fields_from_condition = fields_from_condition || [];

                this.show_popup = true;
                this.$root.tablesZidxIncrease();
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
                this.createLoadRefColors('first_create');
            },
        },
        mounted() {
            eventBus.$on('show-ref-value-colors', this.showRefValueColorsPopup);
        },
        beforeDestroy() {
            eventBus.$off('show-ref-value-colors', this.showRefValueColorsPopup);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup-wrapper {
        label {
            margin-bottom: 0;
        }
        .blue-gradient {
            margin-left: 10px;
        }
    }
</style>