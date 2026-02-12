<template>
    <div class="popup-wrapper" v-show="show_this" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            <span>Conditional Formattings (CFs)</span>
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                            <button class="btn btn-default btn-sm blue-gradient"
                                    :style="$root.themeButtonStyle"
                                    style="font-size: 14px !important; padding: 0 3px; position:absolute; right: 25px; z-index: 100;"
                                    @click="showOverview"
                            >Overview</button>
                            <div style="position: absolute; right: 100px; z-index: 100;">
                                <info-sign-link v-if="settingsMeta.is_loaded"
                                                :app_sett_key="'help_link_cond_format_pop'"
                                                :hgt="24"
                                                :txt="'for Cond Formats'"
                                ></info-sign-link>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner">
                        <div class="flex flex--col full-height">

                            <div class="flex__elem-remain table-container">
                                <custom-table
                                        v-if="draw_table"
                                        :cell_component_name="'custom-cell-cond-format'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['cond_formats']"
                                        :all-rows="filteredCondFormats"
                                        :rows-count="filteredCondFormats.length"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :is-full-width="true"
                                        :user="$root.user"
                                        :behavior="'cond_format'"
                                        :adding-row="addingRow"
                                        :forbidden-columns="$root.systemFields"
                                        :use_theme="true"
                                        @added-row="addFormat"
                                        @updated-row="updateFormat"
                                        @delete-row="deleteFormat"
                                        @reorder-rows="rowsReordered"
                                ></custom-table>
                            </div>
                            <label class="red" style="padding-left: 5px;">
                                Note: Conditional Formattings (CFs) with smaller ids (#), usually at higher positions on the list, are on top of CFs with greater ids (#), at lower positions.
                            </label>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import CustomTable from '../CustomTable/CustomTable';
    import InfoSignLink from "../CustomTable/Specials/InfoSignLink.vue";

    export default {
        name: "ConditionalFormattingPopup",
        components: {
            InfoSignLink,
            CustomTable,
        },
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                show_this: false,

                draw_table: true,
                addingRow: {
                    active: this.tableMeta._is_owner || (this.tableMeta._current_right && this.tableMeta._current_right.can_create_condformat),
                    position: 'body_top'
                },
                //PopupAnimationMixin
                getPopupWidth: window.innerWidth*0.8,
                idx: 0,
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
        },
        computed: {
            filteredCondFormats() {
                return this.tableMeta._is_owner
                    ? this.tableMeta._cond_formats
                    : _.filter(this.tableMeta._cond_formats, (cf) => {
                        return !!cf._visible_shared;
                    });
            },
        },
        methods: {
            redrawTb() {
                this.draw_table = false;
                this.$nextTick(() => {
                    this.draw_table = true;
                });
            },
            addFormat(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                if (this.$root.user.id) {
                    this.$root.sm_msg_type = 1;
                    axios.post('/ajax/cond-format', {
                        table_id: this.tableMeta.id,
                        fields: fields
                    }).then(({ data }) => {
                        this.tableMeta._cond_formats.unshift(data);
                        eventBus.$emit('reload-page');
                        this.redrawTb();
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    tableRow.id = 't'+Math.floor(Math.random() * 1000);
                    this.tableMeta._cond_formats.push(tableRow);
                }
            },
            updateFormat(tableRow) {
                let cond_format_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                if (this.$root.user.id) {
                    this.$root.sm_msg_type = 1;
                    axios.put('/ajax/cond-format', {
                        cond_format_id: cond_format_id,
                        fields: fields
                    }).then(({ data }) => {
                        eventBus.$emit('reload-page');
                        this.redrawTb();
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            deleteFormat(tableRow) {
                if (this.$root.user.id) {
                    this.$root.sm_msg_type = 1;
                    axios.delete('/ajax/cond-format', {
                        params: {
                            cond_format_id: tableRow.id
                        }
                    }).then(({ data }) => {
                        let idx = _.findIndex(this.tableMeta._cond_formats, {id: tableRow.id});
                        if (idx > -1) {
                            this.tableMeta._cond_formats.splice(idx, 1);
                        }
                        this.redrawTb();
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let idx = _.findIndex(this.tableMeta._cond_formats, {id: tableRow.id});
                    if (idx > -1) {
                        this.tableMeta._cond_formats.splice(idx, 1);
                    }
                }
            },
            rowsReordered() {
                this.$root.sm_msg_type = 2;
                axios.get('/ajax/settings/load/cond-formats', {
                    params: { table_id: this.tableMeta.id }
                }).then(({ data }) => {
                    this.tableMeta._cond_formats = data;
                    this.redrawTb();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            hideMenu(e) {
                if (this.show_this && e.keyCode === 27 && this.$root.tablesZidx == this.zIdx) {
                    this.hide();
                }
            },
            hide() {
                this.show_this = false;
                this.$root.tablesZidxDecrease();
                this.$emit('popup-close');
            },
            showCondFormatsPopupHandler(db_table) {
                if (!db_table || db_table === this.tableMeta.db_name) {
                    this.show_this = true;
                    this.$root.tablesZidxIncrease();
                    this.zIdx = this.$root.tablesZidx;
                    this.runAnimation();
                }
            },
            showOverview() {
                eventBus.$emit('show-overview-format-popup', this.tableMeta.db_name);
            },
        },
        created() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('show-cond-format-popup', this.showCondFormatsPopupHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('show-cond-format-popup', this.showCondFormatsPopupHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";

    .table-container {
        height: 100%;
        padding: 5px;
        overflow: auto;
        border-right: 2px solid #AAA;
    }
</style>