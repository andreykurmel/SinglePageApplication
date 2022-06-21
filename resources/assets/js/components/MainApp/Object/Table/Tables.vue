<template>
    <div id="tables" class="full-width" :style="$root.themeMainBgStyle">
        <nav class="navbar navbar-default" role="navigation">
            <div class="container-fluid nav-inner" :style="$root.themeRibbonStyle">
                <ul class="nav flex flex--center flex--automargin pull-left">
                    <div v-if="!$root.sideIsNa('side_left_menu') || !$root.sideIsNa('side_left_filter')">
                        <a @click.prevent="showTree()">
                            <span class="glyphicon" :class="[ $root.isLeftMenu ? 'glyphicon-triangle-left': 'glyphicon-triangle-right']"></span>
                        </a>
                    </div>

                    <template v-if="tableMeta">
                        <div :class="{active: currentTab === 'tab-data'}" v-if="canSeeTabData && (!viewAvails || inArray('tab-data', viewAvails))">
                            <a @click.prevent="showComponent('tab-data')" :style="textSysStyle">Data</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-settings'}" v-if="$root.user.id && (!viewAvails || inArray('tab-settings', viewAvails))">
                            <a @click.prevent="showComponent('tab-settings')" :style="textSysStyle">Settings</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-list-view'}" v-if="(!viewAvails || inArray('tab-list-view', viewAvails))">
                            <a @click.prevent="showComponent('tab-list-view')" :style="textSysStyle"><span class="fas fa-th" style="color: inherit"></span>&nbsp;Grid View</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-favorite'}" v-if="$root.user.id && (!viewAvails || inArray('tab-favorite', viewAvails))">
                            <a @click.prevent="showComponent('tab-favorite')" :style="textSysStyle"><span class="glyphicon glyphicon-star"></span>&nbsp;Favorite</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-kanban-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'kanban') && tableMeta.add_kanban && (!viewAvails || inArray('tab-kanban-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-kanban-add')" :style="textSysStyle"><i class="fab fa-trello"></i>&nbsp;Kanban</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-gantt-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'gantt') && tableMeta.add_gantt && (!viewAvails || inArray('tab-gantt-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-gantt-add')" :style="textSysStyle"><i class="fas fa-chart-bar"></i>&nbsp;Gantt</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-dcr-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'request') && tableMeta.add_request && (!viewAvails || inArray('tab-dcr-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-dcr-add')" :style="textSysStyle"><i class="far fa-calendar-check"></i>&nbsp;DCR</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-map-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'map') && tableMeta.add_map && (!viewAvails || inArray('tab-map-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-map-add')" :style="textSysStyle"><span class="glyphicon glyphicon-map-marker"></span>&nbsp;GSI</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-bi-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'bi') && tableMeta.add_bi && (!viewAvails || inArray('tab-bi-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-bi-add')" :style="textSysStyle"><span class="glyphicon glyphicon-equalizer"></span>&nbsp;BI</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-alert-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'alert') && tableMeta.add_alert && (!viewAvails || inArray('tab-alert-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-alert-add')" :style="textSysStyle"><img src="/assets/img/bell.png" width="15" height="15" style="filter: grayscale(1);"/>&nbsp;ANA</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-email-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'email') && tableMeta.add_email && (!viewAvails || inArray('tab-email-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-email-add')" :style="textSysStyle"><i class="far fa-envelope"></i>&nbsp;Email</a>
                        </div>
                        <div :class="{active: currentTab === 'tab-calendar-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'calendar') && tableMeta.add_calendar && (!viewAvails || inArray('tab-calendar-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-calendar-add')" :style="textSysStyle"><i class="far fa-calendar"></i>&nbsp;Calendar</a>
                        </div>
                    </template>
                </ul>
                <div v-if="table_id && tableMeta && settingsMeta" class="nav flex flex--center flex--automargin pull-right">
                    <div v-if="tableMeta._is_owner" v-show="currentTab === 'tab-gantt-add'" class="flex flex--center">
                        <i class="glyphicon glyphicon-cog" @click="gantt_sett_click++" style="font-size: 2em;cursor: pointer;"></i>
                    </div>
                    <div v-if="tableMeta._is_owner" v-show="currentTab === 'tab-gantt-add'">
                        <button class="btn btn-primary btn-sm blue-gradient mini-add" @click="gantt_add_click++" :style="$root.themeButtonStyle">Add</button>
                    </div>

                    <div v-if="tableMeta._is_owner" v-show="currentTab === 'tab-calendar-add'" style="height: 30px;">
                        <i class="glyphicon glyphicon-cog" @click="calendar_sett_click++" style="font-size: 2em;cursor: pointer;margin-top: 3px;"></i>
                    </div>
                    <div v-if="$root.AddonAvailableToUser(tableMeta, 'calendar', 'edit')" v-show="currentTab === 'tab-calendar-add'">
                        <button class="btn btn-primary btn-sm blue-gradient mini-add" @click="calendar_add_click++" style="margin-right: 3px;" :style="$root.themeButtonStyle">Add</button>
                    </div>

                    <div v-show="foundBiToUpdate && currentTab === 'tab-bi-add'">
                        <button class="btn btn-primary btn-sm blue-gradient mini-add" @click="biUpdateAll()" style="margin-right: 3px;" :style="$root.themeButtonStyle">Update All</button>
                    </div>
                    <div v-if="$root.AddonAvailableToUser(tableMeta, 'bi', 'edit') && biCanAdd()" v-show="currentTab === 'tab-bi-add'">
                        <button class="btn btn-primary btn-sm blue-gradient mini-add" @click="biAddWidget()" style="margin-right: 3px;" :style="$root.themeButtonStyle">Add</button>
                    </div>
                    <div v-if="$root.AddonAvailableToUser(tableMeta, 'bi', 'edit')" v-show="currentTab === 'tab-bi-add'" style="height: 30px;">
                        <bi-settings-button :table_id="tableMeta.id" :table-meta="tableMeta"></bi-settings-button>
                    </div>

                    <div v-show="currentTab === 'tab-list-view'">
                        <a>
                            <selected-rows-button
                                :table-meta="tableMeta"
                                :request_params="$root.request_params"
                                :all-rows="$root.listTableRows"
                                @update-meta-params="updateMetaParams"
                            ></selected-rows-button>
                        </a>
                    </div>
                    <div v-show="presentFormulaFields && inArray(currentTab, ['tab-list-view'])">
                        <button class="btn btn-default btn-sm btn--formula blue-gradient"
                                :style="$root.themeButtonStyle"
                                @click="recalcTableAll()"
                        >
                            <img src="/assets/img/formula_reload.png" width="30" height="30"/>
                        </button>
                        <formulas-calculating v-if="curr_recalc" :job_id="curr_recalc" class="static_calcs"></formulas-calculating>
                    </div>
                    <div v-show="showChangedListBtn && inArray(currentTab, ['tab-list-view'])">
                        <button class="btn btn-warning btn-sm pd35" :style="{fontWeight: 'bold'}" @click="collaboratorChangedList()">Reload</button>
                    </div>
                    <div v-show="showChangedFavBtn && inArray(currentTab, ['tab-favorite'])">
                        <button class="btn btn-warning btn-sm pd35" :style="{fontWeight: 'bold'}" @click="collaboratorChangedFav()">Reload</button>
                    </div>

                    <div v-if="tableMeta._is_owner || hasVisConds" v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite'])">
                        <a>
                            <span class="btn btn-default btn-sm--changed blue-gradient" @click="showCondForm()" :style="$root.themeButtonStyle">
                                <img src="/assets/img/conditional_formatting_small_1.png" width="25" height="25"/>
                            </span>
                        </a>
                    </div>
                    <div v-if="!$root.user.see_view" v-show="currentTab === 'tab-list-view'">
                        <a>
                            <span class="btn btn-primary btn-sm blue-gradient"
                                  @click="showViewsPopup()"
                                  style="padding: 5px 3px"
                                  :style="$root.themeButtonStyle">
                                <span>Views</span>
                            </span>
                        </a>
                    </div>
                    <div v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite'])" v-if="tableMeta">
                        <a><cell-height-button
                                :table_meta="tableMeta"
                                :cell-height="$root.cellHeight"
                                :max-cell-rows="$root.maxCellRows"
                                @change-cell-height="$root.changeCellHeight"
                                @change-max-cell-rows="$root.changeMaxCellRows"
                        ></cell-height-button></a>
                    </div>
                    <div v-if="!$root.user.see_view || canAdd" v-show="currentTab === 'tab-list-view'">
                        <a><add-button :available="canAdd" :adding-row="addingRow" @add-clicked="AddRowListView"></add-button></a>
                    </div>
                    <div v-if="tableMeta && canSomeEdit" v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite'])">
                        <a>
                            <button class="btn btn-primary btn-sm blue-gradient"
                                    :style="$root.themeButtonStyle"
                                    @click="show_dm_popup = true"
                            >DM</button>
                        </a>
                    </div>
                    <div v-if="tableMeta" v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite'])">
                        <a><search-button :table_id="tableMeta.id"
                                          :table-meta="tableMeta"
                                          :search-object="searchObject"
                                          :request-params="$root.request_params"
                                          @search-word-changed="searchWordChanged()"
                        ></search-button></a>
                    </div>
                    <div v-if="tableMeta" v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite'])">
                        <a><show-hide-button :table-meta="tableMeta" :user="$root.user" @show-changed="isShowedToggled"></show-hide-button></a>
                    </div>
                    <div v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite'])">
                        <a @click.prevent="$root.fullWidthCellToggle()"><full-width-button :full-width-cell="$root.fullWidthCell"></full-width-button></a>
                    </div>
                    <div v-if="$root.user.id && tableMeta"
                        v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite'])"
                    >
                        <a><download-button :tb_id="'table_list_view'"
                                            :table-meta="tableMeta"
                                            :search-object="searchObject"
                                            :list-rows="$root.listTableRows"
                        ></download-button></a>
                    </div>

                    <div v-show="currentTab === 'tab-favorite'" style="padding: 0 3px">
                        <info-sign-link :app_sett_key="'help_link_favorite_tab'"></info-sign-link>
                    </div>
                    <div v-show="currentTab === 'tab-dcr-add'" style="padding: 0 3px">
                        <info-sign-link :app_sett_key="'help_link_dcr_tab'"></info-sign-link>
                    </div>
                    <div v-show="currentTab === 'tab-map-add'" style="padding: 0 3px">
                        <info-sign-link :app_sett_key="'help_link_map_tab'"></info-sign-link>
                    </div>
                    <div v-show="currentTab === 'tab-bi-add'" style="padding: 0 3px">
                        <info-sign-link :app_sett_key="'help_link_bi_tab'"></info-sign-link>
                    </div>
                    <div v-show="currentTab === 'tab-kanban-add'" style="padding: 0 3px">
                        <info-sign-link :app_sett_key="'help_link_kanban_tab'"></info-sign-link>
                    </div>
                    <div v-show="currentTab === 'tab-gantt-add'" style="padding: 0 3px">
                        <info-sign-link :app_sett_key="'help_link_gantt_tab'"></info-sign-link>
                    </div>
                    <div v-show="currentTab === 'tab-email-add'" style="padding: 0 3px">
                        <info-sign-link :app_sett_key="'help_link_email_tab'"></info-sign-link>
                    </div>
                    <div v-show="currentTab === 'tab-calendar-add'" style="padding: 0 3px">
                        <info-sign-link :app_sett_key="'help_link_calendar_tab'"></info-sign-link>
                    </div>

                    <div v-if="!$root.sideIsNa('side_right')">
                        <a @click.prevent="showNote()"><span class="glyphicon" :class="[ $root.isRightMenu ? 'glyphicon-triangle-right': 'glyphicon-triangle-left']"></span></a>
                    </div>
                </div>
            </div>
        </nav>
        <div class="tabs-wrapper" v-if="settingsMeta && !temp_hide" :style="$root.themeMainBgStyle">

            <tab-data
                v-show="currentTab === 'tab-data'"
                v-if="canSeeTabData && tableMeta && (!viewAvails || inArray('tab-data', viewAvails)) && currentTab === 'tab-data'"
                :table_id="tableMeta.id"
                :user="$root.user"
                :table-meta="tableMeta"
                :cell-height="1"
                :max-cell-rows="0"
                @import-finished="dataImportFinished"
            ></tab-data>

            <tab-settings
                v-show="currentTab === 'tab-settings'"
                v-if="tableMeta && (!viewAvails || inArray('tab-settings', viewAvails))"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :cell-height="1"
                :max-cell-rows="0"
                :settings-tab="settingsTab"
                :user="$root.user"
                :table_id="tableMeta.id"
                :is-visible="currentTab === 'tab-settings'"
            ></tab-settings>

            <tab-list-view
                v-show="currentTab === 'tab-list-view' && (!viewAvails || inArray('tab-list-view', viewAvails))"
                v-if="tableMeta"
                :table_id="tableMeta.id"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :search-object="searchObject"
                :cell-height="1"
                :max-cell-rows="0"
                :full-width-cell="$root.fullWidthCell"
                :is-pagination="isPagination"
                :user="$root.user"
                :adding-row="addingRow"
                :query-preset="queryPreset"
                :is-visible="currentTab === 'tab-list-view'"
                :has_filters_url_preset="Boolean(filters_url_preset && filters_url_preset.length)"
                @update-meta-params="updateMetaParams"
                @show-src-record="showSrcRecord"
                @show-add-ddl-option="showAddDDLOption"
            ></tab-list-view>

            <tab-favorite
                v-show="currentTab === 'tab-favorite'"
                v-if="tableMeta && (!viewAvails || inArray('tab-favorite', viewAvails)) && currentTab === 'tab-favorite'"
                :table_id="tableMeta.id"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :search-object="searchObject"
                :cell-height="1"
                :max-cell-rows="0"
                :full-width-cell="$root.fullWidthCell"
                :is-pagination="isPagination"
                :is-visible="currentTab === 'tab-favorite'"
                :user="$root.user"
                @update-meta-params="updateMetaParams"
                @show-add-ddl-option="showAddDDLOption"
            ></tab-favorite>

            <tab-settings-requests
                v-show="currentTab === 'tab-dcr-add'"
                v-if="tableMeta && tableMeta.add_request && $root.AddonAvailableToUser(tableMeta, 'request') && (!viewAvails || inArray('tab-dcr-add', viewAvails))"
                :table-meta="tableMeta"
                :cell-height="1"
                :max-cell-rows="0"
                :user="$root.user"
                :table_id="tableMeta.id"
                :is-visible="currentTab === 'tab-dcr-add'"
            ></tab-settings-requests>

            <tab-map-view
                v-show="currentTab === 'tab-map-add'"
                v-if="tableMeta && tableMeta.add_map && $root.AddonAvailableToUser(tableMeta, 'map') && (!viewAvails || inArray('tab-map-add', viewAvails))"
                :table_id="tableMeta.id"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :request_params="$root.request_params"
                :user="$root.user"
                :cell-height="1"
                :can-edit="$root.AddonAvailableToUser(tableMeta, 'map', 'edit')"
                :is-visible="currentTab === 'tab-map-add'"
                @show-src-record="showSrcRecord"
            ></tab-map-view>

            <tab-bi-view
                v-show="currentTab === 'tab-bi-add'"
                v-if="tableMeta && tableMeta.add_bi && $root.AddonAvailableToUser(tableMeta, 'bi') && (!viewAvails || inArray('tab-bi-add', viewAvails))"
                :table_id="tableMeta.id"
                :rows_count="tableMeta._view_rows_count"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :request_params="$root.request_params"
                :row_state_hash="$root.listTableRows_state"
                :user="$root.user"
                :can-create="$root.AddonAvailableToUser(tableMeta, 'bi', 'edit')"
                :is-visible="currentTab === 'tab-bi-add'"
                @show-src-record="showSrcRecord"
            ></tab-bi-view>

            <tab-alert-and-notif
                    v-show="currentTab === 'tab-alert-add'"
                    v-if="tableMeta && tableMeta.add_alert && $root.AddonAvailableToUser(tableMeta, 'alert') && (!viewAvails || inArray('tab-alert-add', viewAvails))"
                    :table_id="tableMeta.id"
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :user="$root.user"
            ></tab-alert-and-notif>

            <tab-kanban-view
                    v-show="currentTab === 'tab-kanban-add'"
                    v-if="tableMeta && tableMeta.add_kanban && $root.AddonAvailableToUser(tableMeta, 'kanban') && (!viewAvails || inArray('tab-kanban-add', viewAvails))"
                    :table_id="tableMeta.id"
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :user="$root.user"
                    @show-src-record="showSrcRecord"
            ></tab-kanban-view>

            <tab-gantt-view
                    v-show="currentTab === 'tab-gantt-add'"
                    v-if="tableMeta && tableMeta.add_gantt && $root.AddonAvailableToUser(tableMeta, 'gantt') && (!viewAvails || inArray('tab-gantt-add', viewAvails))"
                    :table_id="tableMeta.id"
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :user="$root.user"
                    :add_click="gantt_add_click"
                    :settings_click="gantt_sett_click"
                    @show-src-record="showSrcRecord"
            ></tab-gantt-view>

            <tab-email-view
                    v-show="currentTab === 'tab-email-add'"
                    v-if="tableMeta && tableMeta.add_email && $root.AddonAvailableToUser(tableMeta, 'email') && (!viewAvails || inArray('tab-email-add', viewAvails))"
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :user="$root.user"
            ></tab-email-view>

            <tab-calendar-view
                    v-show="currentTab === 'tab-calendar-add'"
                    v-if="tableMeta && tableMeta.add_calendar && $root.AddonAvailableToUser(tableMeta, 'calendar') && (!viewAvails || inArray('tab-calendar-add', viewAvails))"
                    :table-meta="tableMeta"
                    :table-rows="$root.listTableRows"
                    :is-visible="currentTab === 'tab-calendar-add'"
                    :settings_click="calendar_sett_click"
                    :add_click="calendar_add_click"
                    @show-src-record="showSrcRecord"
            ></tab-calendar-view>

        </div>

        <div v-if="settingsMeta" class="hidden-popup-wrapper">
            <!--Popup for showing very long datas-->
            <table-data-string-popup :max-cell-rows="0"></table-data-string-popup>

            <!--Add Select Option Popup-->
            <add-option-popup
                v-if="addOptionPopup.show"
                :table-header="addOptionPopup.tableHeader"
                :table-row="addOptionPopup.tableRow"
                :table-meta="tableMeta"
                :settings-meta="$root.settingsMeta"
                :user="$root.user"
                @hide="addOptionPopup.show = false"
                @show-src-record="showSrcRecord"
            ></add-option-popup>


            <!--Popup for add column to table on the fly-->
            <add-table-column-popup
                    v-if="tableMeta && tableMeta._is_owner"
                    :table-meta="tableMeta"
            ></add-table-column-popup>


            <!--Popup for add column to table on the fly-->
            <reference-colors-popup
                    v-if="tableMeta"
                    :table-meta="tableMeta"
            ></reference-colors-popup>


            <!--Popup for adding column links-->
            <vertical-display-links
                    v-if="tableMeta"
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :user="$root.user"
                    :table_id="tableMeta ? tableMeta.id : null"
            ></vertical-display-links>


            <!--Popup for assign permissions-->
            <permissions-settings-popup
                    v-if="tableMeta"
                    :table-meta="tableMeta"
                    :user="$root.user"
            ></permissions-settings-popup>


            <!--Popup for adding column links-->
            <ddl-settings-popup
                    v-if="tableMeta"
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :user="$root.user"
            ></ddl-settings-popup>


            <!--Popup for adding column links-->
            <grouping-settings-popup
                    v-if="tableMeta"
                    :table-meta="tableMeta"
                    :user="$root.user"
            ></grouping-settings-popup>


            <!--Popup for adding column links-->
            <display-link-settings-popup
                    v-if="tableMeta"
                    :table-meta="tableMeta"
                    :user="$root.user"
            ></display-link-settings-popup>


            <!--Popup for showing ref conditions -->
            <ref-conditions-popup
                    v-if="tableMeta"
                    :table-meta="tableMeta"
                    :user="$root.user"
                    :table_id="tableMeta ? tableMeta.id : null"
            ></ref-conditions-popup>


            <!--Popup for settings up Conditional Formatting for Table-->
            <conditional-formatting-popup
                    v-if="tableMeta"
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :cell-height="1"
                    :max-cell-rows="0"
                    @popup-close="reshowComponent()"
            ></conditional-formatting-popup>


            <!--Popup for adding column links-->
            <general-settings-popup
                    :table-meta="tableMeta"
            ></general-settings-popup>


            <!--Popup for settings up Conditional Formatting for Table-->
            <table-views-popup
                    v-if="tableMeta"
                    :table-meta="tableMeta"
            ></table-views-popup>


            <!--All Table Settings  -->
            <table-settings-all-popup
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :user="$root.user"
            ></table-settings-all-popup>


            <!--Popup for showing FolderCopyToOtherPopup-->
            <copy-folder-to-others-popup
                    v-if="show_copy_folder_popup"
                    :folder-meta="copy_folder_meta"
                    @popup-close="show_copy_folder_popup = false"
            ></copy-folder-to-others-popup>


            <!--Popup for showing FolderCopyToOtherPopup-->
            <data-modif-popup
                    v-if="show_dm_popup"
                    :table-meta="tableMeta"
                    @popup-close="show_dm_popup = false"
            ></data-modif-popup>


            <!--Record or Table selector for links-->
            <link-rort-modal></link-rort-modal>


            <!--Popup for settings up Column Settings for Table (also in Settings/Basics)-->
            <for-settings-pop-up
                    v-if="settingsMeta['table_fields'] && editDisplaySettingsRow"
                    :global-meta="tableMeta"
                    :table-meta="settingsMeta['table_fields']"
                    :settings-meta="settingsMeta"
                    :table-row="editDisplaySettingsRow"
                    :user="$root.user"
                    :cell-height="1"
                    :max-cell-rows="0"
                    @popup-update="updateDisplayRow"
                    @popup-close="editDisplaySettingsRow = null"
                    @another-row="anotherRowPopup"
            ></for-settings-pop-up>

            <!--Only For TableViews-->
            <table-view-filtering-popup
                    v-if="openedFilteringView"
                    :table-meta="tableMeta"
                    :table-view="$root.user.view_all"
                    @record-found="viewRecordFound"
            ></table-view-filtering-popup>

            <!--For ANR Automations-->
            <proceed-automation-popup
                    v-if="AnrPop"
                    :table-meta="tableMeta"
                    :table-alert="AnrPop"
                    @hide-popup="AnrPop = null"
            ></proceed-automation-popup>

            <!--Link Popups from ListView and MapView.-->
            <template v-for="(linkObj, idx) in linkPopups">
                <link-pop-up
                        v-if="linkObj.key === 'show'"
                        :idx="linkObj.index"
                        :global-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :user="$root.user"
                        :link="linkObj.link"
                        :meta-header="linkObj.header"
                        :meta-row="linkObj.row"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :popup-key="idx"
                        :no_animation="linkObj.behavior === 'map'"
                        :view_authorizer="{view_hash: $root.user.view_hash, is_folder_view: $root.user._is_folder_view}"
                        @show-src-record="showSrcRecord"
                        @link-popup-close="closeLinkPopup"
                ></link-pop-up>
            </template>

            <google-address-autocomplete v-if="had_address_fld"></google-address-autocomplete>
        </div>

    </div>
</template>

<script>
    import {eventBus} from '../../../../app';

    import {SpecialFuncs} from '../../../../classes/SpecialFuncs';
    import {ChartFunctions} from './ChartAddon/ChartFunctions';
    import {RefCondHelper} from "../../../../classes/helpers/RefCondHelper";

    import CanEditMixin from "../../../_Mixins/CanViewEditMixin";
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    import TabData from './TabData';
    import TabSettings from './SettingsModule/TabSettings';
    import TabListView from './TabListView';
    import TabFavorite from './TabFavorite';
    import TabMapView from './TabMapView';
    import TabBiView from './TabBiView';
    import TabSettingsRequests from './SettingsModule/TabSettingsRequests';
    import TabAlertAndNotif from "./TabAlertAndNotif";
    import TabKanbanView from "./TabKanbanView";
    import TabGanttView from "./TabGanttView";
    import TabEmailView from "./TabEmailView";
    import TabCalendarView from "./TabCalendarView";

    import FullWidthButton from './../../../Buttons/FullWidthButton';
    import CellHeightButton from './../../../Buttons/CellHeightButton';
    import SelectedRowsButton from "../../../Buttons/SelectedRowsButton";
    import EmbedButton from './../../../Buttons/EmbedButton';
    import AddButton from './../../../Buttons/AddButton';
    import SearchButton from './../../../Buttons/SearchButton';
    import ShowHideButton from './../../../Buttons/ShowHideButton';
    import DownloadButton from './../../../Buttons/DownloadButton';
    import C2M2Button from './../../../Buttons/C2M2Button';
    import ChartsPerRowButton from './../../../Buttons/ChartsPerRowButton';
    import DeleteDataButton from "../../../Buttons/DeleteDataButton";
    import StringReplaceButton from "../../../Buttons/StringReplaceButton";

    import FormulasCalculating from './../../Object/Folder/FormulasCalculating';
    import VerticalDisplayLinks from '../../../CustomPopup/VerticalDisplayLinks';
    import LinkRortModal from "../../../CustomCell/InCell/LinkRortModal";
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
    import GoogleAddressAutocomplete from "../../../CommonBlocks/GoogleAddressAutocomplete";

    import ConditionalFormattingPopup from "../../../CustomPopup/CondFormatsPopup";
    import TableDataStringPopup from '../../../CustomPopup/TableDataStringPopup';
    import TableViewsPopup from "../../../CustomPopup/TableViewsPopup";
    import RefConditionsPopup from '../../../CustomPopup/RefConditionsPopup';
    import CopyFolderToOthersPopup from "../../../CustomPopup/CopyFolderToOthersPopup";
    import ForSettingsPopUp from "../../../CustomPopup/ForSettingsPopUp";
    import LinkPopUp from "../../../CustomPopup/LinkPopUp";
    import GroupingSettingsPopup from "../../../CustomPopup/GroupingSettingsPopup";
    import DdlSettingsPopup from "../../../CustomPopup/DdlSettingsPopup";
    import PermissionsSettingsPopup from "../../../CustomPopup/PermissionsSettingsPopup";
    import TableSettingsAllPopup from "../../../CustomPopup/TableSettingsAllPopup";
    import GeneralSettingsPopup from "../../../CustomPopup/GeneralSettingsPopup";
    import BiSettingsButton from "../../../Buttons/BiSettingsButton";
    import TableViewFilteringPopup from "../../../CustomPopup/TableViewFilteringPopup";
    import BackupSettingsPopup from "../../../CustomPopup/BackupSettingsPopup";
    import ProceedAutomationPopup from "../../../CustomPopup/ProceedAutomationPopup";
    import DisplayLinkSettingsPopup from "../../../CustomPopup/DisplayLinkSettingsPopup";
    import AddTableColumnPopup from "../../../CustomPopup/AddTableColumnPopup";
    import AddOptionPopup from "../../../CustomPopup/AddOptionPopup";
    import ReferenceColorsPopup from "../../../CustomPopup/ReferenceColorsPopup";
    import DataModifPopup from "../../../CustomPopup/DataModifPopup";

    export default {
        name: "Tables",
        mixins: [
            CanEditMixin,
            CellStyleMixin,
        ],
        components: {
            DataModifPopup,
            ReferenceColorsPopup,
            AddOptionPopup,
            AddTableColumnPopup,
            TabCalendarView,
            TabEmailView,
            TabGanttView,
            TabData,
            TabSettings,
            TabListView,
            TabFavorite,
            TabMapView,
            TabBiView,
            TabKanbanView,
            TabAlertAndNotif,
            TabSettingsRequests,

            BiSettingsButton,
            DeleteDataButton,
            C2M2Button,
            FullWidthButton,
            CellHeightButton,
            SelectedRowsButton,
            AddButton,
            SearchButton,
            ShowHideButton,
            DownloadButton,
            ChartsPerRowButton,
            EmbedButton,
            StringReplaceButton,

            LinkRortModal,
            FormulasCalculating,
            VerticalDisplayLinks,
            InfoSignLink,
            GoogleAddressAutocomplete,

            DisplayLinkSettingsPopup,
            ProceedAutomationPopup,
            TableViewsPopup,
            ConditionalFormattingPopup,
            CopyFolderToOthersPopup,
            TableDataStringPopup,
            RefConditionsPopup,
            ForSettingsPopUp,
            LinkPopUp,
            DdlSettingsPopup,
            GroupingSettingsPopup,
            PermissionsSettingsPopup,
            TableSettingsAllPopup,
            GeneralSettingsPopup,
            BackupSettingsPopup,
            TableViewFilteringPopup,
        },
        data: function () {
            return {
                addOptionPopup: {
                    show: false,
                    tableHeader: null,
                    tableRow: null,
                },
                AnrPop: null,
                temp_hide: false,
                last_tb_google_api: null,
                editDisplaySettingsRow: null,
                viewAvails: null,
                currentTab: null,
                tableMeta: null,
                addingRow: {
                    active: false,
                    position: 'top'
                },
                searchObject: {
                    keyWords: this.setKeyWords(),
                    columns: [],
                    direct_row_id: null,
                },
                settingsTab: {key: 'basics'},
                allRowsForAddons: [],

                show_dm_popup: false,
                show_copy_folder_popup: false,
                copy_folder_meta: null,

                queryPreset: {},
                filters_url_preset: this.filters_preset,

                showChangedListBtn: false,
                showChangedFavBtn: false,

                grouping_settings_show: false,
                alerts_popup_show: false,

                linkPopups: [],
                curr_recalc: this.recalc_id,

                had_address_fld: false,

                gantt_add_click: 0,
                gantt_sett_click: 0,

                calendar_sett_click: 0,
                calendar_add_click: 0,
                table_id: this.table_init_id,
            }
        },
        props: {
            settingsMeta: Object,
            isPagination: Boolean,
            table_init_id: Number|null,
            filters_preset: Array,
            recalc_id: String
        },
        computed: {
            canSeeTabData() {
                return this.$root.user.id
                    && this.tableMeta
                    && !this.tableMeta.is_system
                    && (
                        this.tableMeta._is_owner
                        ||
                        (this.tableMeta._current_right && this.tableMeta._current_right.can_add && this.tableMeta._current_right.can_see_datatab)
                    )
            },
            presentFormulaFields() {
                return !!_.find(this.tableMeta._fields, {f_type: 'Formula'});
            },
            hasVisConds() {
                return this.tableMeta._cond_formats.length && _.find(this.tableMeta._cond_formats, {'_visible_shared': 1});
            },
            isViewAndFiltering() {
                return this.$root.user.view_all
                    && this.$root.user.view_all.view_filtering
                    && this.$root.user.view_all._filtering
                    && this.$root.user.view_all._filtering.length;
            },
            openedFilteringView() {
                return this.tableMeta
                    && this.isViewAndFiltering;
            },
            foundBiToUpdate() {
                return this.tableMeta
                    && this.tableMeta._charts_saved
                    && this.tableMeta._charts_saved.length
                    && _.find(this.tableMeta._charts_saved, (ch) => {
                        return !!ch.chart_settings.wait_for_update;
                    });
            },
        },
        watch: {
            table_init_id(val) {
                this.table_id = val;
                this.currentTab = 'tab-list-view';
                this.showChangedListBtn = false;
                this.showChangedFavBtn = false;
                this.$root.filters = [];
                this.searchObject.keyWords = this.setKeyWords();
                this.searchObject.direct_row_id = null;
                this.addingRow.active = false;
                if (val) {
                    this.getTableMeta();
                } else {
                    this.tableMeta = null;
                    this.$root.tableMeta = null;
                }
            }
        },
        methods: {
            dataImportFinished(new_id) {
                this.tableMeta = null;
                this.$root.tableMeta = null;
                this.getMetaInBkg(new_id);
                this.showComponent('tab-list-view');
            },
            recalcTableAll() {
                this.$root.sm_msg_type = 2;
                axios.post('/ajax/table-data/recalc-all', {
                    table_id: this.table_id,
                    special_params: SpecialFuncs.specialParams(),
                }).then(({ data }) => {
                    this.curr_recalc = String(data.id);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            setKeyWords() {
                return '';
            },
            biCanAdd() {
                return ChartFunctions.settsFromMeta(this.tableMeta, this).can_add;
            },
            getHeadersCheck() {
                return this.tableMeta.db_name === 'plans_view'
                    ? ['plan_basic', 'plan_standard', 'plan_advanced', 'plan_enterprise']
                    : [];
            },
            showComponent(component) {
                this.currentTab = component;
            },
            showNote() {
                this.$root.toggleRightMenu();
            },
            showTree() {
                this.$root.toggleLeftMenu();
            },
            getMetaInBkg(new_id, nohidden) {
                if (new_id) {
                    this.table_id = new_id;
                }
                this.getTableMeta(!nohidden);
            },
            getTableMeta(hidden) {
                (hidden ? this.$root.sm_msg_type = 2 : $.LoadingOverlay('show'));
                axios.post('/ajax/table-data/get-preset', SpecialFuncs.tableMetaRequest(this.table_id)).then(({ data }) => {
                    this.queryPreset = data;
                    eventBus.$emit('changed-preset');

                    this.getTableHeaders(hidden);
                    //TODO: watch
                    // this.tableMeta = null;
                    // this.$root.tableMeta = null;
                    // this.$nextTick(() => {
                    //     this.getTableHeaders(hidden);
                    // });
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    (hidden ? this.$root.sm_msg_type = 0 : $.LoadingOverlay('hide'));
                });
            },
            getTableHeaders(hidden) {
                (hidden ? this.$root.sm_msg_type = 2 : $.LoadingOverlay('show'));
                axios.post('/ajax/table-data/get-headers', SpecialFuncs.tableMetaRequest(this.table_id)).then(({ data }) => {
                    this.$root.setTextRowSett(data);
                    this.applyPreset(data);
                    RefCondHelper.setUses(data);

                    if (
                        (data.google_api_key || this.last_tb_google_api)
                        &&
                        this.$root.user.__google_table_api !== data.google_api_key
                    ) {
                        //Reload for new Google api key.
                        location.reload();
                        return;
                    } else {
                        this.last_tb_google_api = data.google_api_key;
                    }

                    this.tableMeta = data;
                    this.$root.tableMeta = this.tableMeta;
                    this.searchObject.columns = _.map(this.tableMeta._fields, 'field');
                    this.tableMeta._js_headersWithCheck = this.getHeadersCheck();

                    this.had_address_fld = true;//!!_.find(this.tableMeta._fields, {f_type: 'Address'});

                    //hide columns which are hidden in View if owner loaded View in edit mode.
                    if (this.queryPreset && this.queryPreset.hidden_columns) {
                        _.each(this.tableMeta._fields, (header) => {
                            if ($.inArray(header.field, this.queryPreset.hidden_columns) > -1) {
                                header.is_showed = 0;
                            }
                        });
                    }

                    //hide unchecked RowGroups in 'HalfMoon'
                    if (this.queryPreset && this.queryPreset.hidden_row_groups) {
                        this.tableMeta.__hidden_row_groups = this.queryPreset.hidden_row_groups;
                        this.queryPreset.hidden_row_groups = null;
                    } else {
                        this.tableMeta.__hidden_row_groups = [];
                    }

                    if (this.$root.user.view_all) {
                        $('head title').html(this.$root.app_name+' View: '+this.$root.user.view_all.name);
                    } else {
                        $('head title').html(this.$root.app_name+': '+this.tableMeta.name);
                    }

                    eventBus.$emit('change-table-meta', this.tableMeta);
                    eventBus.$emit('re-highlight-menu-tree', true);

                    console.log('TableHeaders', this.tableMeta, 'size about: ', JSON.stringify(this.tableMeta).length);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    (hidden ? this.$root.sm_msg_type = 0 : $.LoadingOverlay('hide'));
                });
            },
            reloadJustTableFields() {
                this.$root.sm_msg_type = 2;
                axios.post('/ajax/table-data/get-tb-fields', SpecialFuncs.tableMetaRequest(this.table_id)).then(({ data }) => {
                    this.tableMeta._fields = data._fields;
                    this.$root.tableMeta._fields = this.tableMeta._fields;
                    this.searchObject.columns = _.map(this.tableMeta._fields, 'field');
                    this.tableMeta._js_headersWithCheck = this.getHeadersCheck();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            applyPreset(tableMeta) {
                if (this.filters_url_preset && this.filters_url_preset.length) {
                    this.queryPreset.applied_filters = this.filters_url_preset;
                }

                tableMeta.rows_per_page = this.queryPreset.rows_per_page || tableMeta.rows_per_page;
                this.searchObject.keyWords = this.queryPreset.search_words || this.setKeyWords();
                this.searchObject.columns = this.queryPreset.columns || [];
                this.searchObject.direct_row_id = this.queryPreset.row_id || null;
                this.$root.filters = this.queryPreset.applied_filters || [];
                _.each(this.$root.filters, (tmpflt) => {
                    let fld = _.find(tableMeta._fields, {id: Number(tmpflt.id)});
                    fld ? fld.filter = 1 : null;
                });
            },
            AddRowListView() {
                if (this.addingRow.active) {
                    eventBus.$emit('add-inline-clicked');
                } else {
                    eventBus.$emit('add-popup-clicked');
                }
            },
            searchWordChanged() {
                eventBus.$emit('search-word-changed');
            },
            tableFavoriteToggle() {
                this.tableMeta._is_favorite = !this.tableMeta._is_favorite;
                axios.put('/ajax/table/favorite', {
                    table_id: this.table_id,
                    favorite: this.tableMeta._is_favorite
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            isShowedToggled(table_field_ids, status) {
                eventBus.$emit('save-table-status');
            },
            updateMetaParams(params) {
                if (params.global_rows_count !== undefined) {
                    this.$set(this.tableMeta, '_global_rows_count', params.global_rows_count);
                }
                if (params.rows_count !== undefined) {
                    this.$set(this.tableMeta, '_view_rows_count', params.rows_count);
                }
                if (params.fav_rows_count !== undefined) {
                    this.$set(this.tableMeta, '_fav_rows_count', params.fav_rows_count);
                }
                if (params.version_hash !== undefined) {
                    this.$set(this.tableMeta, 'version_hash', params.version_hash);
                }
            },

            reshowComponent() {
                let tmp = this.currentTab;
                this.currentTab = '';
                this.$nextTick(() => {
                    this.currentTab = tmp;
                });
            },
            collaboratorChangedList() {
                eventBus.$emit('collaborator-changed-list-data');
                this.showChangedListBtn = false;
            },
            collaboratorChangedFav() {
                eventBus.$emit('collaborator-changed-fav-data');
                this.showChangedFavBtn = false;
            },

            //EVENT BUS HANDLERS
            showCopyFolderToOthersHandler(folder_id) {
                this.$root.sm_msg_type = 2;
                axios.get('/ajax/folder', {
                    params: {
                        folder_id: folder_id,
                    }
                }).then(({ data }) => {
                    this.show_copy_folder_popup = true;
                    this.copy_folder_meta = data.folder;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            showListViewComponent() {
                this.showComponent('tab-list-view');
            },
            intervalTickHandler(e) {
                if (this.tableMeta && !this.$root.sm_msg_type) {
                    let list_row_ids = _.map(this.$root.listTableRows, 'id');
                    let fav_row_ids = _.map(this.$root.favoriteTableRows, 'id');
                    axios.post('/ajax/table/version_hash', {
                        table_id: this.tableMeta.id,
                        row_list_ids: list_row_ids,
                        row_fav_ids: fav_row_ids,
                        automations_check: !document.hidden,
                    }).then(({ data }) => {
                        let changed = this.tableMeta.version_hash !== data.version_hash;
                        if (changed) {
                            this.showChangedListBtn = true;
                            this.showChangedFavBtn = true;
                            if (this.tableMeta.autoload_new_data /*&& mode.list_changed*/) {
                                this.collaboratorChangedList();
                            }
                            if (this.tableMeta.autoload_new_data /*&& mode.fav_changed*/) {
                                this.collaboratorChangedFav();
                            }
                        } else {
                            this.showChangedListBtn = false;
                            this.showChangedFavBtn = false;
                        }

                        if (!this.AnrPop && data.wait_automations && data.wait_automations._anr_tables) {
                            this.AnrPop = data.wait_automations;
                        }
                    });
                }
            },
            outShowSettingsPopup(tableHeader) {
                this.editDisplaySettingsRow = tableHeader;
            },
            selectSettingsPopup(idx) {
                this.editDisplaySettingsRow = this.tableMeta._fields[idx];
            },
            updateDisplayRow() {
                eventBus.$emit('header-updated-cell', this.editDisplaySettingsRow);
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.addOptionPopup = {
                    show: true,
                    tableHeader: tableHeader,
                    tableRow: tableRow,
                };
            },

            //Link PopUps
            showSrcRecord(lnk, header, tableRow, behavior) {
                let index = this.linkPopups.filter((el) => {return el.key === 'show'}).length;
                this.linkPopups.push({
                    key: 'show',
                    index: index,
                    link: lnk,
                    header: header,
                    row: tableRow,
                    behavior: behavior,//['map','link','list_view']
                });
            },
            closeLinkPopup(idx, should_update) {
                if (idx > -1) {
                    this.linkPopups[idx].key = 'hide';
                    this.$forceUpdate();

                    if (should_update) {
                        eventBus.$emit('reload-page');
                    }
                }
            },
            anotherRowPopup(is_next) {
                let row_id = (this.editDisplaySettingsRow ? this.editDisplaySettingsRow.id : null);
                this.$root.anotherPopup(this.tableMeta._fields, row_id, is_next, this.selectSettingsPopup);
            },

            //other
            showCondForm() {
                eventBus.$emit('show-cond-format-popup', this.tableMeta.db_name);
            },
            showViewsPopup() {
                eventBus.$emit('show-table-views-popup', this.tableMeta.db_name);
            },
            biUpdateAll() {
                eventBus.$emit('bi-view-update-all');
            },
            biAddWidget() {
                eventBus.$emit('bi-add-clicked');
            },
            viewRecordFound(filtering) {
                this.$root.request_view_filtering = filtering;
                this.temp_hide = false;
            },
        },
        mounted() {
            this.temp_hide = !!this.isViewAndFiltering;

            this.currentTab = 'tab-list-view';
            let vi_all = this.$root.user.view_all;
            if (vi_all) {
                this.viewAvails = vi_all.parts_avail ? SpecialFuncs.parseMsel(vi_all.parts_avail) : null;
                let viAvail = this.viewAvails || ['tab-list-view'];
                this.currentTab = vi_all.parts_default && viAvail.indexOf(vi_all.parts_default) > -1
                    ? vi_all.parts_default
                    : _.first(viAvail);
            }

            if (this.table_id) {
                this.getTableMeta();
            }

            //sync datas with collaborators
            setInterval(() => {
                if (!localStorage.getItem('no_ping')) {
                    this.intervalTickHandler();
                }
            }, this.$root.version_hash_delay);

            if (this.$root.user.view_all) {
                $('head title').html(this.$root.app_name+' View: '+this.$root.user.view_all.name);
            } else {
                $('head title').html(this.$root.app_name+': Table + Data + Apps');
            }

            eventBus.$on('update-loaded-view', this.getTableMeta);
            eventBus.$on('reload-meta-table', this.getMetaInBkg);
            eventBus.$on('reload-meta-tb__fields', this.reloadJustTableFields);
            eventBus.$on('show-copy-folder-to-others', this.showCopyFolderToOthersHandler);

            //show src link from Map
            eventBus.$on('show-src-record', this.showListViewComponent);
            //show searched row from Map
            eventBus.$on('show-popup', this.showListViewComponent);

            eventBus.$on('show-header-settings', this.outShowSettingsPopup);
        },
        beforeDestroy() {
            eventBus.$off('update-loaded-view', this.getTableMeta);
            eventBus.$off('reload-meta-table', this.getMetaInBkg);
            eventBus.$off('reload-meta-tb__fields', this.reloadJustTableFields);
            eventBus.$off('show-copy-folder-to-others', this.showCopyFolderToOthersHandler);
            //show src link from Map
            eventBus.$off('show-src-record', this.showListViewComponent);
            //show searched row from Map
            eventBus.$off('show-popup', this.showListViewComponent);

            eventBus.$off('show-header-settings', this.outShowSettingsPopup);
        }
    }
</script>

<style lang="scss" scoped>
    #tables {
        .btn-primary {
            font-weight: bold;
        }
        .pd35 {
            margin: 5px 3px;
            padding: 5px 3px;
        }
        .btn-sm--changed {
            padding: 2px 5px;
            height: 30px;
        }
        .btn--formula {
            height: 32px;
            width: 34px;
            padding: 0 2px 0 0;
            border-radius: 50%;
        }
        .hidden-popup-wrapper {
            /*position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;*/
        }
        .static_calcs {
            position: fixed;
            top: 35px;
        }
        .mini-add {
            padding: 5px 3px;
        }
    }
</style>