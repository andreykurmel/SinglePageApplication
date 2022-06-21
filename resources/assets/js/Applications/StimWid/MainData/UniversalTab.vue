<template>
    <div class="universal--block" v-if="is_ready">
        <!--MASTERS-->
        <div v-for="tb in tab_object.tables"
             v-if="tb.table === tab_object.master_table && tb.type_tablda === 'vertical' && !$root.user.view_all"
             v-show="sel_tab === tb.horizontal_low && sel_sub_tab === tb.vertical_low"
             class="flex flex--center flex--automargin pull-right btn-wrap"
        >
            <button v-if="permis[tbkey(tb)].has_rl_calculator && modelUser"
                    :disabled="!permis[tbkey(tb)].can_add || !permis[tbkey(tb)].can_edit"
                    :style="$root.themeButtonStyle"
                    class="btn btn-success btn-top--icon blue-gradient"
                    @click="doRLCalculation(tb)"
                    title="Create RL Brackets"
            ><span class="btn-wrapper">RL</span></button>
            <show-hide-button v-if="vuex_fm[tb.table] && vuex_fm[tb.table].meta.params"
                              v-show="permis[tbkey(tb)].has_halfmoon"
                              :table-meta="vuex_fm[tb.table].meta.params"
                              :user="$root.user"
                              :only_columns="vuex_links[tb.table].avail_columns_for_app"
                              @show-changed="redrawTb"
            ></show-hide-button>
            <download-button v-if="vuex_fm[tb.table] && vuex_fm[tb.table].meta.params"
                             v-show="permis[tbkey(tb)].has_download"
                             :tb_id="'tablda_'+tab+'_'+tb.table"
                             :table-meta="vuex_fm[tb.table].meta.params"
                             :all-rows="vuex_fm[tb.table].rows"
                             :png_name="tab+'_'+select+'_'+sel_tab+'_'+sel_sub_tab+'_'+permis[tbkey(tb)].cur_page+'.png'"
            ></download-button>
            <button v-if="modelUser"
                    :disabled="!permis[tbkey(tb)].can_delete"
                    :style="$root.themeButtonStyle"
                    class="btn btn-danger btn-top--icon blue-gradient"
                    @click="preDeleteMaster()"
                    title="Delete"
            >
                <span class="btn-wrapper"><i class="fa fa-times"></i></span>
            </button>
            <button v-if="modelUser"
                    :disabled="!permis[tbkey(tb)].can_add"
                    :style="$root.themeButtonStyle"
                    class="btn btn-success btn-top--icon blue-gradient"
                    @click="copyMaster()"
                    title="Copy Master with Children"
            >
                <span class="btn-wrapper"><i class="fa fa-copy"></i></span>
            </button>
            <button v-if="!found_model._id"
                    :style="$root.themeButtonStyle"
                    :disabled="!permis[tbkey(tb)].can_add"
                    class="btn btn-success btn-top--icon blue-gradient"
                    @click="saveMaster()"
                    title="Save"
            >
                <span class="btn-wrapper"><i class="fa fa-save"></i></span>
            </button>
        </div>
        <!--MASTERS-->

        <!--BUTTONS (not masters)-->
        <div v-for="tb in tab_object.tables"
             v-if="tb.table !== tab_object.master_table && ['vertical','table'].indexOf(tb.type_tablda) > -1 && !$root.user.view_all"
             v-show="sel_tab === tb.horizontal_low && sel_sub_tab === tb.vertical_low"
             class="flex flex--center-v flex--automargin pull-right btn-wrap"
        >
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
                           :search-object="vuex_fm[tb.table].rows.for_search_block"
                           :limit-columns="vuex_links[tb.table].avail_columns_for_app"
                           @search-word-changed="emitSearchWordChanged(vuex_fm[tb.table].meta)"
            ></search-button>
            <show-hide-button v-if="vuex_fm[tb.table] && vuex_fm[tb.table].meta.params"
                              v-show="permis[tbkey(tb)].has_halfmoon"
                              :table-meta="vuex_fm[tb.table].meta.params"
                              :user="$root.user"
                              :only_columns="vuex_links[tb.table].avail_columns_for_app"
            ></show-hide-button>
            <download-button v-if="vuex_fm[tb.table] && vuex_fm[tb.table].meta.params"
                             v-show="tb.type_tablda === 'table' && permis[tbkey(tb)].has_download"
                             :tb_id="'tablda_'+tab+'_'+tb.table"
                             :table-meta="vuex_fm[tb.table].meta.params"
                             :all-rows="vuex_fm[tb.table].rows"
                             :png_name="tab+'_'+select+'_'+sel_tab+'_'+sel_sub_tab+'_'+permis[tbkey(tb)].cur_page+'.png'"
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
            ><span class="btn-wrapper"><i class="fas fa-arrows-alt"></i></span></button>
            <button v-show="tb.type_tablda === 'table' && permis[tbkey(tb)].has_parse_paste"
                    :style="$root.themeButtonStyle"
                    :disabled="!(modelUser && permis[tbkey(tb)].can_add)"
                    class="btn btn-success btn-top--icon blue-gradient"
                    @click="showParsePaste(tb)"
                    title="Paste to Import"
            ><span class="btn-wrapper"><i class="fas fa-file-import"></i></span></button>
            <button v-show="permis[tbkey(tb)].has_checked"
                    :style="$root.themeButtonStyle"
                    :disabled="!(modelUser && permis[tbkey(tb)].can_add)"
                    class="btn btn-success btn-top--icon blue-gradient"
                    @click="copyRowsClicked(tb)"
                    title="Copy Selected Rows"
            ><span class="btn-wrapper"><i class="fa fa-clone"></i></span></button>
            <add-button
                    :available="modelUser && permis[tbkey(tb)].can_add"
                    :adding-row="addingRows[tbkey(tb)]"
                    @add-clicked="insertinlineClicked(tb)"
                    class=""
            ></add-button>
        </div>
        <!--BUTTONS-->

        <!--HORIZONTALS-->
        <ul class="nav nav-tabs geometry_list">
            <li v-for="h_key in horizontal_keys"
                v-show="h_key !== '_hidden'"
                class="item"
                :class="{'active': sel_tab === h_key}"
                @click="mainTabClick(h_key)"
            >
                <a :class="{'sel_tab': sel_tab === h_key }" href="javascript:void(0)">{{ getHorizName(h_key) }}</a>
            </li>
        </ul>
        <!--HORIZONTALS-->

        <div class="tab-content">
            <!--HORIZONTAL TABS-->
            <div v-for="(vert_group, h_key) in hor_groups"
                 v-show="sel_tab === h_key && h_key !== '_hidden'"
                 :class="[sel_tab === h_key ? 'active' : '']"
                 class="tab-pane full-frame"
            >
                <div class="full-frame">
                    <!--VERTICALS-->
                    <ul v-if="vertical_keys[h_key] && vertical_keys[h_key][0]" class="nav nav-tabs rotate_navbar">
                        <li v-for="vert_k in vertical_keys[h_key]"
                            class="item"
                            :class="{'active': sel_sub_tab === vert_k}"
                            @click="subTabClick(vert_k)"
                        >
                            <a :class="{'sel_tab': sel_sub_tab === vert_k }" href="javascript:void(0)">{{ getVertName(h_key, vert_k) }}</a>
                        </li>
                    </ul>
                    <!--VERTICALS-->

                    <!--VERTICAL TABS-->
                    <div v-for="(tabls, vert_k) in vert_group"
                         v-show="sel_sub_tab === vert_k"
                         :class="{
                                'active': sel_sub_tab === vert_k,
                                'sub-tab': vertical_keys[h_key] && vertical_keys[h_key][0],
                            }"
                         class="full-frame"
                    >
                        <div v-for="tb in tabls"
                             v-if="tb.table"
                             class="flex flex--center-h"
                             style="overflow: auto;"
                             :style="{height: (100/tabls.length)+'%'}"
                        >
                            <tablda-table
                                    v-if="show_table && ['vertical','table','chart','map'].indexOf(tb.type_tablda) > -1 && vuex_links[tb.table]"
                                    :tb_id="'tablda_'+tab+'_'+tb.table"
                                    :is_showed="is_showed && sel_tab === h_key && sel_sub_tab === vert_k"
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
                                    :fill_attachments_handler_click="handlers[tbkey(tb)].fill_attachments_clicked"
                                    :rl_calculation_handler_click="handlers[tbkey(tb)].rl_calculation_clicked"
                                    :foreign_meta_table="vuex_fm[tb.table].meta"
                                    :foreign_all_rows="vuex_fm[tb.table].rows"
                                    :wrap_class="tb.type_tablda === 'vertical' ? 'full-width' : 'full-frame'"
                                    :style="{maxWidth: tb.type_tablda === 'vertical' ? '800px' : 'initial'}"
                                    @row-inserted="(data) => { tb.table === tab_object.master_table ? insertedMaster(data) : afterInsertRow(data) }"
                                    @row-updated="(data) => { tb.table === tab_object.master_table ? updatedMaster(data) : afterUpdateRow(data) }"
                                    @row-deleted="(data) => { tb.table === tab_object.master_table ? afterDeleteRow(data) : afterDeleteRow(data) }"
                                    @new-row-changed="(row) => { REDRAW_3D() }"
                                    @reload-3d="(row) => { REDRAW_3D() }"
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
                                    :is_showed="is_showed && sel_tab === h_key && sel_sub_tab === vert_k"
                                    class="full-frame"
                            ></configurator-component>
                        </div>
                    </div>
                    <!--VERTICAL TABS-->
                </div>
            </div>
            <!--HORIZONTAL TABS-->

        </div>

        <info-sign-link
                :app_sett_key="'stim_3d__'+tab_object.master_table+'_tab'"
                class="knowledge-center-button"
        ></info-sign-link>

        <pre-delete-popup
                v-if="pre_delete_master_popup"
                :master_str="tab_object.init_select || tab_object.init_top"
                :add_tables="del_additional_tbls"
                @popup-delete="deleteMaster()"
                @popup-close="pre_delete_master_popup = false"
        ></pre-delete-popup>
    </div>
</template>

<script>
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
    import PreDeletePopup from "./PreDeletePopup";
    import CellHeightButton from "../../../components/Buttons/CellHeightButton";
    import StringReplaceButton from "../../../components/Buttons/StringReplaceButton";

    export default {
        name: 'UniversalTab',
        mixins: [
            ModelWorkMixin,
            TabFuncMixin,
        ],
        components: {
            StringReplaceButton,
            CellHeightButton,
            SearchButton,
            PreDeletePopup,
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
                hor_groups: {}, // {h1: { v1: [table], v2: [table], ... }, ...}
                horizontal_keys: [], // ['h1','h2',...]
                vertical_keys: {}, // {h1: ['v1', 'v2', ...], h2: [], ...}

                sel_tab: '',
                sel_sub_tab: '',
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
        },
        methods: {
            getHorizName(h_key) {
                let group;
                for (let i in this.hor_groups[h_key]) {
                    group = this.hor_groups[h_key][i];
                    break;
                }
                return group && group[0] ? group[0].horizontal : '';
            },
            getVertName(h_key, vert_k) {
                let group = this.hor_groups[h_key][vert_k];
                return group && group[0] ? group[0].vertical : '';
            },
            mainTabClick(h_key) {
                this.sel_tab = h_key;
                this.sel_sub_tab = _.last(this.vertical_keys[this.sel_tab]);
            },
            subTabClick(vert_k) {
                this.sel_sub_tab = vert_k;
            },
        },
        mounted() {
            let shown_tbls = _.filter(this.tab_object.tables, (tb) => {
                return this.is_visible(tb) && this.no_hidden(tb);
            });
            this.hor_groups = {};
            let h_groups = _.groupBy(shown_tbls, 'horizontal_low');
            _.each(h_groups, (vert_group, h_key) => {
                this.hor_groups[h_key] = _.groupBy(vert_group, 'vertical_low');
            });

            let hidden_tbls = _.filter(this.tab_object.tables, (tb) => {
                return this.is_visible(tb) && !this.no_hidden(tb);
            });
            if (hidden_tbls && hidden_tbls.length) {
                this.hor_groups['_hidden'] = {
                    '_hidden': hidden_tbls,
                };
            }

            this.horizontal_keys = Object.keys( this.hor_groups );
            this.vertical_keys = {};
            _.each(this.horizontal_keys, (h_key) => {
                this.vertical_keys[h_key] = Object.keys( this.hor_groups[h_key] );
            });

            this.sel_tab = this.horizontal_keys[0];
            this.sel_sub_tab = _.last(this.vertical_keys[this.sel_tab]);

            this.prepareTab();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "CommonStyles";
</style>