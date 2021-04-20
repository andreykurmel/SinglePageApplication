<template>
    <div :style="textStyle">
        <div v-if="(isOwner || type === 'new') && !filter_for_field"
             class="flex flex--center flex--space form-group"
             :style="{width: max_set_len+'px'}"
        >
            <label class="">Table Name:</label>
            <input class="form-control l-inl-control"
                   type="text"
                   @change="propChanged('name')"
                   v-model="tb_meta.name"
                   :style="textStyle">
        </div>

        <template v-if="isOwner">
            <template v-if="!filter_for_field">
                <div class="flex flex--center flex--space form-group"
                     :style="{width: max_set_len+'px'}"
                >
                    <label class="">Default # of rows per page:</label>
                    <select class="form-control l-inl-control"
                            @change="propChanged('rows_per_page')"
                            v-model="tb_meta.rows_per_page"
                            :style="textStyle">
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

            <div class="form-group--min" :style="{width: max_set_len+'px'}">
                <span class="indeterm_check__wrap pub-check">
                    <span class="indeterm_check"
                          @click="tb_meta.edit_one_click = !tb_meta.edit_one_click;propChanged('edit_one_click')"
                          :style="$root.checkBoxStyle"
                    >
                        <i v-if="tb_meta.edit_one_click" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <label> One-click to enter cell edit-mode.</label>
            </div>
            <template v-if="!filter_for_field">
                <div class="form-group--min" :style="{width: max_set_len+'px'}">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.autoload_new_data = !tb_meta.autoload_new_data;propChanged('autoload_new_data')"
                              :style="$root.checkBoxStyle"
                        >
                            <i v-if="tb_meta.autoload_new_data" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label> Auto-reload "List View" when change(s) made by others.</label>
                </div>
                <div class="form-group" :style="{width: max_set_len+'px'}">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.pub_hidden = !tb_meta.pub_hidden;propChanged('pub_hidden')"
                              :style="$root.checkBoxStyle"
                        >
                            <i v-if="tb_meta.pub_hidden" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label> Hidden for "Public" sharing.</label>
                </div>

                <div class="input-left-items form-group" :style="{width: max_set_len+'px'}">
                    <div><b>Add-on features:</b></div>
                    <div class="form-group--min m_l">
                        <label>
                            <span class="indeterm_check__wrap pub-check">
                                <span class="indeterm_check"
                                      :class="{'disabled': !userHasAddon('map')}"
                                      @click="!userHasAddon('map')
                                          ? null
                                          : propChanged('add_map',tb_meta)"
                                      :style="$root.checkBoxStyle"
                                >
                                    <i v-if="tb_meta.add_map" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>Geospatial Information (GSI)</span>
                        </label>
                    </div>
                    <div class="form-group--min m_l" v-if="tb_meta.add_map || presentAddress">
                        <div class="m_l" :class="[tb_meta.api_key_mode === 'table' ? 'form-group--min' : '']">
                            <label>Use</label>
                            <select class="form-control l-inl-control"
                                    @change="propChanged('api_key_mode')"
                                    v-model="tb_meta.api_key_mode"
                                    :style="textStyle"
                                    style="width: 130px;padding: 0;"
                            >
                                <option value="table">Table specific</option>
                                <option value="account">Account</option>
                            </select>
                            <label class="">
                                <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Google API Key</a>
                            </label>
                            <template v-if="tb_meta.api_key_mode === 'account'">
                                <select class="form-control l-inl-control"
                                        @change="propChanged('account_api_key_id')"
                                        v-model="tb_meta.account_api_key_id"
                                        :style="textStyle"
                                        style="width: 100px;padding: 0;"
                                >
                                    <option :value="null">No API Key</option>
                                    <option v-for="(kkey,kk) in $root.user._google_api_keys" :value="kkey.id">#{{ kk+1 }}</option>
                                </select>
                                <span>.</span>
                            </template>
                            <span v-if="tb_meta.api_key_mode === 'table'">Enter below:</span>
                        </div>
                        <div v-if="tb_meta.api_key_mode === 'table'" class="flex flex--center">
                            <input class="form-control l-inl-control"
                                   @click="hide_api = false"
                                   @change="propChanged('google_api_key');setApidots();hide_api = true;"
                                   v-model="hide_api ? api_dots : tb_meta.google_api_key"
                                   :style="textStyle"/>
                            <button v-if="tb_meta.google_api_key" class="btn btn-danger btn-sm" @click="removeGlApi()">&times;</button>
                            <i class="fa fa-eye" :style="{color: hide_api ? '' : '#F00'}" @click="hide_api = !hide_api"></i>
                            <i class="fa fa-info-circle" ref="fahelp" @click="showHover"></i>
                            <hover-block v-if="help_tooltip"
                                         :html_str="$root.google_help"
                                         :p_left="help_left"
                                         :p_top="help_top"
                                         :c_offset="help_offset"
                                         @another-click="help_tooltip = false"
                            ></hover-block>
                        </div>
                    </div>
                    <div class="form-group--min m_l">
                        <label>
                            <span class="indeterm_check__wrap pub-check">
                                <span class="indeterm_check"
                                      :class="{'disabled': !userHasAddon('bi')}"
                                      @click="!userHasAddon('bi')
                                          ? null
                                          : propChanged('add_bi',tb_meta)"
                                      :style="$root.checkBoxStyle"
                                >
                                    <i v-if="tb_meta.add_bi" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>Business Intelligence (BI)</span>
                        </label>
                    </div>
                    <div class="form-group--min m_l">
                        <label>
                            <span class="indeterm_check__wrap pub-check">
                                <span class="indeterm_check"
                                      :class="{'disabled': !userHasAddon('request')}"
                                      @click="!userHasAddon('request')
                                          ? null
                                          : propChanged('add_request',tb_meta)"
                                      :style="$root.checkBoxStyle"
                                >
                                    <i v-if="tb_meta.add_request" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>Data Collection & Request (DCR)</span>
                        </label>
                    </div>
                    <div class="form-group--min m_l">
                        <label>
                            <span class="indeterm_check__wrap pub-check">
                                <span class="indeterm_check"
                                      :class="{'disabled': !userHasAddon('alert')}"
                                      @click="!userHasAddon('alert')
                                          ? null
                                          : propChanged('add_alert',tb_meta)"
                                      :style="$root.checkBoxStyle"
                                >
                                    <i v-if="tb_meta.add_alert" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>A&amp;N (Alerts &amp; Notifications)</span>
                        </label>
                    </div>
                    <div class="form-group--min m_l flex flex--center-v">
                        <div>
                            <label>
                                <span class="indeterm_check__wrap pub-check">
                                    <span class="indeterm_check"
                                          :class="{'disabled': !userHasAddon('kanban')}"
                                          @click="!userHasAddon('kanban')
                                              ? null
                                              : propChanged('add_kanban',tb_meta)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_meta.add_kanban" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <span>Kanban</span>
                            </label>
                        </div>
                        <div class="m_l">
                            <label>
                                <span class="indeterm_check__wrap pub-check">
                                    <span class="indeterm_check"
                                          :class="{'disabled': !userHasAddon('kanban')}"
                                          @click="!userHasAddon('kanban')
                                              ? null
                                              : propChanged('add_gantt',tb_meta)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_meta.add_gantt" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <span>Gantt</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group--min m_l">
                        <label>
                            <span class="indeterm_check__wrap pub-check">
                                <span class="indeterm_check"
                                      :class="{'disabled': !userHasAddon('email')}"
                                      @click="!userHasAddon('email')
                                          ? null
                                          : propChanged('add_email',tb_meta)"
                                      :style="$root.checkBoxStyle"
                                >
                                    <i v-if="tb_meta.add_email" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>Email</span>
                        </label>
                    </div>
                    <div class="form-group--min m_l">
                        <label>
                            <span class="indeterm_check__wrap pub-check">
                                <span class="indeterm_check"
                                      :class="{'disabled': !userHasAddon('calendar')}"
                                      @click="!userHasAddon('calendar')
                                          ? null
                                          : propChanged('add_calendar',tb_meta)"
                                      :style="$root.checkBoxStyle"
                                >
                                    <i v-if="tb_meta.add_calendar" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>Calendar</span>
                        </label>
                    </div>
                </div>
            </template>
        </template><!-- is owner -->

        <div v-if="isOwnerOrCanEdit && type !== 'new'"
             class="flex flex--center flex--space form-group"
             :style="{width: max_set_len+'px'}"
        >
            <label class="">Initial loading:</label>
            <select class="form-control l-inl-control"
                    @change="propChanged('initial_view_id')"
                    v-model="tb_cur_settings.initial_view_id"
                    :style="textStyle">
                <option :value="-2" style="color: #00F">Blank (No Data)</option>
                <option :value="-1" style="color: #00F">Entire Table (Available Range)</option>
                <option :value="0" style="color: #00F">Last Exit State</option>
                <option disabled="disabled">Views:</option>
                <option v-for="view in tb_views" :value="view.id">&nbsp;&nbsp;&nbsp;&nbsp;{{ view.name }}</option>
            </select>
        </div>

        <template v-if="isOwner">
            <div class="form-group">
                <label class="form-group--min">Popup form view for single record, including DCR Form.</label>
                <div class="m_l form-group--min">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check"
                              @click="propChanged('vert_tb_bgcolor',tb_meta)"
                              :style="$root.checkBoxStyle"
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
                           :style="textStyle">
                    <span>%</span>
                </div>
                <div class="m_l">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check"
                              @click="propChanged('vert_tb_floating',tb_meta)"
                              :style="$root.checkBoxStyle"
                        >
                            <i v-if="tb_meta.vert_tb_floating" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <span>Apply Floating settings to columns.</span>
                </div>
            </div>

            <div v-if="hasUserField || hasVoteField" class="form-group" :style="{width: max_set_len+'px'}">
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
                        <tr v-if="hasUserField">
                            <th>Table Field</th>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_image',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_image" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_first',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_first" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_last',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_last" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_email',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_email" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('user_fld_show_username',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.user_fld_show_username" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                        </tr>
                        <tr v-if="hasUserField">
                            <th>Data History</th>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_image',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_image" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_first',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_first" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_last',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_last" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_email',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_email" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('history_user_show_username',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.history_user_show_username" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                        </tr>
                        <tr v-if="hasVoteField">
                            <th>Vote</th>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_image',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.vote_user_show_image" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_first',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.vote_user_show_first" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_last',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.vote_user_show_last" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_email',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
                                    >
                                        <i v-if="tb_cur_settings.vote_user_show_email" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </td>
                            <td class="center">
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="propChanged('vote_user_show_username',tb_cur_settings,sunc_settings)"
                                          :style="$root.checkBoxStyle"
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
                <board-setting-block
                        :board_view_height="tb_meta.board_view_height"
                        :board_image_width="tb_meta.board_image_width"
                        @val-changed="genSetting"
                ></board-setting-block>
                <table class="tb-rows-padding">
                    <tr>
                        <td>
                            <label class="">Max. # of loaded records in the pop-up panel for links:</label>
                        </td>
                        <td>
                            <input class="form-control l-inl-control l-inl-control--min"
                                   type="number"
                                   @change="propChanged('max_rows_in_link_popup')"
                                   v-model="tb_meta.max_rows_in_link_popup"
                                   :style="textStyle">
                        </td>
                    </tr>
                    <template v-if="!filter_for_field">
                        <tr>
                            <td>
                                <label class="">Max. # of searching results to be displayed:</label>
                            </td>
                            <td>
                                <input class="form-control l-inl-control l-inl-control--min"
                                       type="number"
                                       @change="propChanged('search_results_len')"
                                       v-model="tb_meta.search_results_len"
                                       :style="textStyle">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label class="">Max. # of items can be displayed in a filter:</label>
                            </td>
                            <td>
                                <input class="form-control l-inl-control l-inl-control--min"
                                       type="number"
                                       @change="propChanged('max_filter_elements')"
                                       v-model="tb_meta.max_filter_elements"
                                       :style="textStyle">
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
                                :style="textStyle">
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
                                    :style="textStyle">
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
                                    :style="textStyle">
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
                                    :style="textStyle">
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
                                    :style="textStyle">
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
                                    :style="textStyle">
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
                                    :style="textStyle">
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
                                    :style="textStyle">
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
                                    :style="textStyle">
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
                                    :style="textStyle">
                                <option :value="null"></option>
                                <option v-for="hdr in tb_meta._fields" v-if="hdr.f_type !== 'Address'" :value="hdr.id">{{ hdr.name }}</option>
                            </select>
                        </label>
                        <label class="flex flex--center half-flex flexo-width-35-75">
                        </label>
                    </div>
                </div>
            </template>

            <div class="unit-conv-items form-group" v-show="presentUnitConv" :style="{width: max_set_len+'px'}">
                <div class="form-group--min">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              @click="tb_meta.unit_conv_is_active = !tb_meta.unit_conv_is_active;propChanged('unit_conv_is_active')"
                              :style="$root.checkBoxStyle"
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
                              :style="$root.checkBoxStyle"
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

                                    <a v-else="" target="_blank" :href="showUnitTb('link')" @click.stop="">{{ showUnitTb() }}</a>

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
                                                :style="textStyle">
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
                                                :style="textStyle">
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
                                                :style="textStyle">
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
                                                :style="textStyle">
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
                                                :style="textStyle">
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
                                                :style="textStyle">
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
                              :style="$root.checkBoxStyle"
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
                              :style="$root.checkBoxStyle"
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
        </template>
    </div>
</template>

<script>
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
                hide_api: true,
                api_dots: '',
                re_init: false,
                help_tooltip: false,
                help_left: 0,
                help_top: 0,
                help_offset: 0,
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
                return this.tb_meta._is_owner;
            },
            isOwnerOrCanEdit() {
                return this.tb_meta._is_owner
                    || (this.tb_meta._current_right && this.tb_meta._current_right.can_edit_tb);
            },
        },
        props: {
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
            setApidots() {
                this.api_dots = String(this.tb_meta.google_api_key || '').replace(/./gi, '*');
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
                    res = tb ? tb.__visiting_url : res;
                } else {
                    res = tb ? tb.__url || tb.name : res;
                }
                return res;
            },
            showHover(e) {
                let bounds = this.$refs.fahelp ? this.$refs.fahelp.getBoundingClientRect() : {};
                let px = (bounds.left + bounds.right) / 2;
                let py = (bounds.top + bounds.bottom) / 2;
                this.help_tooltip = true;
                this.help_left = px || e.clientX;
                this.help_top = py || e.clientY;
                this.help_offset = Math.abs(bounds.top - bounds.bottom) || 0;
            },
            userHasAddon(code) {
                let idx = _.findIndex(this.$root.user._subscription._addons, {code: code});
                return this.$root.user._is_admin || idx > -1;
            },
            genSetting(prop_name, val) {
                this.tb_meta[prop_name] = val;
                this.$emit('prop-changed', prop_name);
            },
            propChanged(prop_name, obj, sync_obj) {
                if (obj) {
                    obj[prop_name] = !obj[prop_name];
                    (sync_obj ? sync_obj[prop_name] = obj[prop_name] : null);
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
                    this.tb_meta.max_filter_elements = Math.max(this.tb_meta.max_filter_elements, 100);
                    this.tb_meta.max_filter_elements = Math.min(this.tb_meta.max_filter_elements, 3000);
                }
                this.$emit('prop-changed', prop_name);
            },
            removeGlApi() {
                this.tb_meta.google_api_key = '';
                this.propChanged('google_api_key');
                this.setApidots();
            },
        },
        mounted() {
            this.setApidots();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import './../CommonBlocks/TabldaLike';

    .m_l {
        margin-left: 30px;
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
        top: 2px;
    }
    
    .input-left-items {
        text-align: left;
        
        label {
            font-weight: normal;
        }
        input {
            margin-left: 30px;
        }
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

    .fa-info-circle {
        cursor: pointer;
        padding-left: 5px;
    }

    .btn-danger {
        line-height: 18px;
        font-size: 2em;
        padding: 5px;
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