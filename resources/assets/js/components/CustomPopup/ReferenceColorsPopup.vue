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
                            <button class="btn btn-sm btn-primary blue-gradient"
                                    :style="$root.themeButtonStyle"
                                    @click="createLoadRefColors('fill')"
                            >Auto</button>
                            <button class="btn btn-sm btn-primary blue-gradient extra-right"
                                    :style="$root.themeButtonStyle"
                                    @click="createLoadRefColors('clear')"
                            >Clear</button>
                        </div>
                    </div>
                </div>

                <div class="popup-content flex flex__elem-remain">
                    <div class="popup-main">
                        <custom-table
                            v-if="!loading && ddl_ref._reference_colors"
                            :cell_component_name="'custom-cell-settings-ddl'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['ddl_reference_colors']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="ddl_ref._reference_colors || []"
                            :rows-count="ddl_ref._reference_colors.length || 0"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :adding-row="addingRow"
                            :behavior="'settings_ddl_items'"
                            :user="$root.user"
                            :available-columns="['ref_value','color']"
                            :use_theme="true"
                            @added-row="addRefColorRow"
                            @updated-row="updateRefColorRow"
                            @delete-row="deleteRefColorRow"
                        ></custom-table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import {eventBus} from "../../app";

import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

import CustomTable from "../CustomTable/CustomTable";

export default {
        name: "ReferenceColorsPopup",
        components: {
            CustomTable,
        },
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
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
                getPopupWidth: 500,
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
                }).then(({ data }) => {
                    this.loading = false;
                    this.ddl_ref._reference_colors = data;
                    if (this.ddl_ref._reference_colors.length >= 100) {
                        Swal('', 'Reached maximum limit for options, first 100 elements are showed.');
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                    fields: fields
                }).then(({ data }) => {
                    this.ddl_ref._reference_colors = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteRefColorRow(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/ddl/reference/color', {
                    params: {
                        ddl_ref_color_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.ddl_ref._reference_colors = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Show/hide form
            hide() {
                this.show_popup = false;
                this.$root.tablesZidx -= 10;
                eventBus.$emit('reload-page');
            },
            showRefValueColorsPopup(ddl, ddl_ref) {
                this.ddl = ddl;
                this.ddl_ref = ddl_ref;

                this.show_popup = true;
                this.$root.tablesZidx += 10;
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
                this.createLoadRefColors('create');
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
        .popup-header {
            .blue-gradient {
                padding: 3px 7px;
                position: absolute;
                z-index: 100;
                right: 30px;
                font-size: 1.2rem;
            }
            .extra-right {
                right: 75px;
            }
        }
    }
</style>