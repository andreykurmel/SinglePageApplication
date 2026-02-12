<template>
    <div id="tables" class="full-width flex flex--col" :style="$root.themeMainBgStyle">
        <!-- TAB NAVIGATION -->
        <nav class="navbar navbar-default" role="navigation">
            <div class="flex flex--wrap flex--center" :style="$root.themeRibbonStyle">
                <ul class="nav flex flex--center flex--automargin flex--wrap pull-left">
                    <div v-if="!$root.sideIsNa('side_left_menu') || !$root.sideIsNa('side_left_filter')">
                        <a @click.prevent="showTree()">
                            <span class="glyphicon" :class="[ $root.isLeftMenu ? 'glyphicon-triangle-left': 'glyphicon-triangle-right']"></span>
                        </a>
                    </div>

                    <template v-if="tableMeta">
                        <div :class="{active: currentTab === 'tab-data'}" v-if="canSeeTabData && (!viewAvails || inArray('tab-data', viewAvails))">
                            <a @click.prevent="showComponent('tab-data')" :style="textSysStyle">
                                <i class="fas fa-database"></i>&nbsp;<span class="a_txt">Data</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-settings'}" v-if="$root.user.id && (!viewAvails || inArray('tab-settings', viewAvails) || inArray('tab-settings-cust', viewAvails))">
                            <a @click.prevent="showComponent('tab-settings')" :style="textSysStyle">
                                <i class="fas fa-cogs"></i>&nbsp;<span class="a_txt">Settings</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-list-view'}" v-if="(!viewAvails || inArray('tab-list-view', viewAvails))">
                            <a @click.prevent="showComponent('tab-list-view')" :style="textSysStyle">
                                <span class="fas fa-th" style="color: inherit"></span>&nbsp;<span class="a_txt">{{ gridNaming() }}</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-vue-virtual'}" v-if="$root.user.id && inArray($root.user.role_id, [1, 3])">
                            <a @click.prevent="showComponent('tab-vue-virtual')" :style="textSysStyle">
                                <span class="a_txt">Vue Virtual</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-favorite'}" v-if="$root.user.id && (!viewAvails || inArray('tab-favorite', viewAvails))">
                            <a @click.prevent="showComponent('tab-favorite')" :style="textSysStyle">
                                <span class="glyphicon glyphicon-star"></span>&nbsp;<span class="a_txt">Favorite</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-kanban-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'kanban') && tableMeta.add_kanban && (!viewAvails || inArray('tab-kanban-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-kanban-add')" :style="textSysStyle">
                                <i class="fab fa-trello"></i>&nbsp;<span class="a_txt">Kanban</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-gantt-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'gantt') && tableMeta.add_gantt && (!viewAvails || inArray('tab-gantt-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-gantt-add')" :style="textSysStyle">
                                <i class="fas fa-chart-bar"></i>&nbsp;<span class="a_txt">Gantt</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-dcr-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'request') && tableMeta.add_request && (!viewAvails || inArray('tab-dcr-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-dcr-add')" :style="textSysStyle">
                                <i class="far fa-calendar-check"></i>&nbsp;<span class="a_txt">DCR</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-map-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'map') && tableMeta.add_map && (!viewAvails || inArray('tab-map-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-map-add')" :style="textSysStyle">
                                <span class="glyphicon glyphicon-map-marker"></span>&nbsp;<span class="a_txt">GSI</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-bi-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'bi') && tableMeta.add_bi && (!viewAvails || inArray('tab-bi-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-bi-add')" :style="textSysStyle">
                                <span class="glyphicon glyphicon-equalizer"></span>&nbsp;<span class="a_txt">BI</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-alert-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'alert') && tableMeta.add_alert && (!viewAvails || inArray('tab-alert-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-alert-add')" :style="textSysStyle">
                                <img src="/assets/img/bell.png" width="15" height="15" style="filter: grayscale(1);"/>&nbsp;<span class="a_txt">ANA</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-email-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'email') && tableMeta.add_email && (!viewAvails || inArray('tab-email-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-email-add')" :style="textSysStyle">
                                <i class="far fa-envelope"></i>&nbsp;<span class="a_txt">Email</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-calendar-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'calendar') && tableMeta.add_calendar && (!viewAvails || inArray('tab-calendar-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-calendar-add')" :style="textSysStyle">
                                <i class="far fa-calendar"></i>&nbsp;<span class="a_txt">Calendar</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-twilio-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'twilio') && tableMeta.add_twilio && (!viewAvails || inArray('tab-twilio-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-twilio-add')" :style="textSysStyle">
                                <i class="fas fa-sms"></i>&nbsp;<span class="a_txt">SMS</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-tournament-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'tournament') && tableMeta.add_tournament && (!viewAvails || inArray('tab-tournament-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-tournament-add')" :style="textSysStyle">
                                <i class="fas fa-futbol"></i>&nbsp;<span class="a_txt">Brackets</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-report-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'report') && tableMeta.add_report && (!viewAvails || inArray('tab-report-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-report-add')" :style="textSysStyle">
                                <i class="fas fa-file"></i>&nbsp;<span class="a_txt">Report</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-ai-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'ai') && tableMeta.add_ai && (!viewAvails || inArray('tab-ai-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-ai-add')" :style="textSysStyle">
                                <i class="fas fa-microchip"></i>&nbsp;<span class="a_txt">AI</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-grouping-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'grouping') && tableMeta.add_grouping && (!viewAvails || inArray('tab-grouping-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-grouping-add')" :style="textSysStyle">
                                <i class="fas fa-layer-group"></i>&nbsp;<span class="a_txt">Grouping</span>
                            </a>
                        </div>
                        <div :class="{active: currentTab === 'tab-simplemap-add'}" v-if="$root.AddonAvailableToUser(tableMeta, 'simplemap') && tableMeta.add_simplemap && (!viewAvails || inArray('tab-simplemap-add', viewAvails))">
                            <a @click.prevent="showComponent('tab-simplemap-add')" :style="textSysStyle">
                                <i class="fas fa-layer-group"></i>&nbsp;<span class="a_txt">TMaps</span>
                            </a>
                        </div>
                    </template>
                </ul>
                <div class="flex__elem-remain" style="min-width: fit-content;">
                    <div
                        v-if="$root.user && $root.user.view_all"
                        class="flex flex--center full-height"
                        style="font-size: 18px;position: relative;"
                    >{{ $root.user.view_all.name }}</div>
                    <formulas-calculating v-if="curr_recalc" :job_id="curr_recalc" :job_type="recalc_type" class="static_calcs"></formulas-calculating>
                </div>
                <div class="nav flex flex--center flex--automargin flex--wrap pull-right">
                    <template v-if="table_id && tableMeta && settingsMeta">
                        <div v-show="inArray(currentTab, ['tab-list-view', 'tab-vue-virtual'])">
                            <a>
                                <selected-rows-button
                                    :table-meta="tableMeta"
                                    :request_params="$root.request_params"
                                    :all-rows="$root.listTableRows"
                                    @update-meta-params="updateMetaParams"
                                ></selected-rows-button>
                            </a>
                        </div>
                        <div v-show="presentFormulaFields && inArray(currentTab, ['tab-list-view', 'tab-vue-virtual'])">
                            <button class="btn btn-default btn-sm btn--formula blue-gradient"
                                    :style="$root.themeButtonStyle"
                                    @click="recalcTableAll()"
                            >
                                <img src="/assets/img/formula_reload.png" width="30" height="30"/>
                            </button>
                        </div>
                        <div v-show="showChangedListBtn && inArray(currentTab, ['tab-list-view', 'tab-vue-virtual'])">
                            <button class="btn btn-warning btn-sm pd35" :style="{fontWeight: 'bold'}" @click="collaboratorChangedList()">Reload</button>
                        </div>
                        <div v-show="showChangedFavBtn && inArray(currentTab, ['tab-favorite'])">
                            <button class="btn btn-warning btn-sm pd35" :style="{fontWeight: 'bold'}" @click="collaboratorChangedFav()">Reload</button>
                        </div>

                        <div v-if="canVisCondFormat" v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite', 'tab-vue-virtual'])">
                            <a>
                                <span class="btn btn-default btn-sm--changed blue-gradient" @click="showCondForm()" :style="$root.themeButtonStyle">
                                    <img src="/assets/img/conditional_formatting_small_1.png" width="25" height="25"/>
                                </span>
                            </a>
                        </div>
                        <div v-if="!$root.user.see_view" v-show="inArray(currentTab, ['tab-list-view', 'tab-vue-virtual'])">
                            <a>
                                <span class="btn btn-primary btn-sm blue-gradient"
                                      @click="showViewsPopup()"
                                      style="padding: 5px 3px"
                                      :style="$root.themeButtonStyle">
                                    <span>Views</span>
                                </span>
                            </a>
                        </div>
                        <div v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite', 'tab-vue-virtual'])" v-if="tableMeta">
                            <a><cell-height-button
                                    :table_meta="tableMeta"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                                    @change-cell-height="$root.changeCellHeight"
                                    @change-max-cell-rows="$root.changeMaxCellRows"
                            ></cell-height-button></a>
                        </div>
                        <div v-if="!$root.user.see_view || canAdd" v-show="inArray(currentTab, ['tab-list-view', 'tab-vue-virtual'])">
                            <a><add-button :available="canAdd" :adding-row="addingRow" @add-clicked="AddRowListView"></add-button></a>
                        </div>
                        <div v-if="tableMeta && canSomeEdit" v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite', 'tab-vue-virtual'])">
                            <a>
                                <button class="btn btn-primary btn-sm blue-gradient"
                                        :style="$root.themeButtonStyle"
                                        @click="show_dm_popup = true"
                                >DM</button>
                            </a>
                        </div>
                        <div v-if="tableMeta" v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite', 'tab-vue-virtual'])">
                            <a><search-button :table_id="table_id"
                                              :table-meta="tableMeta"
                                              :search-object="searchObject"
                                              :request-params="$root.request_params"
                                              @search-word-changed="searchWordChanged()"
                            ></search-button></a>
                        </div>
                        <div v-if="tableMeta" v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite', 'tab-vue-virtual'])">
                            <a><show-hide-button
                                :table-meta="tableMeta"
                                :user="$root.user"
                                @show-changed="isShowedToggled"
                                @show-linked-rows="showSrcRecord"
                            ></show-hide-button></a>
                        </div>
                        <div v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite', 'tab-vue-virtual'])">
                            <a @click.prevent="$root.fullWidthCellToggle()"><full-width-button :full-width-cell="$root.fullWidthCell"></full-width-button></a>
                        </div>
                        <div v-if="$root.user.id && tableMeta"
                            v-show="inArray(currentTab, ['tab-list-view', 'tab-favorite', 'tab-vue-virtual'])"
                        >
                            <a><download-button :tb_id="'table_list_view'"
                                                :table-meta="tableMeta"
                                                :search-object="searchObject"
                                                :list-rows="$root.listTableRows"
                            ></download-button></a>
                        </div>

                        <div v-show="inArray(currentTab, ['tab-list-view', 'tab-vue-virtual'])">
                            <info-sign-link v-if="settingsMeta.is_loaded" :app_sett_key="'help_link_list_view_tab'" :txt="'for Grid View'"></info-sign-link>
                        </div>
                        <div v-show="currentTab === 'tab-favorite'">
                            <info-sign-link v-if="settingsMeta.is_loaded" :app_sett_key="'help_link_favorite_tab'" :txt="'for Favorite'"></info-sign-link>
                        </div>
                        <div v-show="currentTab === 'tab-bi-add'" style="padding: 0 3px">
                            <info-sign-link v-if="settingsMeta.is_loaded" :app_sett_key="'help_link_bi_tab'" :txt="'for Charts'"></info-sign-link>
                        </div>
                    </template>

                    <div v-if="!$root.sideIsNa('side_right')">
                        <a @click.prevent="showNote()"><span class="glyphicon" :class="[ $root.isRightMenu ? 'glyphicon-triangle-right': 'glyphicon-triangle-left']"></span></a>
                    </div>
                </div>
            </div>
        </nav>
        <!-- FILTERS BLOCK -->
        <div v-if="fltrVar() && $root.filters && inArray(currentTab, ['tab-list-view','tab-vue-virtual','tab-favorite'])"
             class="flex flex--wrap"
             :style="{justifyContent: fltrVar('pos')}"
             style="padding: 3px 0 3px 3px;"
        >
            <filters-combo-block
                :table-meta="tableMeta"
                :input_filters="$root.filters"
                :fixed-pos="true"
                :curr-changed="currChanged"
                :placed="'top_filters'"
                @curr-updated="(val) => { currChanged = val; }"
                @applied-saved-filter="aplFilter"
            ></filters-combo-block>
            <filters-block
                :table-meta="tableMeta"
                :input_filters="$root.filters"
                :fixed-pos="true"
                :placed="'top_filters'"
                :style="{justifyContent: fltrVar('pos')}"
                @changed-filter="aplFilter()"
            ></filters-block>
        </div>
        <!-- TABS CONTENT -->
        <div class="tabs-wrapper" v-if="settingsMeta && !temp_hide" :style="$root.themeMainBgStyle">

            <tab-data
                v-show="currentTab === 'tab-data'"
                v-if="canSeeTabData && tableAndSetts && (!viewAvails || inArray('tab-data', viewAvails)) && currentTab === 'tab-data'"
                :table_id="tableMeta.id"
                :user="$root.user"
                :table-meta="tableMeta"
                :cell-height="1"
                :max-cell-rows="0"
                @import-finished="dataImportFinished"
            ></tab-data>

            <!-- Note: no re-creations to better performance and RG/DDL/Link functions -->
            <tab-settings
                v-show="currentTab === 'tab-settings'"
                v-if="tableAndSetts && (!viewAvails || inArray('tab-settings', viewAvails) || inArray('tab-settings-cust', viewAvails))"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :cell-height="1"
                :max-cell-rows="0"
                :settings-tab="settingsTab"
                :user="$root.user"
                :table_id="tableMeta.id"
                :is-visible="currentTab === 'tab-settings'"
                @show-src-record="showSrcRecord"
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
                :preset_page="queryPreset.page"
                :preset_sort="queryPreset.sort"
                :is-visible="currentTab === 'tab-list-view'"
                :recalc_job_id="curr_recalc"
                :has_filters_url_preset="Boolean(filters_url_preset && filters_url_preset.length)"
                @update-meta-params="updateMetaParams"
                @show-src-record="showSrcRecord"
                @show-add-ddl-option="showAddDDLOption"
            ></tab-list-view>

            <tab-favorite
                v-show="currentTab === 'tab-favorite'"
                v-if="tableAndSetts && (!viewAvails || inArray('tab-favorite', viewAvails))"
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
                v-if="tableAndSetts && tableMeta.add_request && $root.AddonAvailableToUser(tableMeta, 'request') && (!viewAvails || inArray('tab-dcr-add', viewAvails))"
                :table-meta="tableMeta"
                :cell-height="1"
                :max-cell-rows="0"
                :table_id="tableMeta.id"
                :is-visible="currentTab === 'tab-dcr-add'"
            ></tab-settings-requests>

            <tab-map-view
                v-show="currentTab === 'tab-map-add'"
                v-if="tableAndSetts && tableMeta.add_map && $root.AddonAvailableToUser(tableMeta, 'map') && (!viewAvails || inArray('tab-map-add', viewAvails))"
                :table_id="tableMeta.id"
                :table-meta="tableMeta"
                :request_params="$root.request_params"
                :can-edit="$root.AddonAvailableToUser(tableMeta, 'map', 'edit')"
                :is-visible="currentTab === 'tab-map-add'"
                @show-src-record="showSrcRecord"
            ></tab-map-view>

            <tab-bi-view
                v-show="currentTab === 'tab-bi-add'"
                v-if="tableAndSetts && tableMeta.add_bi && $root.AddonAvailableToUser(tableMeta, 'bi') && (!viewAvails || inArray('tab-bi-add', viewAvails))"
                :table_id="tableMeta.id"
                :table-meta="tableMeta"
                :rows_count="tableMeta._view_rows_count"
                :request_params="$root.request_params"
                :is-visible="currentTab === 'tab-bi-add'"
                @show-src-record="showSrcRecord"
            ></tab-bi-view>

            <tab-alert-and-notif
                    v-show="currentTab === 'tab-alert-add'"
                    v-if="tableAndSetts && tableMeta.add_alert && $root.AddonAvailableToUser(tableMeta, 'alert') && (!viewAvails || inArray('tab-alert-add', viewAvails))"
                    :table_id="tableMeta.id"
                    :table-meta="tableMeta"
                    :user="$root.user"
            ></tab-alert-and-notif>

            <tab-kanban-view
                    v-show="currentTab === 'tab-kanban-add'"
                    v-if="tableAndSetts && tableMeta.add_kanban && $root.AddonAvailableToUser(tableMeta, 'kanban') && (!viewAvails || inArray('tab-kanban-add', viewAvails))"
                    :table_id="tableMeta.id"
                    :table-meta="tableMeta"
                    :request_params="$root.request_params"
                    :is-visible="currentTab === 'tab-kanban-add'"
                    @show-src-record="showSrcRecord"
            ></tab-kanban-view>

            <tab-gantt-view
                    v-show="currentTab === 'tab-gantt-add'"
                    v-if="tableAndSetts && tableMeta.add_gantt && $root.AddonAvailableToUser(tableMeta, 'gantt') && (!viewAvails || inArray('tab-gantt-add', viewAvails))"
                    :table_id="tableMeta.id"
                    :table-meta="tableMeta"
                    :request_params="$root.request_params"
                    :is-visible="currentTab === 'tab-gantt-add'"
                    @show-src-record="showSrcRecord"
            ></tab-gantt-view>

            <tab-email-view
                    v-show="currentTab === 'tab-email-add'"
                    v-if="tableAndSetts && tableMeta.add_email && $root.AddonAvailableToUser(tableMeta, 'email') && (!viewAvails || inArray('tab-email-add', viewAvails))"
                    :table-meta="tableMeta"
                    :user="$root.user"
                    :is-visible="currentTab === 'tab-email-add'"
            ></tab-email-view>

            <tab-calendar-view
                    v-show="currentTab === 'tab-calendar-add'"
                    v-if="tableAndSetts && tableMeta.add_calendar && $root.AddonAvailableToUser(tableMeta, 'calendar') && (!viewAvails || inArray('tab-calendar-add', viewAvails))"
                    :table_id="tableMeta.id"
                    :table-meta="tableMeta"
                    :request_params="$root.request_params"
                    :is-visible="currentTab === 'tab-calendar-add'"
                    @show-src-record="showSrcRecord"
            ></tab-calendar-view>

            <tab-twilio-view
                    v-show="currentTab === 'tab-twilio-add'"
                    v-if="tableAndSetts && tableMeta.add_twilio && $root.AddonAvailableToUser(tableMeta, 'twilio') && (!viewAvails || inArray('tab-twilio-add', viewAvails))"
                    :table-meta="tableMeta"
                    :is-visible="currentTab === 'tab-twilio-add'"
            ></tab-twilio-view>

            <tab-tournament-view
                    v-show="currentTab === 'tab-tournament-add'"
                    v-if="tableAndSetts && tableMeta.add_tournament && $root.AddonAvailableToUser(tableMeta, 'tournament') && (!viewAvails || inArray('tab-tournament-add', viewAvails))"
                    :table-meta="tableMeta"
                    :request-params="$root.request_params"
                    :is-visible="currentTab === 'tab-tournament-add'"
                    :settings_click="tournament_sett_click"
                    @show-src-record="showSrcRecord"
            ></tab-tournament-view>

            <tab-grouping-view
                    v-show="currentTab === 'tab-grouping-add'"
                    v-if="tableAndSetts && tableMeta.add_grouping && $root.AddonAvailableToUser(tableMeta, 'grouping') && (!viewAvails || inArray('tab-grouping-add', viewAvails))"
                    :table_id="tableMeta.id"
                    :table-meta="tableMeta"
                    :current-page-rows="$root.listTableRows"
                    :request_params="$root.request_params"
                    :is-visible="currentTab === 'tab-grouping-add'"
                    @show-src-record="showSrcRecord"
            ></tab-grouping-view>

            <tab-report-view
                    v-show="currentTab === 'tab-report-add'"
                    v-if="tableAndSetts && tableMeta.add_report && $root.AddonAvailableToUser(tableMeta, 'report') && (!viewAvails || inArray('tab-report-add', viewAvails))"
                    :table-meta="tableMeta"
                    :table-rows="$root.listTableRows"
                    :request_params="$root.request_params"
                    :is-visible="currentTab === 'tab-report-add'"
                    @show-src-record="showSrcRecord"
            ></tab-report-view>

            <tab-ai-view
                    v-show="currentTab === 'tab-ai-add'"
                    v-if="tableAndSetts && tableMeta.add_ai && $root.AddonAvailableToUser(tableMeta, 'ai') && (!viewAvails || inArray('tab-ai-add', viewAvails))"
                    :table-meta="tableMeta"
                    :table-rows="$root.listTableRows"
                    :request_params="$root.request_params"
                    :is-visible="currentTab === 'tab-ai-add'"
                    @show-src-record="showSrcRecord"
            ></tab-ai-view>

            <tab-simplemap-view
                    v-show="currentTab === 'tab-simplemap-add'"
                    v-if="tableAndSetts && tableMeta.add_simplemap && $root.AddonAvailableToUser(tableMeta, 'simplemap') && (!viewAvails || inArray('tab-simplemap-add', viewAvails))"
                    :table_id="tableMeta.id"
                    :table-meta="tableMeta"
                    :current-page-rows="$root.listTableRows"
                    :request_params="$root.request_params"
                    :is-visible="currentTab === 'tab-simplemap-add'"
                    @show-src-record="showSrcRecord"
            ></tab-simplemap-view>

        </div>

        <!--Only For TableViews-->
        <table-view-filtering-popup
            v-if="isViewAndFiltering"
            :table-meta="tableMeta"
            :table-view="$root.user.view_all"
            @record-found="viewRecordFound"
        ></table-view-filtering-popup>

        <div v-if="settingsMeta.is_loaded" class="hidden-popup-wrapper">
            <!--Popup for showing very long datas-->
            <table-data-string-popup :max-cell-rows="0"></table-data-string-popup>


            <!--Edit Popup for 'Email','Phone Number'-->
            <cell-email-phone-popup :table-meta="tableMeta"></cell-email-phone-popup>


            <!--Twilio Send (Email/Sms/Call) Popup-->
            <twilio-send-popup :table-meta="tableMeta"></twilio-send-popup>


            <!--Twilio Incoming Call/SMS Popup-->
            <twilio-incoming-call v-if="defaultTwAcc" :twilio_acc_id="defaultTwAcc.id"></twilio-incoming-call>
            <twilio-incoming-sms v-if="defaultTwAcc" :twilio_acc_id="defaultTwAcc.id"></twilio-incoming-sms>


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
                    @show-src-record="showSrcRecord"
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

            <overview-formats-popup
                v-if="tableMeta"
                :table-meta="tableMeta"
            ></overview-formats-popup>


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
                    :ava-tabs="['basics']"
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
                    @direct-row="selectSettingsPopup"
            ></for-settings-pop-up>

            <!--For ANR Automations-->
            <proceed-automation-popup
                    v-if="AnrPop"
                    :table-meta="tableMeta"
                    :table-alert="AnrPop"
                    @hide-popup="AnrPop = null"
            ></proceed-automation-popup>

            <!--Link Popups from ListView and MapView.-->
            <template v-for="(linkObj, idx) in linkPopups">
                <header-history-pop-up
                    v-if="linkObj.key === 'show' && linkObj.link.link_type === 'History'"
                    :is-visible="true"
                    :idx="linkObj.index"
                    :table-meta="tableMeta"
                    :table-row="linkObj.row"
                    :history-header="linkObj.header"
                    :link="linkObj.link"
                    :popup-key="idx"
                    @popup-close="closeLinkPopup"
                ></header-history-pop-up>
                <link-pop-up
                    v-else-if="linkObj.key === 'show'"
                    :source-meta="tableMeta"
                    :idx="linkObj.index"
                    :link="linkObj.link"
                    :meta-header="linkObj.header"
                    :meta-row="linkObj.row"
                    :popup-key="idx"
                    :view_authorizer="{
                        mrv_marker: $root.is_mrv_page,
                        srv_marker: $root.is_srv_page,
                        dcr_marker: $root.is_dcr_page,
                        view_hash: $root.user.view_hash,
                        is_folder_view: $root.user._is_folder_view
                    }"
                    :available-columns="linkObj.link.avail_columns"
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
    import {DDLHelper} from "../../../../classes/helpers/DDLHelper";
    import {FieldHelper} from "../../../../classes/helpers/FieldHelper";

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
    import TabTwilioView from "./TabTwilioView";
    import TabReportView from "./TabReportView";
    import TabAiView from "./TabAiView.vue";
    import TabTournamentView from "./TabTournamentView";
    import TabGroupingView from "./TabGroupingView.vue";
    import TabSimplemapView from "./TabSimplemapView.vue";

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
    import BiSettingsButton from "../../../Buttons/BiSettingsButton";

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
    import GroupingSettingsPopup from "../../../CustomPopup/GroupingSettingsPopup";
    import DdlSettingsPopup from "../../../CustomPopup/DdlSettingsPopup";
    import PermissionsSettingsPopup from "../../../CustomPopup/PermissionsSettingsPopup";
    import TableSettingsAllPopup from "../../../CustomPopup/TableSettingsAllPopup";
    import GeneralSettingsPopup from "../../../CustomPopup/GeneralSettingsPopup";
    import TableViewFilteringPopup from "../../../CustomPopup/TableViewFilteringPopup";
    import BackupSettingsPopup from "../../../CustomPopup/BackupSettingsPopup";
    import ProceedAutomationPopup from "../../../CustomPopup/ProceedAutomationPopup";
    import DisplayLinkSettingsPopup from "../../../CustomPopup/DisplayLinkSettingsPopup";
    import AddTableColumnPopup from "../../../CustomPopup/AddTableColumnPopup";
    import AddOptionPopup from "../../../CustomPopup/AddOptionPopup";
    import ReferenceColorsPopup from "../../../CustomPopup/ReferenceColorsPopup";
    import DataModifPopup from "../../../CustomPopup/DataModifPopup";
    import TwilioSendPopup from "./Twilio/Popup/TwilioSendPopup";
    import CellEmailPhonePopup from "../../../CustomPopup/CellEmailPhonePopup";
    import OverviewFormatsPopup from "../../../CustomPopup/OverviewFormatsPopup";
    import HeaderHistoryPopUp from "../../../CustomPopup/HeaderHistoryPopUp";

    import TwilioIncomingCall from "./Twilio/Popup/TwilioIncomingCall";
    import TwilioIncomingSms from "./Twilio/Popup/TwilioIncomingSms";
    import FiltersComboBlock from "../../../CommonBlocks/FiltersComboBlock";
    import FiltersBlock from "../../../CommonBlocks/FiltersBlock";

    export default {
        name: "Tables",
        mixins: [
            CanEditMixin,
            CellStyleMixin,
        ],
        components: {
            FiltersBlock,
            FiltersComboBlock,
            TwilioIncomingCall,
            TwilioIncomingSms,

            TabData,
            TabSettings,
            TabTwilioView,
            TabCalendarView,
            TabEmailView,
            TabGanttView,
            TabListView,
            TabFavorite,
            TabMapView,
            TabBiView,
            TabKanbanView,
            TabAlertAndNotif,
            TabSettingsRequests,
            TabReportView,
            TabAiView,
            TabSimplemapView,
            TabGroupingView,
            TabTournamentView,

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

            HeaderHistoryPopUp,
            OverviewFormatsPopup,
            DataModifPopup,
            ReferenceColorsPopup,
            AddOptionPopup,
            AddTableColumnPopup,
            DisplayLinkSettingsPopup,
            ProceedAutomationPopup,
            TableViewsPopup,
            ConditionalFormattingPopup,
            CopyFolderToOthersPopup,
            TableDataStringPopup,
            RefConditionsPopup,
            ForSettingsPopUp,
            DdlSettingsPopup,
            GroupingSettingsPopup,
            PermissionsSettingsPopup,
            TableSettingsAllPopup,
            GeneralSettingsPopup,
            BackupSettingsPopup,
            TableViewFilteringPopup,
            CellEmailPhonePopup,
            TwilioSendPopup,
        },
        data: function () {
            return {
                biRequestsActive: 0,
                triggerBiSave: false,
                currChanged: false,
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
                curr_recalc: null,
                recalc_type: null,
                vh_errors: 0,

                had_address_fld: false,

                tournament_sett_click: 0,

                table_id: this.table_init_id,
            }
        },
        props: {
            settingsMeta: Object,
            isPagination: Boolean,
            table_init_id: Number|null,
            filters_preset: Array,
            request_vars: Object|Array,
        },
        computed: {
            tableAndSetts() {
                return this.tableMeta && this.settingsMeta.is_loaded;
            },
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
                return !!_.find(this.tableMeta._fields, {input_type: 'Formula'});
            },
            canVisCondFormat() {
                return this.tableMeta._is_owner
                    || (this.tableMeta._current_right && this.tableMeta._current_right.can_create_condformat)
                    || (this.tableMeta._cond_formats.length && _.find(this.tableMeta._cond_formats, {'_visible_shared': 1}));
            },
            isViewAndFiltering() {
                return this.$root.user.view_all
                    && this.$root.user.view_all.view_filtering
                    && this.$root.user.view_all._filtering
                    && this.$root.user.view_all._filtering.length;
            },
            defaultTwAcc() {
                return this.$root.user._twilio_api_keys.find((tw) => {
                    return tw.is_active;
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
            },
            tableAndSetts(val) {
                if (val) {//works on page opening
                    this.afterLoadChanges();
                }
            },
        },
        methods: {
            gridNaming() {
                switch (this.tableMeta ? this.tableMeta.primary_view : '') {
                    case 'board_view': return 'Board View';
                    case 'list_view': return 'List View';
                    case 'grid_view': return 'Grid View';
                    default: return 'Grid View';
                }
            },
            fltrVar(neededPos) {
                return SpecialFuncs.filterOnTop(this.tableMeta, this.$root.user, neededPos);
            },
            aplFilter(filters) {
                if (filters) {
                    this.$root.filters = filters;
                }
                eventBus.$emit('changed-filter');
            },
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
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            setKeyWords() {
                return '';
            },
            getHeadersCheck() {
                return this.tableMeta.db_name === 'plans_view'
                    ? ['plan_basic', 'plan_standard', 'plan_advanced', 'plan_enterprise']
                    : [];
            },
            showComponent(component) {
                this.currentTab = component;
                switch (component) {
                    case 'tab-kanban-add':
                        this.$root.selectedAddon.name = 'Kanban';
                        this.$root.selectedAddon.code = 'kanban';
                        break;
                    case 'tab-gantt-add':
                        this.$root.selectedAddon.name = 'Gantt';
                        this.$root.selectedAddon.code = 'gantt';
                        break;
                    case 'tab-dcr-add':
                        this.$root.selectedAddon.name = 'DCR';
                        this.$root.selectedAddon.code = 'dcr';
                        break;
                    case 'tab-map-add':
                        this.$root.selectedAddon.name = 'GSI';
                        this.$root.selectedAddon.code = 'map';
                        break;
                    case 'tab-bi-add':
                        this.$root.selectedAddon.name = 'BI';
                        this.$root.selectedAddon.code = 'bi';
                        break;
                    case 'tab-alert-add':
                        this.$root.selectedAddon.name = 'ANA';
                        this.$root.selectedAddon.code = 'alert';
                        break;
                    case 'tab-email-add':
                        this.$root.selectedAddon.name = 'Email';
                        this.$root.selectedAddon.code = 'email';
                        break;
                    case 'tab-calendar-add':
                        this.$root.selectedAddon.name = 'Calendar';
                        this.$root.selectedAddon.code = 'calendar';
                        break;
                    case 'tab-twilio-add':
                        this.$root.selectedAddon.name = 'SMS';
                        this.$root.selectedAddon.code = 'twilio';
                        break;
                    case 'tab-tournament-add':
                        this.$root.selectedAddon.name = 'Brackets';
                        this.$root.selectedAddon.code = 'tournament';
                        break;
                    case 'tab-grouping-add':
                        this.$root.selectedAddon.name = 'Grouping';
                        this.$root.selectedAddon.code = 'grouping';
                        break;
                    case 'tab-report-add':
                        this.$root.selectedAddon.name = 'Report';
                        this.$root.selectedAddon.code = 'report';
                        break;
                    case 'tab-ai-add':
                        this.$root.selectedAddon.name = 'AI';
                        this.$root.selectedAddon.code = 'ai';
                        break;
                    case 'tab-simplemap-add':
                        this.$root.selectedAddon.name = 'TMaps';
                        this.$root.selectedAddon.code = 'simplemap';
                        break;
                    default:
                        this.$root.selectedAddon.name = '';
                        this.$root.selectedAddon.code = '';
                        break;
                }
            },
            showNote() {
                this.$root.toggleRightMenu();
            },
            showTree() {
                this.$root.toggleLeftMenu();
            },
            getMetaInBkg(new_id) {
                if (new_id) {
                    this.table_id = new_id;
                }
                this.getTableMeta(new_id);
            },
            getTableMeta(hidden) {
                (hidden ? this.$root.sm_msg_type = 2 : $.LoadingOverlay('show'));
                axios.post('/ajax/table-data/get-preset', SpecialFuncs.tableMetaRequest(this.table_id)).then(({ data }) => {
                    this.queryPreset = data || {};
                    this.getTableHeaders(hidden);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                    DDLHelper.setUses(data);
                    FieldHelper.setDisabledKeys(data);

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

                    this.afterLoadChanges();

                    console.log('TableHeaders', this.tableMeta, 'size about: ', JSON.stringify(this.tableMeta).length);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    (hidden ? this.$root.sm_msg_type = 0 : $.LoadingOverlay('hide'));
                });
            },
            afterLoadChanges() {
                if (this.tableAndSetts && this.tableMeta.add_bi /*&& this.tableMeta.add_report*/ && this.currentTab !== 'tab-bi-add') {
                    window.setTimeout(() => {
                        //trigger charts storaging on the server for the reports.
                        this.triggerBiSave = true;
                    }, 1000);
                }
            },
            biRequestsHandler(val) {
                this.biRequestsActive += val;
                if (!this.biRequestsActive) {
                    this.triggerBiSave = false;//NOTE: triggerBiSave will be usable for independent chart saving component for reports.
                }
            },
            reloadJustTableFields() {
                this.$root.sm_msg_type = 2;
                axios.post('/ajax/table-data/get-tb-fields', SpecialFuncs.tableMetaRequest(this.table_id)).then(({ data }) => {
                    this.tableMeta._fields = data._fields;
                    this.$root.tableMeta._fields = this.tableMeta._fields;
                    this.searchObject.columns = _.map(this.tableMeta._fields, 'field');
                    this.tableMeta._js_headersWithCheck = this.getHeadersCheck();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            showListViewComponent() {
                this.showComponent('tab-list-view');
            },
            intervalTickHandler(e) {
                if (this.tableMeta && !this.$root.sm_msg_type && this.vh_errors < 3) {
                    let list_row_ids = _.map(this.$root.listTableRows, 'id');
                    let fav_row_ids = _.map(this.$root.favoriteTableRows, 'id');
                    axios.post('/ajax/table/version_hash', {
                        table_id: this.table_id,
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
                                this.tableMeta.version_hash = data.version_hash;
                            }
                            if (this.tableMeta.autoload_new_data /*&& mode.fav_changed*/) {
                                this.collaboratorChangedFav();
                                this.tableMeta.version_hash = data.version_hash;
                            }
                        } else {
                            this.showChangedListBtn = false;
                            this.showChangedFavBtn = false;
                        }

                        if (data.job_msg) {
                            Swal('Info', data.job_msg);
                        }

                        if (!this.AnrPop && data.wait_automations && data.wait_automations._anr_tables) {
                            this.AnrPop = data.wait_automations;
                        }

                        this.curr_recalc = data.recalc_id;
                        this.recalc_type = data.recalc_type;
                    }).catch(() => {
                        this.vh_errors++;
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
                let index = _.filter(this.linkPopups, {key: 'show'}).length;
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
                if (this.request_vars && this.request_vars.only_viewpart) {
                    this.viewAvails = [this.request_vars.only_viewpart];
                } else {
                    this.viewAvails = vi_all.parts_avail ? SpecialFuncs.parseMsel(vi_all.parts_avail) : null;
                }
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
            eventBus.$on('run-after-load-changes', this.afterLoadChanges);
            eventBus.$on('set-bi-requests-active', this.biRequestsHandler);
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
            eventBus.$off('run-after-load-changes', this.afterLoadChanges);
            eventBus.$off('set-bi-requests-active', this.biRequestsHandler);
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
        .static_calcs {
            position: fixed;
            top: 35px;
            left: 210px;
        }
        .mini-add {
            padding: 5px 3px;
        }

        .nav {
            div {
                a {
                    &:hover {
                        text-decoration: none;
                    }
                    .a_txt {
                        &:hover {
                            text-decoration: underline;
                        }
                    }
                }
            }
        }
    }
</style>