<template>
    <div class="full-height flex flex--col accordion--block">
        <div v-for="(tabls,a_key) in accordions"
             :class="(a_key === sel_tab ? 'flex__elem-remain flex flex--col' : '')"
        >
            <!--HEADERS-->
            <a @click.prevent="accordClick(a_key)" class="btn-filter">
                <div class="flex flex--center-v flex--space">
                    <span>{{ getAccordName(a_key) }}</span>
                    <div v-if="a_key !== sel_tab" style="white-space: nowrap">
                        <span class="state-shower">{{ a_key === sel_tab ? '-' : '+' }}</span>
                    </div>

                    <div v-else="">
                        <div v-for="tb in tabls"
                             v-if="tb.table"
                             class="flex flex--center full-height flex--automargin"
                        >
                            <!--MASTERS-->
                            <template v-if="tb.table === tab_object.master_table && tb.type_tablda === 'vertical' && !$root.user.view_all">
                                <button v-if="permis[tbkey(tb)].has_rl_calculator && modelUser"
                                        :disabled="!permis[tbkey(tb)].can_add || !permis[tbkey(tb)].can_edit"
                                        :style="$root.themeButtonStyle"
                                        class="btn btn-success btn-top--icon blue-gradient"
                                        @click="doRLCalculation(tab_object.master_table)"
                                        title="Create RL Brackets"
                                ><span class="btn-wrapper">RL</span></button>
                                <show-hide-button v-if="vuex_fm[tb.table] && vuex_fm[tb.table].meta.params"
                                                  v-show="permis[tbkey(tb)].has_halfmoon"
                                                  :table-meta="vuex_fm[tb.table].meta.params"
                                                  :user="$root.user"
                                                  :only_columns="vuex_links[tb.table].avail_cols_for_app"
                                                  @show-changed="redrawTb"
                                ></show-hide-button>
                                <download-button v-if="vuex_fm[tb.table] && vuex_fm[tb.table].meta.params"
                                                 v-show="permis[tbkey(tb)].has_download"
                                                 :tb_id="'tablda_'+tab+'_'+tb.table"
                                                 :table-meta="vuex_fm[tb.table].meta.params"
                                                 :all-rows="vuex_fm[tb.table].rows"
                                                 :png_name="tab+'_'+select+'_'+sel_tab+'_'+permis[tbkey(tb)].cur_page+'.png'"
                                ></download-button>
                                <button v-if="modelUser"
                                        :disabled="!permis[tbkey(tb)].can_delete"
                                        :style="$root.themeButtonStyle"
                                        class="btn btn-danger btn-top btn-top--icon blue-gradient"
                                        :class="[!found_model._id ? 'btn-top--offset-2' : 'btn-top--offset']"
                                        @click="preDeleteMaster()"
                                        title="Delete"
                                >
                                    <div class="btn-wrapper"><i class="fa fa-times"></i></div>
                                </button>
                                <button v-if="modelUser"
                                        :disabled="!permis[tbkey(tb)].can_add"
                                        :style="$root.themeButtonStyle"
                                        class="btn btn-success btn-top btn-top--icon blue-gradient"
                                        :class="[!found_model._id ? 'btn-top--offset' : '']"
                                        @click="copyMaster()"
                                        title="Copy Master with Children"
                                >
                                    <div class="btn-wrapper"><i class="fa fa-copy"></i></div>
                                </button>
                                <button v-if="!found_model._id"
                                        :style="$root.themeButtonStyle"
                                        :disabled="!permis[tbkey(tb)].can_add"
                                        class="btn btn-success btn-top btn-top--icon blue-gradient"
                                        @click="saveMaster()"
                                        title="Save"
                                >
                                    <div class="btn-wrapper"><i class="fa fa-save"></i></div>
                                </button>
                            </template>
                            <!--MASTERS-->

                            <!--BUTTONS (not masters)-->
                            <template v-if="tb.table !== tab_object.master_table && ['vertical','table'].indexOf(tb.type_tablda) > -1 && !$root.user.view_all">
                                <button v-if="modelUser && permis[tbkey(tb)].can_add && permis[tbkey(tb)].has_copy_childrene"
                                        :style="$root.themeButtonStyle"
                                        class="btn btn-success btn-top--icon blue-gradient"
                                        @click="copyFromModelClicked(tb)"
                                        title="Copy From Another Model"
                                >
                                    <span class="btn-wrapper">CoFr</span>
                                </button>
                                <button v-if="permis[tbkey(tb)].has_rl_calculator && modelUser"
                                        :disabled="!permis[tbkey(tb)].can_add || !permis[tbkey(tb)].can_edit"
                                        :style="$root.themeButtonStyle"
                                        class="btn btn-success btn-top--icon blue-gradient"
                                        @click="doRLCalculation(tb)"
                                        title="Create RL Brackets"
                                ><span class="btn-wrapper">RL</span></button>
                                <button v-show="tb.type_tablda === 'table' && permis[tbkey(tb)].has_fill_attachments && vuex_fm[tb.table].meta.params"
                                        :style="$root.themeButtonStyle"
                                        class="btn btn-success btn-top--icon blue-gradient"
                                        @click="fillEqptAttachments(tb)"
                                >
                                    <span>FA</span>
                                </button>
                                <button v-show="tb.type_tablda === 'table' && permis[tbkey(tb)].has_condformat && vuex_fm[tb.table].meta.params"
                                        :style="$root.themeButtonStyle"
                                        class="btn btn-success btn-top--icon blue-gradient"
                                        @click="showCondPopupClicked(tb)"
                                >
                                    <img src="/assets/img/conditional_formatting_small_1.png" width="25" height="25"/>
                                </button>
                                <button v-show="tb.type_tablda === 'table' && permis[tbkey(tb)].has_viewpop && vuex_fm[tb.table].meta.params"
                                        :style="$root.themeButtonStyle"
                                        style="padding: 5px 3px; font-size: 14px;"
                                        class="btn btn-success btn-top--icon blue-gradient"
                                        @click="showViewsPopupClicked(tb)"
                                >
                                    <span>Views</span>
                                </button>
                                <cell-height-button v-if="tb.type_tablda === 'table' && permis[tbkey(tb)].has_cellheight_btn && vuex_fm[tb.table].meta.params"
                                                    :table_meta="vuex_fm[tb.table].meta.params"
                                                    :cell-height="$root.cellHeight"
                                                    :max-cell-rows="$root.maxCellRows"
                                                    @change-cell-height="$root.changeCellHeight"
                                                    @change-max-cell-rows="$root.changeMaxCellRows"
                                ></cell-height-button>
                                <string-replace-button v-if="tb.type_tablda === 'table' && permis[tbkey(tb)].has_string_replace && vuex_fm[tb.table].meta.params"
                                                       :table_id="vuex_fm[tb.table].meta.params.id"
                                                       :table-meta="vuex_fm[tb.table].meta.params"
                                                       :request_params="vuex_fm[tb.table].rows.rowsRequest(true)"
                                ></string-replace-button>
                                <search-button v-if="tb.type_tablda === 'table' && permis[tbkey(tb)].has_search_block && vuex_fm[tb.table].meta.params"
                                               :table-meta="vuex_fm[tb.table].meta.params"
                                               :limit-columns="vuex_links[tb.table].avail_cols_for_app"
                                               @search-word-changed="(so) => { emitSearchWordChanged(vuex_fm[tb.table].meta, so); }"
                                ></search-button>
                                <show-hide-button v-if="vuex_fm[tb.table] && vuex_fm[tb.table].meta.params"
                                                  v-show="permis[tbkey(tb)].has_halfmoon"
                                                  :table-meta="vuex_fm[tb.table].meta.params"
                                                  :user="$root.user"
                                                  :only_columns="vuex_links[tb.table].avail_cols_for_app"
                                ></show-hide-button>
                                <download-button v-if="vuex_fm[tb.table] && vuex_fm[tb.table].meta.params"
                                                 v-show="tb.type_tablda === 'table' && permis[tbkey(tb)].has_download"
                                                 :tb_id="'tablda_'+tab+'_'+tb.table"
                                                 :table-meta="vuex_fm[tb.table].meta.params"
                                                 :all-rows="vuex_fm[tb.table].rows"
                                                 :png_name="tab+'_'+select+'_'+sel_tab+'_'+permis[tbkey(tb)].cur_page+'.png'"
                                ></download-button>
                                <button v-show="tb.type_tablda === 'table' && permis[tbkey(tb)].has_section_parse"
                                        :style="$root.themeButtonStyle"
                                        :disabled="!(modelUser && permis[tbkey(tb)].can_edit)"
                                        class="btn btn-success btn-top--icon blue-gradient"
                                        @click="parseSections(tb)"
                                        title="Parse Sections"
                                ><span class="btn-wrapper">P</span></button>
                                <button v-show="tb.type_tablda === 'table' && permis[tbkey(tb)].has_rts && permis[tbkey(tb)].has_checked"
                                        :style="$root.themeButtonStyle"
                                        :disabled="!(modelUser && permis[tbkey(tb)].can_edit)"
                                        class="btn btn-success btn-top--icon blue-gradient"
                                        @click="showRTS(tb)"
                                        title="Rotate, Translate, Scale"
                                ><div class="btn-wrapper"><i class="fas fa-arrows-alt"></i></div></button>
                                <button v-show="tb.type_tablda === 'table' && permis[tbkey(tb)].has_parse_paste"
                                        :style="$root.themeButtonStyle"
                                        :disabled="!(modelUser && permis[tbkey(tb)].can_add)"
                                        class="btn btn-success btn-top--icon blue-gradient"
                                        @click="showParsePaste(tb)"
                                        title="Paste to Import"
                                ><div class="btn-wrapper"><i class="fas fa-file-import"></i></div></button>
                                <button v-show="permis[tbkey(tb)].has_checked"
                                        :style="$root.themeButtonStyle"
                                        :disabled="!(modelUser && permis[tbkey(tb)].can_add)"
                                        class="btn btn-success btn-top btn-top--icon btn-top--offset-add blue-gradient"
                                        @click="copyRowsClicked(tb)"
                                        title="Copy Selected Rows"
                                ><div class="btn-wrapper"><i class="fa fa-clone"></i></div></button>
                                <add-button
                                        :available="modelUser && permis[tbkey(tb)].can_add"
                                        :adding-row="addingRows[tbkey(tb)]"
                                        @add-clicked="insertinlineClicked(tb)"
                                        class="btn-top"
                                ></add-button>
                            </template>
                            <!--BUTTONS-->

                            <info-sign-link :app_sett_key="'stim_3d__'+tab_object.master_table+'_tab'" :txt="'for Stim/'+tab_object.master_table"></info-sign-link>
                        </div>
                    </div>

                </div>
            </a>
            <!--CONTENT-->
            <div v-show="a_key === sel_tab" class="accordion--content full-height">
                <div v-for="tb in tabls"
                     v-if="tb.table"
                     class="flex flex--center-h"
                     :style="{height: (100/tabls.length)+'%'}"
                >
                    <tablda-table
                            v-if="show_table && ['vertical','table','chart'].indexOf(tb.type_tablda) > -1 && vuex_links[tb.table]"
                            :tb_id="'tablda_'+tab+'_'+tb.table"
                            :is_showed="is_showed && sel_tab === a_key"
                            :master_table="tb.table === tab_object.master_table"
                            :sel_tab="sel_tab"
                            :sel_sub_tab="sel_sub_tab"
                            :show_type="tb.type_tablda"
                            :stim_link_params="vuex_links[tb.table]"
                            :adding_row="addingRows[tbkey(tb)]"
                            :found_model="found_model"
                            :update_handler_click="handlers[tbkey(tb)].update_clicked"
                            :insert_handler_click="handlers[tbkey(tb)].insert_clicked"
                            :add_popup_handler_click="handlers[tbkey(tb)].popup_clicked"
                            :copy_rows_handler_click="handlers[tbkey(tb)].copy_rows_clicked"
                            :parse_paste_handler_click="handlers[tbkey(tb)].parse_paste_clicked"
                            :rts_popup_handler_click="handlers[tbkey(tb)].rts_popup_clicked"
                            :parse_sections_handler_click="handlers[tbkey(tb)].parse_sections_clicked"
                            :copy_from_model_handler_click="handlers[tbkey(tb)].copy_from_model_clicked"
                            :show_cond_popup_handler_click="handlers[tbkey(tb)].show_cond_popup_clicked"
                            :fill_attachments_handler_click="handlers[tbkey(tb)].fill_attachments_clicked"
                            :rl_calculation_handler_click="handlers[tbkey(tb)].rl_calculation_clicked"
                            :foreign_meta_table="vuex_fm[tb.table].meta"
                            :foreign_all_rows="vuex_fm[tb.table].rows"
                            :style="{maxWidth: tb.type_tablda === 'vertical' ? '800px' : 'initial'}"
                            @row-inserted="(data) => { tb.table === tab_object.master_table ? insertedMaster(data) : afterInsertRow(data) }"
                            @row-updated="(data) => { tb.table === tab_object.master_table ? updatedMaster(data) : afterUpdateRow(data) }"
                            @row-deleted="(data) => { tb.table === tab_object.master_table ? afterDeleteRow(data) : afterDeleteRow(data) }"
                            @new-row-changed="(row) => { REDRAW_3D() }"
                            @reload-3d="(soft) => { REDRAW_3D(soft) }"
                            @meta-permissions="(p) => { setMetaPermis(p,tb) }"
                    ></tablda-table>
                    <attachments-block
                            v-if="show_table && tb.type_tablda === 'attachment' && vuex_fm[tb.table].meta && vuex_fm[tb.table].meta.is_loaded"
                            :table-meta="vuex_fm[tb.table].meta.params"
                            :table-row="vuex_fm[tb.table].rows.master_row || vuex_fm[tb.table].meta.empty_row"
                            :role="!vuex_fm[tb.table].rows.master_row ? 'add' : 'update'"
                            :behavior="'list_view'"
                            :user="$root.user"
                            :style="{padding: '5px'}"
                            class="full-frame"
                    ></attachments-block>
                    <configurator-component
                            v-if="show_table && tb.type_tablda === 'configurator'
                                && vuex_fm[tb.table].rows && vuex_fm[tb.table].rows.master_row"
                            :master_row="vuex_fm[tb.table].rows.master_row"
                            :selected_html="vuex_fm[tb.table].selected_html"
                            :master_table="tab_object.master_table"
                            :is_showed="is_showed && a_key === sel_tab"
                            class="full-frame"
                    ></configurator-component>
                </div>
            </div>
        </div>

        <deletew-children-popup
            v-if="pre_delete_master_popup && found_model.meta && found_model.rows"
            :master_table="found_model.meta.params"
            :request_params="found_model.rows.rowsRequest()"
            :direct_row="found_model.rows.master_row"
            @popup-close="pre_delete_master_popup = false"
            @after-delete="afterDeleteMaster"
        ></deletew-children-popup>

        <copyw-children-popup
            v-if="copy_master_popup && found_model.meta && found_model.rows"
            :master_table="found_model.meta.params"
            :request_params="found_model.rows.rowsRequest()"
            :direct_row="found_model.rows.master_row"
            @popup-close="copy_master_popup = false"
            @after-copy="copyMasterAfter"
        ></copyw-children-popup>
    </div>
</template>

<script>
    import {eventBus} from "../../../app";

    import {FoundModel} from '../../../classes/FoundModel';
    import {TabObject} from '../../../classes/TabObject';

    import TabFuncMixin from './TabFuncMixin.vue';
    import ModelWorkMixin from './ModelWorkMixin.vue';

    import InfoSignLink from "../../../components/CustomTable/Specials/InfoSignLink.vue";
    import AttachmentsBlock from "../../../components/CommonBlocks/AttachmentsBlock.vue";
    import TabldaTable from "./TabldaTable";
    import AddButton from "../../../components/Buttons/AddButton";
    import DownloadButton from "../../../components/Buttons/DownloadButton";
    import ShowHideButton from "../../../components/Buttons/ShowHideButton";
    import ConfiguratorComponent from "../Configurator/ConfiguratorComponent";
    import SearchButton from "../../../components/Buttons/SearchButton";
    import CellHeightButton from "../../../components/Buttons/CellHeightButton";
    import StringReplaceButton from "../../../components/Buttons/StringReplaceButton";
    import DeletewChildrenPopup from "../../../components/CustomPopup/Inheritance/DeletewChildrenPopup";
    import CopywChildrenPopup from "../../../components/CustomPopup/Inheritance/CopywChildrenPopup";

    export default {
        name: 'AccordionTab',
        mixins: [
            ModelWorkMixin,
            TabFuncMixin,
        ],
        components: {
            CopywChildrenPopup,
            DeletewChildrenPopup,
            StringReplaceButton,
            CellHeightButton,
            SearchButton,
            ConfiguratorComponent,
            ShowHideButton,
            DownloadButton,
            AddButton,
            TabldaTable,
            AttachmentsBlock,
            InfoSignLink,
        },
        data() {
            return {
                accordions: {}, // { a1: [table], a2: [table], ... }
                sel_tab: '',
            }
        },
        computed: {
        },
        props: {
            tab: String,
            select: String,
            is_showed: Boolean,
            found_model: FoundModel,
            tab_object: TabObject,
        },
        watch: {
            'found_model._id': {
                handler(val) {
                    this.handleHideShowTables();
                },
            }
        },
        methods: {
            getAccordName(a_key) {
                let group = this.accordions[a_key];
                return group && group[0] ? group[0].accordion : '';
            },
            accordClick(a_key) {
                this.sel_tab = this.sel_tab === a_key ? '' : a_key;
            },
            buildTabGroups() {
                let shown_tbls = this.getVisibleTables();
                this.accordions = _.groupBy(shown_tbls, 'accordion_low');
                this.elements_length = _.keys(this.accordions).length;
            },
        },
        mounted() {
            this.fillHideShowTables();
            this.prepareTab();
            this.handleHideShowTables();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "CommonStyles";
</style>