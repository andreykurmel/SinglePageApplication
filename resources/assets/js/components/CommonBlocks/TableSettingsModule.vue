<template>
    <div :style="textSysStyle">
        <div v-if="(isOwner || type) && !filter_for_field"
             class="flex flex--center flex--space form-group"
             :style="{width: max_set_len+'px'}"
        >
            <label class="">Table Name:</label>
            <input class="form-control l-inl-control"
                   type="text"
                   @change="propChanged('name')"
                   v-model="tb_meta.name"
                   :style="textSysStyle">
        </div>

        <template v-if="isOwnerOrCanEdit('can_change_primaryview') && !filter_for_field">
            <div class="flex flex--center flex--space form-group"
                 :style="{width: max_set_len+'px'}"
            >
                <label>Primary View:</label>
                <select class="form-control l-inl-control inl-sm"
                        @change="propChanged('primary_view')"
                        v-model="tb_meta.primary_view"
                        :style="textSysStyle"
                        style="width: 100%; min-width: 110px;">
                    <option value="grid_view">Grid View</option>
                    <option value="list_view">List View</option>
                    <option value="board_view">Board View</option>
                </select>
            </div>

            <div class="flex flex--center flex--space form-group"
                 :style="{width: max_set_len+'px'}"
            >
                <label>Width, %:</label>
                <input class="form-control l-inl-control inl-sm"
                       :disabled="tb_meta.primary_view === 'grid_view'"
                       @change="propChanged('primary_width')"
                       v-model="tb_meta.primary_width"
                       :style="textSysStyle"
                       style="width: 100%; min-width: 60px;"/>

                <label>&nbsp;&nbsp;&nbsp;Alignment:</label>
                <select class="form-control l-inl-control inl-sm"
                        @change="propChanged('primary_align')"
                        v-model="tb_meta.primary_align"
                        :style="textSysStyle"
                        style="width: 100%; min-width: 80px;">
                    <option value="start">Left</option>
                    <option value="center">Center</option>
                    <option value="end">Right</option>
                </select>
            </div>
            <board-setting-block
                v-if="tb_meta.primary_view === 'board_view'"
                :tb_meta="tb_meta"
                :board_settings="tb_meta"
                class="form-group--min"
                @val-changed="setSetting"
            ></board-setting-block>
        </template>

        <template v-if="isOwner">
            <template v-if="!filter_for_field">
                <div v-show="tb_meta.primary_view === 'list_view'"
                     class="flex flex--center flex--space form-group"
                     :style="{width: max_set_len+'px'}"
                >
                    <label>Listing Field:</label>
                    <select class="form-control l-inl-control"
                            @change="propChanged('listing_fld_id')"
                            v-model="tb_meta.listing_fld_id"
                            :style="textSysStyle">
                        <option v-for="hdr in tb_meta._fields" :value="hdr.id">{{ hdr.name }}</option>
                    </select>

                    <label>&nbsp;&nbsp;&nbsp;Rows Width, px:</label>
                    <input class="form-control l-inl-control inl-sm"
                           @change="propChanged('listing_rowswi')"
                           v-model="tb_meta.listing_rowswi"
                           :style="textSysStyle"/>
                </div>

                <div class="flex flex--center flex--space form-group"
                     :style="{width: max_set_len+'px'}"
                >
                    <label class="">Default # of rows per page:</label>
                    <select class="form-control l-inl-control"
                            @change="propChanged('rows_per_page')"
                            v-model="tb_meta.rows_per_page"
                            :style="textSysStyle">
                        <option>10</option>
                        <option>20</option>
                        <option>50</option>
                        <option>100</option>
                        <option>200</option>
                    </select>
                </div>

                <!--Theme settings-->
                <div class="form-group--min" :style="{width: max_set_len+'px'}">
                    <label>Color settings</label> (overwrite account theme settings):
                </div>
                <table-settings-colors-table class="form-group table-settings-colors m_l"
                                             :tb_theme="tb_theme"
                                             @prop-changed="propChanged"
                                             :style="{width: (max_set_len-50)+'px'}"
                ></table-settings-colors-table>
                <!--Theme settings-->
            </template>

            <div class="form-group--min flex" :style="{width: max_set_len+'px'}">
                <span class="indeterm_check__wrap pub-check">
                    <span class="indeterm_check"
                          @click="tb_meta.edit_one_click = !tb_meta.edit_one_click;propChanged('edit_one_click')"
                          :style="checkboxSys"
                    >
                        <i v-if="tb_meta.edit_one_click" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <label class="wrap ml5">One-click to enter editing-mode at empty cell.</label>
            </div>
            <template v-if="!filter_for_field">
                <div class="form-group--min flex" :style="{width: max_set_len+'px'}">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.autoload_new_data = !tb_meta.autoload_new_data;propChanged('autoload_new_data')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.autoload_new_data" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label class="wrap ml5">Auto-reload "Grid View" when change(s) made by others.</label>
                </div>
                <div class="form-group--min flex" :style="{width: max_set_len+'px'}">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.pub_hidden = !tb_meta.pub_hidden;propChanged('pub_hidden')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.pub_hidden" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label class="wrap ml5">Hidden for "Public" sharing.</label>
                </div>
                <div class="form-group--min flex flex--center-v" :style="{width: max_set_len+'px'}">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.filters_on_top = !tb_meta.filters_on_top;propChanged('filters_on_top')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.filters_on_top" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label class="wrap ml5">Filters on top.</label>
                    <label v-if="tb_meta.filters_on_top">&nbsp;&nbsp;&nbsp;Position:</label>
                    <select v-if="tb_meta.filters_on_top"
                            class="form-control l-inl-control inl-sm"
                            @change="propChanged('filters_ontop_pos')"
                            v-model="tb_meta.filters_ontop_pos"
                            :style="textSysStyle">
                        <option value="start">Left</option>
                        <option value="center">Center</option>
                        <option value="end">Right</option>
                    </select>
                </div>
                <div class="form-group--min flex" :style="{width: max_set_len+'px'}">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.enabled_activities = !tb_meta.enabled_activities;propChanged('enabled_activities')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.enabled_activities" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label class="wrap ml5">Turn on "Activities".</label>
                </div>
                <div class="form-group--min flex" :style="{width: max_set_len+'px'}">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.mirror_edited_underline = !tb_meta.mirror_edited_underline;propChanged('mirror_edited_underline')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.mirror_edited_underline" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label class="wrap ml5">Underline changed values in mirrored cells.</label>
                </div>
                <div class="form-group--min flex" :style="{width: max_set_len+'px'}">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.no_span_data_cell_in_popup = !tb_meta.no_span_data_cell_in_popup;propChanged('no_span_data_cell_in_popup')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.no_span_data_cell_in_popup" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label class="wrap ml5">Same width for fields with and without right-side components (units, history icon, etc).</label>
                </div>
                <div class="form-group flex" :style="{width: max_set_len+'px'}">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.refill_auto_oncopy = !tb_meta.refill_auto_oncopy;propChanged('refill_auto_oncopy')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.refill_auto_oncopy" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label class="wrap ml5">Recreate the values for "Auto String" fields for copied records.</label>
                </div>
            </template>
        </template><!-- is owner -->

        <div v-if="isOwnerOrCanEdit('can_edit_tb')"
             class="flex flex--center flex--space form-group"
             :style="{width: max_set_len+'px'}"
        >
            <label class="">Initial loading:</label>
            <select class="form-control l-inl-control"
                    @change="propChanged('initial_view_id')"
                    v-model="tb_cur_settings.initial_view_id"
                    :style="textSysStyle">
                <option :value="-3" style="color: #00F">Entire Table (Available Range)</option>
                <option :value="-2" style="color: #00F">Blank (No Data)</option>
                <option :value="-1" style="color: #00F">Last Exit State</option>
                <option disabled="disabled">Views:</option>
                <option v-for="view in tb_views" :value="view.id">&nbsp;&nbsp;&nbsp;&nbsp;{{ view.name }}</option>
            </select>
        </div>

        <div v-if="isOwnerOrCanEdit('can_edit_tb')"
             class="flex flex--center flex--space form-group"
             :style="{width: max_set_len+'px'}"
        >
            <label class="">App link value field:</label>
            <select class="form-control l-inl-control"
                    @change="propChanged('multi_link_app_fld_id')"
                    v-model="tb_meta.multi_link_app_fld_id"
                    :style="textSysStyle">
                <option :value="null"></option>
                <option v-for="fld in tb_meta._fields"
                        v-if="$root.systemFields.indexOf(fld.field) === -1"
                        :value="fld.id"
                >{{ $root.uniqName(fld.name) }}</option>
            </select>
        </div>

        <template v-if="isOwner">
            <div class="form-group">
                <label class="form-group--min">Pop-up/DCR form view of single record:</label>
                <div class="m_l form-group--min">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check"
                              @click="propChanged('vert_tb_bgcolor',tb_meta)"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.vert_tb_bgcolor" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <span>Apply table header BKGD color.</span>
                </div>
                <div class="m_l form-group--min">
                    <span>Header width:</span>
                    <input class="form-control l-inl-control l-inl-control--nano"
                           type="number"
                           @change="propChanged('vert_tb_hdrwidth')"
                           v-model="tb_meta.vert_tb_hdrwidth"
                           :style="textSysStyle">
                    <span>%</span>
                </div>
                <div class="m_l form-group--min">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check"
                              @click="propChanged('vert_tb_floating',tb_meta)"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.vert_tb_floating" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <span>Apply Floating settings to columns.</span>
                </div>
                <div class="m_l form-group--min">
                    <span>Row spacing:</span>
                    <input class="form-control l-inl-control l-inl-control--nano"
                           type="number"
                           @change="propChanged('vert_tb_rowspacing')"
                           v-model="tb_meta.vert_tb_rowspacing"
                           :style="textSysStyle">
                    <span>px</span>
                </div>
                <div class="m_l form-group--min">
                    <span>Width (px):</span>
                    <input class="form-control l-inl-control l-inl-control--nano"
                           style="max-width: 80px"
                           placeholder="Default"
                           title="Default"
                           type="number"
                           @change="propChanged('vert_tb_width_px')"
                           v-model="tb_meta.vert_tb_width_px"
                           :style="textSysStyle">
                    <input class="form-control l-inl-control l-inl-control--nano"
                           style="max-width: 80px"
                           placeholder="Min"
                           title="Min"
                           type="number"
                           @change="propChanged('vert_tb_width_px_min')"
                           v-model="tb_meta.vert_tb_width_px_min"
                           :style="textSysStyle">
                    <input class="form-control l-inl-control l-inl-control--nano"
                           style="max-width: 80px"
                           placeholder="Max"
                           title="Max"
                           type="number"
                           @change="propChanged('vert_tb_width_px_max')"
                           v-model="tb_meta.vert_tb_width_px_max"
                           :style="textSysStyle">
                </div>
                <div class="m_l form-group--min">
                    <span>Height (%):</span>
                    <input class="form-control l-inl-control l-inl-control--nano"
                           style="max-width: 80px"
                           placeholder="Default"
                           title="Default"
                           type="number"
                           @change="propChanged('vert_tb_height')"
                           v-model="tb_meta.vert_tb_height"
                           :style="textSysStyle">
                    <input class="form-control l-inl-control l-inl-control--nano"
                           style="max-width: 80px"
                           placeholder="Min"
                           title="MIn"
                           type="number"
                           @change="propChanged('vert_tb_height_min')"
                           v-model="tb_meta.vert_tb_height_min"
                           :style="textSysStyle">
                    <input class="form-control l-inl-control l-inl-control--nano"
                           style="max-width: 80px"
                           placeholder="Max"
                           title="Max"
                           type="number"
                           @change="propChanged('vert_tb_height_max')"
                           v-model="tb_meta.vert_tb_height_max"
                           :style="textSysStyle">
                </div>
            </div>

            <div class="form-group" :style="{width: max_set_len+'px'}">
                <div><b>"User" value display settings:</b></div>
                <div>
                    <table class="tablda-like m_l">
                        <tr>
                            <th rowspan="3">Uses</th>
                            <th colspan="5">Components</th>
                        </tr>
                        <tr>
                            <th rowspan="2">Image</th>
                            <th colspan="2">Names</th>
                            <th rowspan="2">Email</th>
                            <th rowspan="2">Username</th>
                        </tr>
                        <tr>
                            <th>First</th>
                            <th>Last</th>
                        </tr>
                        <!--<tr v-if="hasUserField">-->
                        <tr>
                            <th>Table Field</th>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_image',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_image" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_first',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_first" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_last',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_last" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_email',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_email" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_username',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_username" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                        </tr>
                        <!--<tr v-if="hasUserField">-->
                        <tr>
                            <th>Data History</th>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_image',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_image" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_first',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_first" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_last',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_last" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_email',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_email" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_username',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_username" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                        </tr>
                        <!--<tr v-if="hasVoteField">-->
                        <tr>
                            <th>Vote</th>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_image',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.vote_user_show_image" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_first',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.vote_user_show_first" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_last',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.vote_user_show_last" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_email',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.vote_user_show_email" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_username',tb_cur_settings,sunc_settings)"
                                          :style="checkboxSys"
                                    >
                                        <i v-if="tb_cur_settings.vote_user_show_username" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="form-group" :style="{width: max_set_len+'px'}">
                <table class="tb-rows-padding full-width">
                    <tr v-if="presentMirror">
                        <td>
                            <label class="">Max. # of records that can be mirrored from source table for one record:</label>
                        </td>
                        <td class="txt--right">
                            <input class="form-control l-inl-control l-inl-control--min"
                                   type="number"
                                   @change="propChanged('max_mirrors_in_one_row')"
                                   v-model="tb_meta.max_mirrors_in_one_row"
                                   :style="textSysStyle">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label class="">Default # of records per page in the pop-up panel for links:</label>
                        </td>
                        <td class="txt--right">
                            <input class="form-control l-inl-control l-inl-control--min no-margin"
                                   type="number"
                                   @change="propChanged('max_rows_in_link_popup')"
                                   v-model="tb_meta.max_rows_in_link_popup"
                                   :style="textSysStyle">
                        </td>
                    </tr>
                    <template v-if="!filter_for_field">
                        <tr>
                            <td>
                                <label class="">Max. # of results to be displayed for search:</label>
                            </td>
                            <td class="txt--right">
                                <input class="form-control l-inl-control l-inl-control--min no-margin"
                                       type="number"
                                       @change="propChanged('search_results_len')"
                                       v-model="tb_meta.search_results_len"
                                       :style="textSysStyle">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label class="">Max. # of items that can be displayed in a filter:</label>
                            </td>
                            <td class="txt--right">
                                <input class="form-control l-inl-control l-inl-control--min no-margin"
                                       type="number"
                                       @change="propChanged('max_filter_elements')"
                                       v-model="tb_meta.max_filter_elements"
                                       :style="textSysStyle">
                            </td>
                        </tr>
                    </template>
                </table>
            </div>

            <template v-if="!filter_for_field">
                <div class="address-items form-group" v-show="presentAddress" :style="{width: max_set_len+'px'}">
                    <div class="address-top">
                        <b>Auto populating the value of "Address" type field</b>
                        <select class="form-control l-inl-control"
                                :disabled="!presentAddress"
                                @change="propChanged('address_fld__source_id')"
                                v-model="tb_meta.address_fld__source_id"
                                :style="textSysStyle">
                            <option :value="null"></option>
                            <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type === 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                        </select>
                        <label>to columns:</label>
                    </div>
                    <div class="flex form-group--min m_l">
                        <label class="flex flex--center half-flex flexo-width-25-75">
                            <span>Street #:</span>
                            <select class="form-control l-inl-control"
                                    :disabled="!tb_meta.address_fld__source_id"
                                    @change="propChanged('address_fld__street_address')"
                                    v-model="tb_meta.address_fld__street_address"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                        <label class="flex flex--center half-flex flexo-width-35-75">
                            <span>Name:</span>
                            <select class="form-control l-inl-control"
                                    :disabled="!tb_meta.address_fld__source_id"
                                    @change="propChanged('address_fld__street')"
                                    v-model="tb_meta.address_fld__street"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                    </div>
                    <div class="flex form-group--min m_l">
                        <label class="flex flex--center half-flex flexo-width-25-75">
                            <span>City:</span>
                            <select class="form-control l-inl-control"
                                    :disabled="!tb_meta.address_fld__source_id"
                                    @change="propChanged('address_fld__city')"
                                    v-model="tb_meta.address_fld__city"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                        <label class="flex flex--center half-flex flexo-width-35-75">
                            <span>State:</span>
                            <select class="form-control l-inl-control"
                                    :disabled="!tb_meta.address_fld__source_id"
                                    @change="propChanged('address_fld__state')"
                                    v-model="tb_meta.address_fld__state"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                    </div>
                    <div class="flex form-group--min m_l">
                        <label class="flex flex--center half-flex flexo-width-25-75">
                            <span>Zipcode:</span>
                            <select class="form-control l-inl-control"
                                    :disabled="!tb_meta.address_fld__source_id"
                                    @change="propChanged('address_fld__zipcode')"
                                    v-model="tb_meta.address_fld__zipcode"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                        <label class="flex flex--center half-flex flexo-width-35-75">
                            <span>Country:</span>
                            <select class="form-control l-inl-control"
                                    :disabled="!tb_meta.address_fld__source_id"
                                    @change="propChanged('address_fld__country')"
                                    v-model="tb_meta.address_fld__country"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                    </div>
                    <div class="flex form-group--min m_l">
                        <label class="flex flex--center half-flex flexo-width-25-75">
                            <span>Latitude:</span>
                            <select class="form-control l-inl-control"
                                    :disabled="!tb_meta.address_fld__source_id"
                                    @change="propChanged('address_fld__lat')"
                                    v-model="tb_meta.address_fld__lat"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                        <label class="flex flex--center half-flex flexo-width-35-75">
                            <span>Longitude:</span>
                            <select class="form-control l-inl-control"
                                    :disabled="!tb_meta.address_fld__source_id"
                                    @change="propChanged('address_fld__long')"
                                    v-model="tb_meta.address_fld__long"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                    </div>
                    <div class="flex form-group--min m_l">
                        <label class="flex flex--center half-flex flexo-width-25-75">
                            <span>County:</span>
                            <select class="form-control l-inl-control"
                                    :disabled="!tb_meta.address_fld__source_id"
                                    @change="propChanged('address_fld__countyarea')"
                                    v-model="tb_meta.address_fld__countyarea"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                        <label class="flex flex--center half-flex flexo-width-35-75">
                        </label>
                    </div>
                </div>
            </template>

            <div class="unit-conv-items form-group"
                 v-show="presentUnitConv && $root.checkAvailable($root.user, 'unit_conversions')"
                 :style="{width: max_set_len+'px'}"
            >
                <div class="form-group--min">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.unit_conv_is_active = !tb_meta.unit_conv_is_active;propChanged('unit_conv_is_active')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.unit_conv_is_active" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label> Activate Unit Conversion (UC).</label>
                </div>
                <div class="m_l form-group--min" v-if="tb_meta.unit_conv_is_active">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.unit_conv_by_user = !tb_meta.unit_conv_by_user;propChanged('unit_conv_by_user')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.unit_conv_by_user" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label> User defined conversion.</label>
                </div>
                <template v-if="tb_meta.unit_conv_is_active && tb_meta.unit_conv_by_user && $root.private_menu_tree && $root.account_menu_tree">
                    <div class="m_l">
                        <label>Select table defining conversion:</label>
                    </div>
                    <div class="form-group--min flex m_l">
                        <table class="tablda-like full-width" style="height: 32px;">
                            <tr>
                                <td @click="edit_conv_table = !edit_conv_table">

                                    <select-with-folder-structure
                                            v-if="edit_conv_table"
                                            :empty_val="true"
                                            :for_single_select="true"
                                            :cur_val="tb_meta.unit_conv_table_id"
                                            :available_tables="$root.settingsMeta.available_tables"
                                            :user="$root.user"
                                            @sel-changed="(val) => {this.tb_meta.unit_conv_table_id = val;}"
                                            @sel-closed="propChanged('unit_conv_table_id');edit_conv_table=false;"
                                            class="form-control"
                                    ></select-with-folder-structure>

                                    <div v-else="" >
                                        <a target="_blank" :href="showUnitTb('__visiting_url')" @click.stop="">{{ showUnitTb() }}</a>
                                        <a v-if="refIsOwner" target="_blank" :href="showUnitTb('__url')" @click.stop="">(Table)</a>
                                    </div>

                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="m_l">
                        <label>Correspondence:</label>
                    </div>
                    <div class="m_l">
                        <table class="tablda-like full-width">
                            <thead>
                                <tr>
                                    <th colspan="2">Units</th>
                                    <th rowspan="2">Operator</th>
                                    <th rowspan="2">Factor</th>
                                    <th colspan="2">Formula</th>
                                </tr>
                                <tr>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Forward</th>
                                    <th>Reverse</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select class="form-control l-inl-control"
                                                :disabled="!unitConvTable"
                                                @change="propChanged('unit_conv_from_fld_id')"
                                                v-model="tb_meta.unit_conv_from_fld_id"
                                                :style="textSysStyle">
                                            <option :value="null"></option>
                                            <template v-if="unitConvTable">
                                                <option v-for="hdr in unitConvTable._fields" :value="hdr.id">{{ hdr.name }}</option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control l-inl-control"
                                                :disabled="!unitConvTable"
                                                @change="propChanged('unit_conv_to_fld_id')"
                                                v-model="tb_meta.unit_conv_to_fld_id"
                                                :style="textSysStyle">
                                            <option :value="null"></option>
                                            <template v-if="unitConvTable">
                                                <option v-for="hdr in unitConvTable._fields" :value="hdr.id">{{ hdr.name }}</option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control l-inl-control"
                                                :disabled="!unitConvTable"
                                                @change="propChanged('unit_conv_operator_fld_id')"
                                                v-model="tb_meta.unit_conv_operator_fld_id"
                                                :style="textSysStyle">
                                            <option :value="null"></option>
                                            <template v-if="unitConvTable">
                                                <option v-for="hdr in unitConvTable._fields" :value="hdr.id">{{ hdr.name }}</option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control l-inl-control"
                                                :disabled="!unitConvTable"
                                                @change="propChanged('unit_conv_factor_fld_id')"
                                                v-model="tb_meta.unit_conv_factor_fld_id"
                                                :style="textSysStyle">
                                            <option :value="null"></option>
                                            <template v-if="unitConvTable">
                                                <option v-for="hdr in unitConvTable._fields" :value="hdr.id">{{ hdr.name }}</option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control l-inl-control"
                                                :disabled="!unitConvTable"
                                                @change="propChanged('unit_conv_formula_fld_id')"
                                                v-model="tb_meta.unit_conv_formula_fld_id"
                                                :style="textSysStyle">
                                            <option :value="null"></option>
                                            <template v-if="unitConvTable">
                                                <option v-for="hdr in unitConvTable._fields" :value="hdr.id">{{ hdr.name }}</option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control l-inl-control"
                                                :disabled="!unitConvTable"
                                                @change="propChanged('unit_conv_formula_reverse_fld_id')"
                                                v-model="tb_meta.unit_conv_formula_reverse_fld_id"
                                                :style="textSysStyle">
                                            <option :value="null"></option>
                                            <template v-if="unitConvTable">
                                                <option v-for="hdr in unitConvTable._fields" :value="hdr.id">{{ hdr.name }}</option>
                                            </template>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
                <div class="m_l form-group--min" v-if="tb_meta.unit_conv_is_active">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.unit_conv_by_system = !tb_meta.unit_conv_by_system;propChanged('unit_conv_by_system')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.unit_conv_by_system" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>
                        <span>Use</span>
                        <a target="_blank" :href="$root.user._uc_sys_visiting_url">system</a>
                        <span>conversion (units need to exactly match).</span>
                    </label>
                </div>
                <div class="m_l form-group--min" v-if="tb_meta.unit_conv_is_active">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.unit_conv_by_lib = !tb_meta.unit_conv_by_lib;propChanged('unit_conv_by_lib')"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta.unit_conv_by_lib" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>
                        <span>Use</span>
                        <a target="_blank" href="https://www.npmjs.com/package/convert-units#supported-units">third party</a>
                        <span>conversion (units need to exactly match).</span>
                    </label>
                </div>
            </div>

            <div class="form-group--min">
                <div class="form-group--min">
                    <label>Select Grid View (display) engine:</label>
                </div>
                <div>
                    <input type="radio" v-model="tb_meta.table_engine" :value="'default'" @change="setSetting('table_engine','default')"/>
                    <label>Regular</label>
                </div>
                <div>
                    <input type="radio" v-model="tb_meta.table_engine" :value="'virtual'" @change="setSetting('table_engine','virtual')"/>
                    <label>Virtual Scroll</label>
                </div>
                <div class="ml15 flex flex--space">
                    <div class="flex flex--center" style="white-space: normal;">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check"
                                      @click="propChanged('auto_enable_virtual_scroll',tb_meta)"
                                      :style="checkboxSys"
                                >
                                    <i v-if="tb_meta.auto_enable_virtual_scroll" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                        <span>&nbsp;Auto-enable when visible cells >= </span>
                    </div>
                    <input class="form-control l-inl-control inl-sm"
                           @change="propChanged('auto_enable_virtual_scroll_when')"
                           v-model="tb_meta.auto_enable_virtual_scroll_when"
                           :style="textSysStyle"
                           style="min-width: 60px;"/>
                </div>
                <div>
                    <input type="radio" v-model="tb_meta.table_engine" :value="'vue_virtual'" @change="setSetting('table_engine','vue_virtual')"/>
                    <label>Vue Virtual Scroll</label>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import CellStyleMixin from "./../_Mixins/CellStyleMixin.vue";

    import TableSettingsColorsTable from "./TableSettingsColorsTable.vue";
    import HoverBlock from "./HoverBlock";
    import SelectWithFolderStructure from "../CustomCell/InCell/SelectWithFolderStructure";
    import BoardSettingBlock from "./BoardSettingBlock";

    export default {
        name: 'TableSettingsModule',
        mixins: [
            CellStyleMixin,
        ],
        components: {
            BoardSettingBlock,
            HoverBlock,
            TableSettingsColorsTable,
            SelectWithFolderStructure,
        },
        data() {
            return {
                edit_conv_table: false,
            }
        },
        computed: {
            hasUserField() {
                return this.hasField('User');
            },
            hasVoteField() {
                return this.hasField('Vote');
            },
            presentAddress() {
                return _.find(this.tb_meta._fields, (hdr) => { return hdr.f_type === 'Address' });
            },
            presentMirror() {
                return _.find(this.tb_meta._fields, (hdr) => { return hdr.input_type === 'Mirror' });
            },
            presentUnitConv() {
                return _.find(this.tb_meta._fields, (hdr) => { return !!hdr.unit_ddl_id });
            },
            unitConvTable() {
                if (this.tb_meta && this.tb_meta._ref_conditions && this.tb_meta.unit_conv_table_id) {
                    let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tb_meta.unit_conv_table_id)});
                    return tb || null;
                } else {
                    return null;
                }
            },
            isOwner() {
                return this.tb_meta._is_owner && !this.type;
            },
            refIsOwner() {
                let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tb_meta.unit_conv_table_id)});
                return tb && tb.user_id == this.$root.user.id;
            },
        },
        props: {
            tableMeta: Object,//style mixin
            tb_meta: Object,
            tb_theme: Object,
            tb_cur_settings: Object,
            sunc_settings: Object,
            tb_views: Array,
            type: String,
            max_set_len: Number,
            filter_for_field: String,
        },
        methods: {
            isOwnerOrCanEdit(permission) {
                if (this.type) {
                    return false;
                }
                return this.tb_meta._is_owner
                    || (this.tb_meta._current_right && this.tb_meta._current_right[permission]);
            },
            hasField(field) {
                return this.tb_meta
                    &&
                    this.tb_meta._fields
                    &&
                    _.find(this.tb_meta._fields, (fld) => {
                        return !in_array(fld.field, this.$root.systemFields) && fld.f_type === field;
                    });
            },
            showUnitTb(link) {
                let res = this.tb_meta.unit_conv_table_id;
                let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tb_meta.unit_conv_table_id)});
                if (link) {
                    res = tb ? tb[link] : res;
                } else {
                    res = tb ? tb.name : res;
                }
                return res;
            },
            setSetting(prop_name, val) {
                this.tb_meta[prop_name] = val;
                this.$emit('prop-changed', prop_name);
            },
            propChanged(prop_name, obj, sync_obj) {
                $.LoadingOverlay('show');
                window.setTimeout(() => {
                    if (obj) {
                        obj[prop_name] = !obj[prop_name];
                        (sync_obj ? sync_obj[prop_name] = obj[prop_name] : null);
                    }

                    if (prop_name === 'max_mirrors_in_one_row') {
                        this.tb_meta.max_mirrors_in_one_row = Math.max(this.tb_meta.max_mirrors_in_one_row, 1);
                        this.tb_meta.max_mirrors_in_one_row = Math.min(this.tb_meta.max_mirrors_in_one_row, 100);
                    }
                    if (prop_name === 'max_rows_in_link_popup') {
                        this.tb_meta.max_rows_in_link_popup = Math.max(this.tb_meta.max_rows_in_link_popup, 1);
                        this.tb_meta.max_rows_in_link_popup = Math.min(this.tb_meta.max_rows_in_link_popup, 500);
                    }
                    if (prop_name === 'search_results_len') {
                        this.tb_meta.search_results_len = Math.max(this.tb_meta.search_results_len, 1);
                        this.tb_meta.search_results_len = Math.min(this.tb_meta.search_results_len, 100);
                    }
                    if (prop_name === 'max_filter_elements') {
                        this.tb_meta.max_filter_elements = Math.max(this.tb_meta.max_filter_elements, 10);
                        this.tb_meta.max_filter_elements = Math.min(this.tb_meta.max_filter_elements, 3000);
                    }

                    if (prop_name === 'vert_tb_width_px_min') {
                        this.tb_meta.vert_tb_width_px_min = Math.max(this.tb_meta.vert_tb_width_px_min, 200);
                    }
                    if (prop_name === 'vert_tb_width_px') {
                        this.tb_meta.vert_tb_width_px = Math.max(this.tb_meta.vert_tb_width_px, this.tb_meta.vert_tb_width_px_min);
                    }
                    if (prop_name === 'linkd_tb_width_px_min') {
                        this.tb_meta.linkd_tb_width_px_min = Math.max(this.tb_meta.linkd_tb_width_px_min, 200);
                    }
                    if (prop_name === 'linkd_tb_width_px') {
                        this.tb_meta.linkd_tb_width_px = Math.max(this.tb_meta.linkd_tb_width_px, this.tb_meta.linkd_tb_width_px_min);
                    }
                    if (prop_name === 'vert_tb_height_min') {
                        this.tb_meta.vert_tb_height_min = Math.max(this.tb_meta.vert_tb_height_min, 20);
                    }
                    if (prop_name === 'vert_tb_height') {
                        this.tb_meta.vert_tb_height = Math.max(this.tb_meta.vert_tb_height, this.tb_meta.vert_tb_height_min);
                    }
                    if (prop_name === 'linkd_tb_height_min') {
                        this.tb_meta.linkd_tb_height_min = Math.max(this.tb_meta.linkd_tb_height_min, 20);
                    }
                    if (prop_name === 'linkd_tb_height') {
                        this.tb_meta.linkd_tb_height = Math.max(this.tb_meta.linkd_tb_height, this.tb_meta.linkd_tb_height_min);
                    }

                    if (prop_name === 'name') {
                        this.tb_meta.name = SpecialFuncs.safeTableName(this.tb_meta.name);
                    }
                    this.$emit('prop-changed', prop_name);

                    if (prop_name === 'initial_view_id') {
                        eventBus.$emit('save-table-status');
                    }
                    if (prop_name === 'add_report' || prop_name === 'add_bi') {
                        eventBus.$emit('run-after-load-changes');
                    }
                    $.LoadingOverlay('hide');
                }, 50);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import './../CommonBlocks/TabldaLike';

    .inl-sm {
        padding: 3px 5px;
        height: 28px;
        width: 100px;
    }

    .m_l {
        margin-left: 20px;
    }

    .form-group, .form-group--min {
        white-space: nowrap;
        break-inside: avoid;
    }

    .form-group--min {
        margin-bottom: 5px;
    }

    .pub-check {
        /*margin: 0 12px 0 0;*/
        position: relative;
    }

    label {
        margin: 0;
    }

    .tb-rows-padding {
        td {
            padding-bottom: 5px;
            white-space: normal;
        }
    }

    .table-settings-colors {
        width: calc(100% - 30px) !important;
    }

    .tablda-like {
        th {
            padding: 1px 3px;
        }
    }
    .center {
        text-align: center;
    }

    .address-items {
        .half-flex {
            width: 50%;
            margin: 0 5px;
            font-weight: normal;
        }
        .flexo-width-35-75 {
            span {
                width: 35%;
            }
            select {
                width: 75%;
            }
        }
        .flexo-width-25-75 {
            span {
                width: 25%;
            }
            select {
                width: 75%;
            }
        }

        .left-pad {
            padding-left: 30px;
        }
        .address-top {
            margin-bottom: 5px;
            white-space: normal;

            select {
                margin-left: 5px;
                margin-right: 5px;
                max-width: 25%;
            }
        }
    }

    .unit-conv-items {
        select {
            margin: 0;
        }
    }

    select {
        option {
            white-space: nowrap;
            display: block;
            margin: 0;
            font-size: 1em;
            line-height: 1.1em;
        }
    }
</style>
<style lang="scss">
    .form-group--min {
        .select2-container {
            width: 100%;
            max-width: 100%;
        }
    }
</style>