<template>
    <div class="link-data" style="background-color: inherit;">
        <div class="link-menu flex">
            <button class="btn btn-default mr5" :class="{active: activeTab === 'outgo'}" :style="textSysStyle" @click="changeTab('outgo')">
                Outgoing
            </button>
            <button class="btn btn-default mr5" :class="{active: activeTab === 'incom'}" :style="textSysStyle" @click="changeTab('incom')">
                Incoming
            </button>

            <div style="position: absolute;right: 5px;">
                <info-sign-link v-show="activeTab === 'outgo' && activeLinkTab === 'list'"
                                class="ml5 mr5"
                                :app_sett_key="'help_link_settings_links'"
                                :hgt="30"
                                :txt="'for Settings/Links/List'"
                ></info-sign-link>
                <info-sign-link v-show="activeTab === 'outgo' && activeLinkTab === 'details'"
                                class="ml5 mr5"
                                :app_sett_key="'help_link_settings_links_details'"
                                :hgt="30"
                                :txt="'for Settings/Links/Details'"
                ></info-sign-link>
                <info-sign-link v-show="activeTab === 'outgo' && activeLinkTab === 'share'"
                                class="ml5 mr5"
                                :app_sett_key="'help_link_settings_links_share'"
                                :hgt="30"
                                :txt="'for Settings/Links/Share'"
                ></info-sign-link>
                <info-sign-link v-show="activeTab === 'outgo' && activeLinkTab === 'drilling'"
                                class="ml5 mr5"
                                :app_sett_key="'help_link_settings_links_drilling'"
                                :hgt="30"
                                :txt="'for Settings/Links/Drilling'"
                ></info-sign-link>
                <info-sign-link v-show="activeTab === 'incom'"
                                class="ml5 mr5"
                                :app_sett_key="'help_link_settings_links_incom'"
                                :hgt="30"
                                :txt="'for Settings/Links/Incoming'"
                ></info-sign-link>
            </div>
        </div>

        <!--Tab with link settings-->
        <div class="link-tab" v-show="activeTab === 'outgo'">
            <div class="full-height permissions-tab" :style="$root.themeMainBgStyle">
                <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">
                    <div class="permissions-menu-header m-5">
                        <button class="btn btn-default h36"
                                :style="textSysStyle"
                                :class="{active : activeLinkTab === 'list'}"
                                @click="activeLinkTab = 'list'"
                        >List</button>
                        <button class="btn btn-default h36"
                                :disabled="!linkRow"
                                :style="textSysStyle"
                                :class="{active : activeLinkTab === 'details'}"
                                @click="activeLinkTab = 'details'"
                        >Details</button>
                        <button class="btn btn-default h36"
                                :disabled="!linkRow || linkRow.link_type !== 'Record'"
                                :style="textSysStyle"
                                :class="{active : activeLinkTab === 'permis'}"
                                @click="activeLinkTab = 'permis'"
                        >Permissions</button>
                        <button v-show="linkRow && linkRow.link_type === 'Record'"
                                class="btn btn-default h36"
                                :style="textSysStyle"
                                :class="{active : activeLinkTab === 'share'}"
                                @click="activeLinkTab = 'share'"
                        >Share</button>
                        <button v-show="linkRow && linkRow.link_type === 'Record' && linkRow.table_ref_condition_id"
                                class="btn btn-default h36"
                                :style="textSysStyle"
                                :class="{active : activeLinkTab === 'drilling'}"
                                @click="activeLinkTab = 'drilling'"
                        >Drilling at Fields & Links</button>

                        <div class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">
                            <label v-if="linkRow" class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;width: 300px;" :style="textSysStyleSmart">
                                Loaded Link:&nbsp;
                                <select-block
                                    :options="linkOpts()"
                                    :sel_value="linkRow.id"
                                    :style="{ maxWidth:'200px', height:'32px', }"
                                    @option-select="linkChange"
                                ></select-block>
                            </label>
                        </div>
                    </div>
                    <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                        <div v-show="activeLinkTab === 'list'" class="permissions-panel full-height no-padding">
                            <custom-table
                                v-if="draw_table"
                                :cell_component_name="'custom-cell-display-links'"
                                :global-meta="tableMeta"
                                :table-meta="settingsMeta['table_field_links']"
                                :settings-meta="settingsMeta"
                                :all-rows="allLinks"
                                :rows-count="allLinks.length"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :adding-row="addingRow"
                                :user="user"
                                :behavior="'settings_display_links'"
                                :available-columns="availableLinks"
                                :use_theme="true"
                                :widths_div="2"
                                @added-row="addLink"
                                @updated-row="updateLink"
                                @delete-row="deleteLink"
                                @reorder-rows="rowsReordered"
                                @row-index-clicked="rowIndexClickedLink"
                                @show-add-ref-cond="showAddRefCond"
                                @show-add-ddl-option="showLinkedPermissions"
                            ></custom-table>
                        </div>

                        <div v-show="activeLinkTab === 'details'" class="permissions-panel full-height no-padding">
                            <div v-if="linkRow && linkRow.link_type === 'Record'" class="flex flex--col">
                                <div class="full-height relative" style="padding: 5px 0;">
                                    <vertical-table
                                        class="spaced-table__fix"
                                        :td="'custom-cell-display-links'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['table_field_links']"
                                        :settings-meta="settingsMeta"
                                        :table-row="linkRow"
                                        :user="user"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :available-columns="['table_ref_condition_id','tooltip','link_display','avail_addons']"
                                        :headers-changer="headersChang"
                                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                        @show-add-ref-cond="showAddRefCond"
                                        @updated-cell="updateLink"
                                        @show-def-val-popup="showLinkColumnPopup"
                                        @show-add-ddl-option="showLinkedPermissions"
                                    ></vertical-table>
                                </div>

                                <div class="full-height relative">
                                    <div class="permissions-menu-header no-margin" :style="$root.themeMainBgStyle" style="padding: 5px 0 0 5px;">
                                        <button class="btn btn-default btn-sm" :class="{active : subLinkTab === 'options'}" :style="textSysStyle" @click="setSub('options')">
                                            Options
                                        </button>
                                        <button class="btn btn-default btn-sm" :class="{active : subLinkTab === 'table'}" :style="textSysStyle" @click="setSub('table')">
                                            Table
                                        </button>
                                        <button class="btn btn-default btn-sm" :class="{active : subLinkTab === 'listing'}" :style="textSysStyle" @click="setSub('listing')">
                                            List
                                        </button>
                                        <button class="btn btn-default btn-sm" :class="{active : subLinkTab === 'board'}" :style="textSysStyle" @click="setSub('board')">
                                            Board
                                        </button>
                                        <button class="btn btn-default btn-sm" :class="{active : subLinkTab === 'inline'}" :style="textSysStyle" @click="setSub('inline')">
                                            In-line
                                        </button>
                                        <button class="btn btn-default btn-sm" :class="{active : subLinkTab === 'popup'}" :style="textSysStyle" @click="setSub('popup')">
                                            Pop-up
                                        </button>
                                    </div>
                                    <div class="permissions-menu-body"
                                         style="overflow: auto;"
                                         :style="{padding: subLinkTab === 'board' ? '5px' : '5px 0'}"
                                    >
                                        <board-setting-block
                                            v-if="subLinkTab === 'board'"
                                            :tb_meta="tableMeta"
                                            :board_settings="linkRow"
                                            :no_header="true"
                                            :with_theme="true"
                                            @val-changed="updateBoardLink"
                                        ></board-setting-block>
                                        <vertical-table
                                            v-else-if="subLinkTab"
                                            class="spaced-table__fix"
                                            :td="'custom-cell-display-links'"
                                            :global-meta="tableMeta"
                                            :table-meta="settingsMeta['table_field_links']"
                                            :settings-meta="settingsMeta"
                                            :table-row="linkRow"
                                            :user="user"
                                            :cell-height="1"
                                            :max-cell-rows="0"
                                            :available-columns="getSubLinkColumns()"
                                            :headers-changer="headersChang"
                                            :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                            @show-add-ref-cond="showAddRefCond"
                                            @updated-cell="updateLink"
                                            @show-def-val-popup="showLinkColumnPopup"
                                            @show-add-ddl-option="showLinkedPermissions"
                                        ></vertical-table>
                                    </div>
                                </div>
                            </div>

                            <div class="full-frame" v-if="linkRow && linkRow.link_type !== 'Record'" style="padding: 5px 0;">
                                <vertical-table
                                    class="spaced-table__fix"
                                    :td="'custom-cell-display-links'"
                                    :global-meta="tableMeta"
                                    :table-meta="settingsMeta['table_field_links']"
                                    :settings-meta="settingsMeta"
                                    :table-row="linkRow"
                                    :user="user"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :available-columns="availableLinkColumns"
                                    :headers-changer="headersChang"
                                    :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                    @show-add-ref-cond="showAddRefCond"
                                    @updated-cell="updateLink"
                                    @show-def-val-popup="showLinkColumnPopup"
                                    @show-add-ddl-option="showLinkedPermissions"
                                ></vertical-table>

                                <template v-if="linkRow && linkRow.link_type === 'App' && linkRowWithUrl(linkRow)">
                                    <button class="btn btn-success"
                                            :style="{marginLeft: '10px'}"
                                            @click="showLinkParams = !showLinkParams"
                                    >Calling / URL Parameters</button>
                                    <div v-show="showLinkParams" class="params-wrapper">
                                        <custom-table
                                            :cell_component_name="'custom-cell-display-links'"
                                            :global-meta="tableMeta"
                                            :table-meta="settingsMeta['table_field_link_params']"
                                            :all-rows="linkRow._params"
                                            :rows-count="linkRow._params.length"
                                            :cell-height="1"
                                            :max-cell-rows="0"
                                            :is-full-width="true"
                                            :behavior="'settings_display_links'"
                                            :user="user"
                                            :adding-row="addingRow"
                                            :use_theme="true"
                                            :no_width="true"
                                            @added-row="addLinkParam"
                                            @updated-row="updateLinkParam"
                                            @delete-row="deleteLinkParam"
                                        ></custom-table>
                                    </div>
                                </template>

                                <!-- ERI PARSER SETTINGS -->
                                <template v-if="linkRow && linkRow.link_type === 'App' && linkRow.table_app_id == $root.settingsMeta.eri_parser_app_id">
                                    <vertical-table
                                        class="spaced-table__fix"
                                        :td="'custom-cell-display-links'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['table_field_links']"
                                        :settings-meta="settingsMeta"
                                        :table-row="linkRow"
                                        :user="user"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :available-columns="['eri_parser_file_id', 'eri_remove_prev_records']"
                                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                        @updated-cell="updateLink"
                                    ></vertical-table>

                                    <div class="flex flex--space mb10 mr10">
                                        <div>
                                            <button class="btn btn-default h36"
                                                    :style="textSysStyle"
                                                    :class="{active : activeEriTab === 'parts'}"
                                                    @click="activeEriTab = 'parts'"
                                            >Parts</button>
                                            <button class="btn btn-default h36"
                                                    :disabled="!linkRow"
                                                    :style="textSysStyle"
                                                    :class="{active : activeEriTab === 'correspondence'}"
                                                    @click="activeEriTab = 'correspondence'"
                                            >Correspondence</button>
                                        </div>

                                        <div v-if="activeEriTab === 'parts'" class="flex flex--center-v">
                                            <button v-if="selectedEriPart"
                                                    class="btn btn-default btn-sm blue-gradient"
                                                    style="height: 32px"
                                                    :style="$root.themeButtonStyle"
                                                    @click="importVariables = true"
                                            >Import</button>
                                            <label class="flex flex--center" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                                                <span style="color: #444;">Loaded Part:&nbsp;</span>
                                                <select-block
                                                    :options="eriPrtOpts()"
                                                    :sel_value="selectedEriPart ? selectedEriPart.id : null"
                                                    :style="{ width:'150px', height:'32px', padding:'6px' }"
                                                    @option-select="eriPrtChange"
                                                ></select-block>
                                            </label>
                                        </div>
                                        <div v-if="activeEriTab === 'correspondence'">
                                            <label class="flex flex--center" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                                                <span style="color: #444;">Loaded Part:&nbsp;</span>
                                                <select-block
                                                    :options="eriTbOpts()"
                                                    :sel_value="selectedEriTable ? selectedEriTable.id : null"
                                                    :style="{ width:'150px', height:'32px', padding:'6px' }"
                                                    @option-select="eriTbChange"
                                                ></select-block>
                                            </label>
                                        </div>
                                    </div>

                                    <div v-show="activeEriTab === 'parts'" class="flex" style="max-height: calc(100% - 175px);">
                                        <div style="width: calc(50% - 5px)">
                                            <custom-table
                                                :cell_component_name="'custom-cell-display-links'"
                                                :global-meta="tableMeta"
                                                :table-meta="settingsMeta['table_field_link_eri_parts']"
                                                :all-rows="linkRow._eri_parts"
                                                :rows-count="linkRow._eri_parts.length"
                                                :cell-height="1.5"
                                                :max-cell-rows="0"
                                                :is-full-width="true"
                                                :selected-row="selectedEriPrt"
                                                :behavior="'settings_display_links'"
                                                :user="user"
                                                :adding-row="addingRow"
                                                :use_theme="true"
                                                @added-row="addLinkEriPart"
                                                @updated-row="updateLinkEriPart"
                                                @delete-row="deleteLinkEriPart"
                                                @reorder-rows="reloadLink"
                                                @row-index-clicked="(idx) => { selectedEriPrt = idx; }"
                                            ></custom-table>
                                        </div>
                                        <div style="margin: 0 3px; border-right: 3px solid #777;"></div>
                                        <div v-if="selectedEriPart" style="width: calc(50% - 5px)">
                                            <custom-table
                                                :cell_component_name="'custom-cell-display-links'"
                                                :global-meta="tableMeta"
                                                :table-meta="settingsMeta['table_field_link_eri_part_variables']"
                                                :all-rows="selectedEriPart._part_variables"
                                                :rows-count="selectedEriPart._part_variables.length"
                                                :parent-row="selectedEriPart"
                                                :cell-height="1"
                                                :max-cell-rows="0"
                                                :is-full-width="true"
                                                :behavior="'settings_display_links'"
                                                :user="user"
                                                :adding-row="addingRow"
                                                :use_theme="true"
                                                @added-row="addLinkEriPartVariable"
                                                @updated-row="updateLinkEriPartVariable"
                                                @delete-row="deleteLinkEriPartVariable"
                                                @reorder-rows="reloadLink"
                                            ></custom-table>
                                        </div>
                                    </div>

                                    <div v-show="activeEriTab === 'correspondence'" class="flex" style="max-height: calc(100% - 175px);">
                                        <div style="width: calc(40% - 5px)">
                                            <custom-table
                                                :cell_component_name="'custom-cell-display-links'"
                                                :global-meta="tableMeta"
                                                :table-meta="settingsMeta['table_field_link_eri_tables']"
                                                :all-rows="linkRow._eri_tables"
                                                :rows-count="linkRow._eri_tables.length"
                                                :foreign-special="linkRow"
                                                :cell-height="1"
                                                :max-cell-rows="0"
                                                :is-full-width="true"
                                                :selected-row="selectedEriTb"
                                                :behavior="'settings_display_links'"
                                                :user="user"
                                                :adding-row="addingRow"
                                                :use_theme="true"
                                                @added-row="addLinkEriTable"
                                                @updated-row="updateLinkEriTable"
                                                @delete-row="deleteLinkEriTable"
                                                @reorder-rows="reloadLink"
                                                @row-index-clicked="(idx) => { selectedEriTb = idx; }"
                                            ></custom-table>
                                        </div>
                                        <div style="margin: 0 3px; border-right: 3px solid #777;"></div>
                                        <div v-if="selectedEriTable" style="width: calc(60% - 5px)">
                                            <custom-table
                                                :cell_component_name="'custom-cell-display-links'"
                                                :global-meta="tableMeta"
                                                :table-meta="settingsMeta['table_field_link_eri_fields']"
                                                :all-rows="selectedEriTable._eri_fields"
                                                :rows-count="selectedEriTable._eri_fields.length"
                                                :parent-row="selectedEriTable"
                                                :foreign-special="linkRow"
                                                :cell-height="1"
                                                :max-cell-rows="0"
                                                :is-full-width="true"
                                                :behavior="'settings_display_links'"
                                                :user="user"
                                                :adding-row="addingRow"
                                                :use_theme="true"
                                                @added-row="addLinkEriField"
                                                @updated-row="updateLinkEriField"
                                                @delete-row="deleteLinkEriField"
                                                @reorder-rows="reloadLink"
                                                @show-def-val-popup="showEriConversionsPopup"
                                            ></custom-table>
                                        </div>
                                    </div>
                                </template>

                                <!-- ERI WRITER SETTINGS -->
                                <template v-if="linkRow && linkRow.link_type === 'App' && linkRow.table_app_id == $root.settingsMeta.eri_writer_app_id">
                                    <vertical-table
                                        class="spaced-table__fix"
                                        :td="'custom-cell-display-links'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['table_field_links']"
                                        :settings-meta="settingsMeta"
                                        :table-row="linkRow"
                                        :user="user"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :available-columns="['eri_writer_file_id','eri_parser_link_id','eri_writer_filename_fields','eri_writer_filename_year','eri_writer_filename_time']"
                                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                        @updated-cell="updateLink"
                                    ></vertical-table>
                                </template>

                                <!-- DA LOADING OCR/PDF/GEOMETRY PARSERS SETTINGS -->
                                <template v-if="linkRow && linkRow.link_type === 'App'
                                    && (linkRow.table_app_id == $root.settingsMeta.da_loading_app_id
                                        || linkRow.table_app_id == $root.settingsMeta.mto_dal_app_id
                                        || linkRow.table_app_id == $root.settingsMeta.mto_geom_app_id
                                        || linkRow.table_app_id == $root.settingsMeta.ai_extractm_app_id)"
                                >
                                    <vertical-table
                                        class="spaced-table__fix"
                                        :td="'custom-cell-display-links'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['table_field_links']"
                                        :settings-meta="settingsMeta"
                                        :table-row="linkRow"
                                        :user="user"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :available-columns="dalParserColumns()"
                                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                        @updated-cell="updateLink"
                                    ></vertical-table>
                                    <div class="flex ml10 mr10">
                                        <div style="width: 30%; font-weight: bold;" :style="textSysStyle">
                                            <span v-if="linkRow.table_app_id == $root.settingsMeta.ai_extractm_app_id">Settings</span>
                                            <span v-else>Column-field correspondence</span>
                                        </div>
                                        <div style="width: 70%; border-left: 1px solid #ccc;">
                                            <custom-table
                                                :cell_component_name="'custom-cell-display-links'"
                                                :global-meta="tableMeta"
                                                :table-meta="settingsMeta['table_field_link_da_loadings']"
                                                :all-rows="linkRow._link_app_correspondences"
                                                :rows-count="linkRow._link_app_correspondences.length"
                                                :parent-row="linkRow"
                                                :cell-height="1"
                                                :max-cell-rows="0"
                                                :is-full-width="true"
                                                :behavior="'settings_display_links'"
                                                :user="user"
                                                :adding-row="addingRow"
                                                :use_theme="true"
                                                :available-columns="['column_key','da_field_id','da_master_field_id','is_active']"
                                                :headers-changer="linkRow.table_app_id == $root.settingsMeta.ai_extractm_app_id
                                                    ? {'column_key': 'Data Identification', 'da_field_id': 'Field for Saving Output'}
                                                    : null"
                                                @added-row="addlinkAppCorr"
                                                @updated-row="updatelinkAppCorr"
                                                @delete-row="deletelinkAppCorr"
                                            ></custom-table>
                                        </div>
                                    </div>
                                </template>

                                <!-- SMART AUTO SELECT SETTINGS -->
                                <template v-if="linkRow && linkRow.link_type === 'App' && linkRow.table_app_id == $root.settingsMeta.smart_select_app_id">
                                    <vertical-table
                                        class="spaced-table__fix"
                                        :td="'custom-cell-display-links'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['table_field_links']"
                                        :settings-meta="settingsMeta"
                                        :table-row="linkRow"
                                        :user="user"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :available-columns="['smart_select_source_field_id','smart_select_target_field_id','smart_select_data_range']"
                                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                        @updated-cell="updateLink"
                                    ></vertical-table>
                                </template>
                            </div>
                        </div>

                        <div v-show="activeLinkTab === 'permis'" class="permissions-panel full-height">
                            <div class="full-frame" v-if="linkRow">
                                <vertical-table
                                    class="spaced-table__fix"
                                    :td="'custom-cell-display-links'"
                                    :global-meta="tableMeta"
                                    :table-meta="settingsMeta['table_field_links']"
                                    :settings-meta="settingsMeta"
                                    :table-row="linkRow"
                                    :user="user"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :available-columns="availableLinkPermisColumns"
                                    :headers-changer="headersChang"
                                    :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                    @show-add-ref-cond="showAddRefCond"
                                    @updated-cell="updateLink"
                                    @show-def-val-popup="showLinkColumnPopup"
                                    @show-add-ddl-option="showLinkedPermissions"
                                ></vertical-table>
                            </div>
                        </div>

                        <div v-show="activeLinkTab === 'share'" class="permissions-panel full-height">
                            <table-settings-mrv-filler
                                v-if="linkRow"
                                :table-meta="tableMeta"
                                :selected-link="linkRow"
                                @updated-row="updateLink"
                            ></table-settings-mrv-filler>
                        </div>

                        <div v-show="activeLinkTab === 'drilling'" class="permissions-panel full-height">
                            <div class="full-frame" v-if="linkRow">
                                <vertical-table
                                    class="spaced-table__fix"
                                    :td="'custom-cell-display-links'"
                                    :global-meta="tableMeta"
                                    :table-meta="settingsMeta['table_field_links']"
                                    :settings-meta="settingsMeta"
                                    :table-row="linkRow"
                                    :user="user"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :available-columns="['link_export_json_drill','link_export_drilled_fields']"
                                    :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                    @updated-cell="updateLink"
                                ></vertical-table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!--Summarize incoming link-->
        <div class="link-tab" v-show="activeTab === 'incom'">
            <div class="full-frame">
                <custom-table
                        v-if="sortApplied && tableMeta && incomLinks()"
                        :cell_component_name="'custom-cell-incoming-links'"
                        :global-meta="tableMeta"
                        :table-meta="settingsMeta['incoming_links']"
                        :settings-meta="settingsMeta"
                        :all-rows="incomLinks()"
                        :rows-count="incomLinks().length"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :is-full-width="true"
                        :user="user"
                        :available-columns="availableIncom"
                        :behavior="'incoming_links'"
                        :use_theme="true"
                        @updated-row="updateIncomLink"
                        @sort-by-field="sortByFieldFront"
                ></custom-table>
            </div>
        </div>

        <!-- Popups -->
        <field-link-columns-pop-up
            v-if="colPopLink"
            :link-row="colPopLink"
            @popup-close="colPopLink = null"
        ></field-link-columns-pop-up>

        <eri-field-conversions-pop-up
            v-if="eriFieldRow && eriTableRow"
            :table-meta="tableMeta"
            :eri-field-row="eriFieldRow"
            :eri-table-row="eriTableRow"
            @popup-close="eriFieldRow = null"
        ></eri-field-conversions-pop-up>

        <permissions-settings-popup
            v-if="linkedMeta"
            :table-meta="linkedMeta"
            :user="$root.user"
            :init_show="true"
            @hidden-form="linkedPermisClose()"
        ></permissions-settings-popup>

        <!--Import regular ddl options-->
        <ddl-paste-options-popup
            v-if="importVariables"
            @parse-options="parseImport"
            @popup-close="importVariables = false"
        ></ddl-paste-options-popup>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import TableSortMixin from '../../../../_Mixins/TableSortMixin';
    import DisplayLinksMixin from '../../../../_Mixins/DisplayLinksMixin';
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
    import IncomLinksMixin from "./IncomLinksMixin";

    import CustomTable from '../../../../CustomTable/CustomTable';
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import FieldLinkColumnsPopUp from "../../../../CustomPopup/FieldLinkColumnsPopUp";
    import TableSettingsMrvFiller from "./TableSettingsMrvFiller.vue";
    import PermissionsSettingsPopup from "../../../../CustomPopup/PermissionsSettingsPopup.vue";
    import DdlPasteOptionsPopup from "../../../../CustomPopup/DdlPasteOptionsPopup.vue";
    import EriFieldConversionsPopUp from "../../../../CustomPopup/EriFieldConversionsPopUp.vue";
    import BoardSettingBlock from "../../../../CommonBlocks/BoardSettingBlock.vue";

    export default {
        name: "TableSettingsDisplayLinks",
        mixins: [
            TableSortMixin,
            DisplayLinksMixin,
            CellStyleMixin,
            IncomLinksMixin,
        ],
        components: {
            BoardSettingBlock,
            EriFieldConversionsPopUp,
            DdlPasteOptionsPopup,
            PermissionsSettingsPopup,
            TableSettingsMrvFiller,
            FieldLinkColumnsPopUp,
            SelectBlock,
            InfoSignLink,
            CustomTable,
        },
        data: function () {
            return {
                importVariables: false,
                activeEriTab: 'parts',
                selectedEriTb: -1,
                selectedEriPrt: -1,
                availableColumns: ['name'],
                linkedMeta: null,
                colPopLink: null,
                eriFieldRow: null,
                eriTableRow: null,
                showLinkParams: false,
                availableIncom: ['incoming_allow', 'user_id', 'table_name', 'ref_cond_name', 'use_category', 'use_name'],
            }
        },
        computed: {
            selectedEriPart() {
                return this.linkRow._eri_parts[this.selectedEriPrt];
            },
            selectedEriTable() {
                return this.linkRow._eri_tables[this.selectedEriTb];
            },
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            table_id: Number|null,
            user: Object,
            foreign_link_id: Number,
            filter_id: Number,
        },
        watch: {
            table_id(val) {
                this.clearIncom();
                this.changeTab('outgo');
            },
        },
        methods: {
            eriTbOpts() {
                return _.map(this.linkRow._eri_tables, (smp) => {
                    let prt = _.find(this.linkRow._eri_parts, {id: Number(smp.eri_part_id)});
                    return {
                        val: smp.id,
                        show: prt ? prt.part : smp.id,
                    };
                });
            },
            eriPrtOpts() {
                return _.map(this.linkRow._eri_parts, (smp) => {
                    return {
                        val: smp.id,
                        show: smp.part,
                    };
                });
            },
            eriPrtChange(opt) {
                this.selectedEriPrt = _.findIndex(this.linkRow._eri_parts, {id: Number(opt.val)});
            },
            eriTbChange(opt) {
                this.selectedEriTb = _.findIndex(this.linkRow._eri_tables, {id: Number(opt.val)});
            },
            sortByFieldFront(tableHeader, $dir, $sub) {
                this.sort = this.sortByFieldWrows(this.sort, tableHeader, $dir, $sub);
                this.sortRows();
            },
            sortRows() {
                if (this.filter_id > 0) {
                    return;
                }
                let sortFlds = _.map(this.sort, 'field');
                let sortDirections = _.map(this.sort, 'direction');
                this.tableMeta.__incoming_links = _.orderBy(this.tableMeta.__incoming_links, sortFlds, sortDirections);
                this.sortApplied = false;
                this.$nextTick(() => {
                    this.sortApplied = true;
                });
            },
            redrawTbs() {
                let tmp_col = this.selectedCol;
                let tmp_link = this.selectedLink;
                this.selectedCol = -1;
                this.selectedLink = -1;
                this.$nextTick(() => {
                    this.selectedCol = tmp_col;
                    this.selectedLink = tmp_link;
                });
            },
            showAddRefCond(refId) {
                eventBus.$emit('show-ref-conditions-popup', this.tableMeta.db_name, refId);
            },
            showLinkColumnPopup(lRow) {
                this.colPopLink = lRow;
            },
            showLinkedPermissions(refTbId) {
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data/get-headers', {
                    table_id: refTbId,
                    user_id: this.$root.user.id,
                }).then(({ data }) => {
                    this.linkedMeta = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            linkedPermisClose() {
                this.linkedMeta = null;
            },

            //Link Param Functions
            addLinkParam(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link/param', {
                    table_field_link_id: this.linkRow.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.linkRow._params.push(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateLinkParam(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link/param', {
                    table_field_link_param_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkParam(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/settings/data/link/param', {
                    params: {
                        table_field_link_param_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.linkRow._params, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.linkRow._params.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Link Eri Table Functions
            addLinkEriTable(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link/eri-table', {
                    table_field_link_id: this.linkRow.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.linkRow._eri_tables.push(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateLinkEriTable(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link/eri-table', {
                    link_eri_table_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkEriTable(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/settings/data/link/eri-table', {
                    params: {
                        link_eri_table_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.linkRow._eri_tables, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.linkRow._eri_tables.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Link Eri Field Functions
            addLinkEriField(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link/eri-field', {
                    link_eri_table_id: this.selectedEriTable.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.selectedEriTable._eri_fields.push(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateLinkEriField(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link/eri-field', {
                    link_eri_field_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkEriField(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/settings/data/link/eri-field', {
                    params: {
                        link_eri_field_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.selectedEriTable._eri_fields, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.selectedEriTable._eri_fields.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Link Eri Part Functions
            addLinkEriPart(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link/eri-part', {
                    table_field_link_id: this.linkRow.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.linkRow._eri_parts.push(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateLinkEriPart(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link/eri-part', {
                    link_eri_table_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkEriPart(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/settings/data/link/eri-part', {
                    params: {
                        link_eri_table_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.linkRow._eri_parts, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.linkRow._eri_parts.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Link Eri Part Variable Functions
            addLinkEriPartVariable(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link/eri-part-variable', {
                    link_eri_part_id: this.selectedEriPart.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.selectedEriPart._part_variables.push(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateLinkEriPartVariable(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link/eri-part-variable', {
                    link_eri_part_var_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkEriPartVariable(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/settings/data/link/eri-part-variable', {
                    params: {
                        link_eri_part_var_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.selectedEriPart._part_variables, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.selectedEriPart._part_variables.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Link DA Loading Functions
            addlinkAppCorr(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link/da-loading', {
                    table_link_id: this.linkRow.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.linkRow._link_app_correspondences.push(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updatelinkAppCorr(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link/da-loading', {
                    link_da_loading_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deletelinkAppCorr(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/settings/data/link/da-loading', {
                    params: {
                        link_da_loading_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.linkRow._link_app_correspondences, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.linkRow._link_app_correspondences.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            parseImport(parseImportOptions) {
                if (parseImportOptions && this.selectedEriPart) {
                    this.$root.sm_msg_type = 1;
                    axios.post('/ajax/settings/data/link/eri-part-variable/paste', {
                        link_eri_part_id: this.selectedEriPart.id,
                        variables: parseImportOptions,
                    }).then(({ data }) => {
                        this.selectedEriPart._part_variables = data;
                        this.importVariables = false;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            reloadLink() {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/settings/data/link/reload', {
                    table_link_id: this.linkRow.id,
                }).then(({ data }) => {
                    this.linkRow._eri_tables = data._eri_tables;
                    this.linkRow._eri_parts = data._eri_parts;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            //
            dalParserColumns() {
                if (this.linkRow.table_app_id == this.$root.settingsMeta.da_loading_app_id) {
                    return ['da_loading_type','da_loading_gemini_key_id','da_loading_image_field_id','da_loading_output_table_id','da_loading_remove_prev_rec'];
                }
                if (this.linkRow.table_app_id == this.$root.settingsMeta.mto_dal_app_id) {
                    return ['mto_dal_pdf_doc_field_id','mto_dal_pdf_output_table_id','mto_dal_pdf_remove_prev_rec'];
                }
                if (this.linkRow.table_app_id == this.$root.settingsMeta.mto_geom_app_id) {
                    return ['mto_geom_doc_field_id','mto_geom_output_table_id','mto_geom_remove_prev_rec'];
                }
                if (this.linkRow.table_app_id == this.$root.settingsMeta.ai_extractm_app_id) {
                    return ['ai_extract_doc_field_id','ai_extract_ai_id','ai_extract_output_table_id','ai_extract_remove_prev_rec'];
                }
                return [];
            },

            changeTab(tab) {
                this.activeTab = tab;
                if (this.activeTab === 'incom') {
                    this.loadIncomings();
                }
            },
            linkRowWithUrl(linkRow) {
                return linkRow.table_app_id != this.$root.settingsMeta.payment_app_id
                    && linkRow.table_app_id != this.$root.settingsMeta.json_import_app_id
                    && linkRow.table_app_id != this.$root.settingsMeta.json_export_app_id
                    && linkRow.table_app_id != this.$root.settingsMeta.eri_parser_app_id
                    && linkRow.table_app_id != this.$root.settingsMeta.eri_writer_app_id
                    && linkRow.table_app_id != this.$root.settingsMeta.da_loading_app_id
                    && linkRow.table_app_id != this.$root.settingsMeta.mto_dal_app_id
                    && linkRow.table_app_id != this.$root.settingsMeta.mto_geom_app_id
                    && linkRow.table_app_id != this.$root.settingsMeta.ai_extractm_app_id
                    && linkRow.table_app_id != this.$root.settingsMeta.smart_select_app_id;
            },
            showEriConversionsPopup(eriRow, parentRow) {
                this.eriFieldRow = eriRow;
                this.eriTableRow = parentRow;
            },
        },
        mounted() {
            if (this.foreign_link_id) {
                let link = _.find(this.allLinks, {id: Number(this.foreign_link_id)});
                this.rowIndexClickedLink(null, link);
            }

            eventBus.$on('event-create-field-link', this.eventCreateLink);
            eventBus.$on('event-update-field-link', this.eventUpdateLink);
            eventBus.$on('ref-conditions-popup-closed', this.redrawTbs);
        },
        beforeDestroy() {
            eventBus.$off('event-create-field-link', this.eventCreateLink);
            eventBus.$off('event-update-field-link', this.eventUpdateLink);
            eventBus.$off('ref-conditions-popup-closed', this.redrawTbs);
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabSettingsPermissions";

    .permissions-tab {
        padding: 5px;
    }
    .spaced-table__fix {
        margin: 0 10px;
        width: calc(100% - 20px);
    }
    .params-wrapper {
        margin: 5px;
    }

    .link-data {
        height: 100%;
        padding: 5px 5px 7px 5px;

        .link-menu {
            button {
                background-color: #CCC;
                outline: 0;
            }
            .active {
                background-color: #FFF;
            }
        }

        .link-tab {
            height: calc(100% - 30px);
            position: relative;
            top: -3px;
            border: 1px solid #CCC;
            border-radius: 4px;
        }
    }
    .btn-default {
        height: 36px;
    }
    .scrollable-eri-fields {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }
</style>