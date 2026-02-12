<template>
    <div class="full-frame settings-tab">

        <div style="margin-top: 3px;">
            <template v-if="all_settings.elem_type !== 'text_data'">
                <div class="inline-fld">
                    <label>Data Range:</label>
                    <select
                        class="form-group form-control control-200"
                        v-model="all_settings.data_range"
                        @change="chSettingChanged('data_range')"
                    >
                        <option :value="null"></option>
                        <option :value="'-2'">Table</option>
                        <option :value="'-1'">GridView (All pages)</option>
                        <option :value="'0'">Current Page</option>
                        <option disabled>Row Group:</option>
                        <option v-for="rg in tableMeta._row_groups" :value="'rg:'+rg.id">&nbsp;&nbsp;&nbsp;{{ rg.name }}</option>
                        <option disabled>Filter Combo:</option>
                        <option v-for="sf in tableMeta._saved_filters" :value="'sf:'+sf.id">&nbsp;&nbsp;&nbsp;{{ sf.name }}</option>
                    </select>
                </div>
                <div v-if="all_settings.elem_type === 'pivot_table'" class="inline-fld">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check"
                            :class="{'disabled': !canEdit}"
                            @click="!canEdit ? null : referencedChange()"
                        >
                            <i v-if="apt.referenced_tables" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>&nbsp;Ref'd Tables</label>
                </div>
            </template>
            <template v-else="">
                <button class="btn btn-default blue-gradient"
                        :class="[text_tab !== 'editor' ? 'opac' : '']"
                        :style="$root.themeButtonStyle"
                        @click="text_tab = 'editor'"
                >Editor</button>
                <button class="btn btn-default blue-gradient"
                        :class="[text_tab !== 'vars' ? 'opac' : '']"
                        :style="$root.themeButtonStyle"
                        @click="text_tab = 'vars'"
                >Variables</button>
            </template>

            <div class="inline-fld" :class="[all_settings.elem_type === 'text_data' ? 'pull-right' : '']">
                <button v-if="text_tab !== 'vars'"
                        class="btn btn-default blue-gradient"
                        :disabled="!should_save"
                        style="position: relative;top: -3px;"
                        @click="sendChangeSignal(canEdit ? 'data_range' : '__update_cache')"
                        :style="$root.themeButtonStyle"
                >Save</button>
            </div>
        </div>

        <div class="chart-bsett" ref="ch_body" :style="{backgroundColor: (all_settings.dimensions.back_color || '#FFF')}">

            <!-- START TEXT PART -->
            <div class="full-frame settings-tab" v-if="all_settings.elem_type === 'text_data'">
                <vue-ckeditor
                        v-if="conf_ready"
                        v-show="text_tab === 'editor'"
                        class="ck_textarea"
                        :config="config_ck"
                        v-model="ckeditor_content"
                ></vue-ckeditor>
                <div v-show="text_tab === 'vars'">

                    <div style="padding-top: 5px;">
                        <button class="btn btn-default blue-gradient"
                                :class="[vars_tabs !== 'tables' ? 'opac' : '']"
                                :style="$root.themeButtonStyle"
                                @click="vars_tabs = 'tables'"
                        >Tables</button>
                        <button class="btn btn-default blue-gradient"
                                :class="[vars_tabs !== 'charts' ? 'opac' : '']"
                                :style="$root.themeButtonStyle"
                                @click="vars_tabs = 'charts'"
                        >Charts</button>
                    </div>

                    <table class="table_settings" v-show="vars_tabs === 'tables'">
                        <colgroup>
                            <col style="width: 120px;">
                            <col style="width: 120px;">
                            <col style="width: 300px;">
                            <col style="width: 125px;">
                            <col style="width: 55px;">
                        </colgroup>
                        <thead>
                        <tr>
                            <th rowspan="2">Name</th>
                            <th rowspan="2">Component</th>
                            <th rowspan="2">Row and Column Levels</th>
                            <th rowspan="2">About</th>
                            <th rowspan="2">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!--HORIZONTAL-->
                        <template v-for="(vt,i) in all_settings.text_data.vars_table">
                            <tr
                                :is="'bi-chart-settings-variable'"
                                :type="'table'"
                                :is_add="false"
                                :table-meta="tableMeta"
                                :variable_obj="vt"
                                :linked_chart="chobj(vt.chart_id)"
                                @change-signal="variableSave('vars_table')"
                                @delete-signal="delTabl(i)"
                            ></tr>
                        </template>
                        <tr
                            :is="'bi-chart-settings-variable'"
                            :type="'table'"
                            :is_add="true"
                            :table-meta="tableMeta"
                            :variable_obj="var_table"
                            :linked_chart="chobj(var_table.chart_id)"
                            @add-signal="addTabl()"
                        ></tr>
                        </tbody>
                    </table>

                    <table class="table_settings" v-show="vars_tabs === 'charts'">
                        <colgroup>
                            <col style="width: 120px;">
                            <col style="width: 120px;">
                            <col style="width: 100px;">
                            <col style="width: 100px;">
                            <col style="width: 100px;">
                            <col style="width: 100px;">
                            <col style="width: 55px;">
                        </colgroup>
                        <thead>
                        <tr>
                            <th rowspan="2">Name</th>
                            <th rowspan="2">Component</th>
                            <th colspan="2">Grouping</th>
                            <th rowspan="2">Stacking</th>
                            <th rowspan="2">About</th>
                            <th rowspan="2">Action</th>
                        </tr>
                        <tr>
                            <th>L1</th>
                            <th>L2</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!--HORIZONTAL-->
                        <template v-for="(vt,i) in all_settings.text_data.vars_chart">
                            <tr
                                :is="'bi-chart-settings-variable'"
                                :type="'chart'"
                                :is_add="false"
                                :table-meta="tableMeta"
                                :variable_obj="vt"
                                :linked_chart="chobj(vt.chart_id)"
                                @change-signal="variableSave('vars_chart')"
                                @delete-signal="delChar(i)"
                            ></tr>
                        </template>
                        <tr
                            :is="'bi-chart-settings-variable'"
                            :type="'chart'"
                            :is_add="true"
                            :table-meta="tableMeta"
                            :variable_obj="var_chart"
                            :linked_chart="chobj(var_chart.chart_id)"
                            @add-signal="addChar()"
                        ></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- START TABLE PART -->
            <div class="full-frame settings-tab" v-if="all_settings.elem_type === 'pivot_table'">
                <div class="form-group"></div>
                <div class="">
                    <div class="inline-fld">
                        <input class="form-group form-control control-200"
                               placeholder="Title"
                               :disabled="!canEdit"
                               v-model="apt.labels.general"
                               @change="chSettingChanged('pivot_table.labels.general')"/>
                    </div>
                    <div class="inline-fld">
                        <input class="form-group form-control control-200"
                               placeholder="Row Label"
                               :disabled="!canEdit"
                               v-model="apt.labels.x_label"
                               @change="chSettingChanged('pivot_table.labels.x_label')"/>
                    </div>
                    <div class="inline-fld">
                        <input class="form-group form-control control-200"
                               placeholder="Column Label"
                               :disabled="!canEdit"
                               v-model="apt.labels.y_label"
                               @change="chSettingChanged('pivot_table.labels.y_label')"/>
                    </div>
                    <div class="flex flex--center-v" style="float: right; margin-top: 7px;">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check"
                                  :class="{'disabled': !canEdit}"
                                  @click="!canEdit ? null : chSettingChanged('pivot_table.activness_visible',apt,'activness_visible');"
                            >
                                <i v-if="apt.activness_visible" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <label class="no-margin">&nbsp;"Active"</label>
                    </div>
                </div>
                <div class="form-group">
                    <table class="table_settings">
                        <colgroup>
                            <col style="width: 75px;">
                            <col style="width: 60px;">
                            <col style="width: 35px;">
                            <col style="width: 75px;">
                            <col style="width: 215px;">
                            <col v-if="apt_fld_meta.some_has_mselect" style="width: 75px;">
                            <col style="width: 50px;">
                            <col style="width: 80px;">
                            <col style="width: 65px;">
                            <col style="width: 55px;">
                            <col style="width: 95px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th rowspan="2"></th>
                                <th colspan="3">Level</th>
                                <th rowspan="2">Fields/Columns</th>
                                <th rowspan="2" v-if="apt_fld_meta.some_has_mselect">Split Multi Choices</th>
                                <th colspan="2">Sub Total</th>
                                <th rowspan="2">Hide If Empty</th>
                                <th rowspan="2">Link(s)</th>
                                <th rowspan="2">Width</th>
                            </tr>
                            <tr>
                                <th>Qty</th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Place</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--HORIZONTAL-->
                            <tr v-for="lvl in Number(apt.hor_l)">
                                <td v-if="lvl === 1" class="td--th" :rowspan="apt.hor_l">
                                    <span>Columns</span>
                                </td>
                                <td v-if="lvl === 1" class="td--th" :rowspan="apt.hor_l">
                                    <select class="form-control p-sm"
                                            v-model="apt.hor_l"
                                            :disabled="!canEdit"
                                            :style="{color: apt.hor_l ? '' : '#AAA'}"
                                            @change="clearFields('horizontal');chSettingChanged('pivot_table.hor_l');"
                                    >
                                        <option v-for="i in max_hor">{{ i }}</option>
                                    </select>
                                </td>
                                <td class="td--th"
                                    :style="{border: horToLVL == lvl ? '2px dashed #000' : null}"
                                    :draggable="canEdit"
                                    @dragstart.stop="horFromLVL = lvl"
                                    @dragover.stop=""
                                    @dragenter.stop="horToLVL = lvl"
                                    @dragend.stop="reorderLevels('horizontal')"
                                    style="cursor: pointer"
                                >{{ lvl }}</td>
                                <td>
                                    <input type="text"
                                           v-model="apt.horizontal['l'+lvl+'_lvl_fname']"
                                           :disabled="!canEdit"
                                           @change="chSettingChanged('pivot_table.horizontal.l'+lvl+'_lvl_fname');"
                                           class="form-control"/>
                                </td>
                                <td>
                                    <select-block
                                            v-if="apt.referenced_tables"
                                            :options="getFieldsAndRef()"
                                            :sel_value="apt.horizontal['l'+lvl+'_reference']"
                                            :is_disabled="!canEdit"
                                            :style="{color: apt.horizontal['l'+lvl+'_field'] ? '' : '#AAA'}"
                                            @option-select="(opt) => { refFieldChanged(opt,'horizontal',lvl) }"
                                    ></select-block>
                                    <select-block
                                            v-else=""
                                            :options="getFieldsAndRef()"
                                            :sel_value="apt.horizontal['l'+lvl+'_field']"
                                            :is_disabled="!canEdit"
                                            :style="{color: apt.horizontal['l'+lvl+'_field'] ? '' : '#AAA'}"
                                            @option-select="(opt) => { refFieldChanged(opt,'horizontal',lvl) }"
                                    ></select-block>
                                </td>
                                <td v-if="apt_fld_meta.some_has_mselect">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              :class="{'disabled': !canEdit || !apt_fld_meta['hor'+lvl] || !apt_fld_meta['hor'+lvl+'_ismsel']}"
                                              @click="!canEdit || !apt_fld_meta['hor'+lvl] || !apt_fld_meta['hor'+lvl+'_ismsel']
                                                ? null
                                                : chSettingChanged('pivot_table.horizontal.l'+lvl+'_split',apt.horizontal,'l'+lvl+'_split');"
                                        >
                                            <i v-if="apt.horizontal['l'+lvl+'split']" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              :class="{'disabled': !canEdit || !apt.horizontal['l'+lvl+'_field']}"
                                              @click="!canEdit || !apt.horizontal['l'+lvl+'_field']
                                                ? null
                                                : chSettingChanged('pivot_table.horizontal.l'+lvl+'_sub_total',apt.horizontal,'l'+lvl+'_sub_total');"
                                        >
                                            <i v-if="apt.horizontal['l'+lvl+'_sub_total']" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td v-if="lvl === 1" class="td--th" :rowspan="apt.hor_l">
                                    <select class="form-control p-sm"
                                            style="font-weight: normal;"
                                            v-model="apt.horizontal.sub_tot_pos"
                                            :disabled="!canEdit"
                                            :style="{color: apt.horizontal.sub_tot_pos ? '' : '#AAA'}"
                                            @change="chSettingChanged('pivot_table.horizontal.sub_tot_pos');"
                                    >
                                        <option value="front">Front</option>
                                        <option value="back">Back</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              :class="{'disabled': !canEdit || !apt.horizontal['l'+lvl+'_field']}"
                                              @click="!canEdit || !apt.horizontal['l'+lvl+'_field']
                                                ? null
                                                : chSettingChanged('pivot_table.horizontal.l'+lvl+'_hide_empty',apt.horizontal,'l'+lvl+'_hide_empty');"
                                        >
                                            <i v-if="apt.horizontal['l'+lvl+'_hide_empty']" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              :class="{'disabled': !canEdit || !apt.horizontal['l'+lvl+'_field']}"
                                              @click="!canEdit || !apt.horizontal['l'+lvl+'_field']
                                                ? null
                                                : chSettingChanged('pivot_table.horizontal.l'+lvl+'_show_links',apt.horizontal,'l'+lvl+'_show_links');"
                                        >
                                            <i v-if="apt.horizontal['l'+lvl+'_show_links']" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td v-if="lvl === 1" :rowspan="apt.hor_l">
                                    <select class="form-control p-sm"
                                            :disabled="!canEdit"
                                            v-model="apt.horizontal.hor_fields_width"
                                            @change="chSettingChanged('pivot_table.horizontal.hor_fields_width');"
                                    >
                                        <option value="null"></option>
                                        <option value="min">Min</option>
                                        <option value="uniform">Uniform</option>
                                    </select>
                                </td>
                            </tr>
                            <!--END HORIZONTAL-->

                            <!--VERTICAL-->
                            <tr v-for="lvl in Number(apt.vert_l)">
                                <td v-if="lvl === 1" class="td--th" :rowspan="apt.vert_l">
                                    <span>Rows</span>
                                </td>
                                <td v-if="lvl === 1" class="td--th" :rowspan="apt.vert_l">
                                    <select class="form-control p-sm"
                                            v-model="apt.vert_l"
                                            :disabled="!canEdit"
                                            :style="{color: apt.vert_l ? '' : '#AAA'}"
                                            @change="clearFields('vertical');chSettingChanged('pivot_table.vert_l');"
                                    >
                                        <option v-for="i in max_vert">{{ i }}</option>
                                    </select>
                                </td>
                                <td class="td--th"
                                    :style="{border: vertToLVL == lvl ? '2px dashed #000' : null}"
                                    :draggable="canEdit"
                                    @dragstart.stop="vertFromLVL = lvl"
                                    @dragover.stop=""
                                    @dragenter.stop="vertToLVL = lvl"
                                    @dragend.stop="reorderLevels('vertical')"
                                    style="cursor: pointer"
                                >{{ lvl }}</td>
                                <td>
                                    <input type="text"
                                           v-model="apt.vertical['l'+lvl+'_lvl_fname']"
                                           :disabled="!canEdit"
                                           @change="chSettingChanged('pivot_table.vertical.l'+lvl+'_lvl_fname');"
                                           class="form-control"/>
                                </td>
                                <td>
                                    <select-block
                                            v-if="apt.referenced_tables"
                                            :options="getFieldsAndRef()"
                                            :sel_value="apt.vertical['l'+lvl+'_reference']"
                                            :is_disabled="!canEdit"
                                            :style="{color: apt.vertical['l'+lvl+'_field'] ? '' : '#AAA'}"
                                            @option-select="(opt) => { refFieldChanged(opt,'vertical',lvl) }"
                                    ></select-block>
                                    <select-block
                                            v-else=""
                                            :options="getFieldsAndRef()"
                                            :sel_value="apt.vertical['l'+lvl+'_field']"
                                            :is_disabled="!canEdit"
                                            :style="{color: apt.vertical['l'+lvl+'_field'] ? '' : '#AAA'}"
                                            @option-select="(opt) => { refFieldChanged(opt,'vertical',lvl) }"
                                    ></select-block>
                                </td>
                                <td v-if="apt_fld_meta.some_has_mselect">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              :class="{'disabled': !canEdit || !apt_fld_meta['vert'+lvl] || !apt_fld_meta['vert'+lvl+'_ismsel']}"
                                              @click="!canEdit || !apt_fld_meta['vert'+lvl] || !apt_fld_meta['vert'+lvl+'_ismsel']
                                                ? null
                                                : chSettingChanged('pivot_table.vertical.l'+lvl+'_split',apt.vertical,'l'+lvl+'_split');"
                                        >
                                            <i v-if="apt.vertical['l'+lvl+'_split']" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              :class="{'disabled': !canEdit || !apt.vertical['l'+lvl+'_field']}"
                                              @click="!canEdit || !apt.vertical['l'+lvl+'_field']
                                                ? null
                                                : chSettingChanged('pivot_table.vertical.l'+lvl+'_sub_total',apt.vertical,'l'+lvl+'_sub_total');"
                                        >
                                            <i v-if="apt.vertical['l'+lvl+'_sub_total']" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td v-if="lvl === 1" class="td--th" :rowspan="apt.vert_l">
                                    <select class="form-control p-sm"
                                            style="font-weight: normal;"
                                            v-model="apt.vertical.sub_tot_pos"
                                            :disabled="!canEdit"
                                            :style="{color: apt.vertical.sub_tot_pos ? '' : '#AAA'}"
                                            @change="chSettingChanged('pivot_table.vertical.sub_tot_pos');"
                                    >
                                        <option value="top">Top</option>
                                        <option value="bot">Bot</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              :class="{'disabled': !canEdit || !apt.vertical['l'+lvl+'_field']}"
                                              @click="!canEdit || !apt.vertical['l'+lvl+'_field']
                                                ? null
                                                : chSettingChanged('pivot_table.vertical.l'+lvl+'_hide_empty',apt.vertical,'l'+lvl+'_hide_empty');"
                                        >
                                            <i v-if="apt.vertical['l'+lvl+'_hide_empty']" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              :class="{'disabled': !canEdit || !apt.vertical['l'+lvl+'_field']}"
                                              @click="!canEdit || !apt.vertical['l'+lvl+'_field']
                                                ? null
                                                : chSettingChanged('pivot_table.vertical.l'+lvl+'_show_links',apt.vertical,'l'+lvl+'_show_links');"
                                        >
                                            <i v-if="apt.vertical['l'+lvl+'_show_links']" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                                <th v-if="lvl === 1" :rowspan="apt.vert_l"></th>
                            </tr>
                            <!--END VERTICAL-->
                        </tbody>
                    </table>
                </div>
                <div class="form-group">
                    <!--ABOUT SETTINGS-->
                    <table class="table_settings">
                        <colgroup>
                            <col style="width: 75px;">
                            <col style="width: 60px;">
                            <col style="width: 35px;">
                            <col style="width: 75px;">
                            <col style="width: 90px;">
                            <col style="width: 140px;">
                            <col style="width: 100px;">
                            <col style="width: 90px;">
                            <col style="width: 90px;">
                            <col style="width: 90px;">
                        </colgroup>
                        <thead>
                        <tr>
                            <th rowspan="2"></th>
                            <th rowspan="2">Qty</th>
                            <th rowspan="2">#</th>
                            <th rowspan="2">Name</th>
                            <th rowspan="2">Type</th>
                            <th rowspan="2">
                                <span>Fields</span>
                                <label v-if="apt.len_about > 1">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              @click="chSettingChanged('pivot_table.stack_about',apt,'stack_about');"
                                        >
                                            <i v-if="apt.stack_about" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <span>Stacked</span>
                                </label>
                            </th>
                            <th colspan="2">STATS</th>
                            <th rowspan="2">Label</th>
                            <th rowspan="2">Show Zeros</th>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <th>Func</th>
                        </tr>
                        </thead>
                        <tbody>
                            <!--About 1,2,3,4,5-->
                            <tr v-for="lvl in Number(apt.len_about)">
                                <td v-if="lvl === 1" class="td--th" :rowspan="apt.len_about">
                                    <span>About</span>
                                </td>
                                <td v-if="lvl === 1" class="td--th" :rowspan="apt.len_about">
                                    <select class="form-control p-sm"
                                            v-model="apt.len_about"
                                            :disabled="!canEdit"
                                            :style="{color: apt.len_about ? '' : '#AAA'}"
                                            @change="chSettingChanged('pivot_table.len_about');"
                                    >
                                        <option v-for="i in max_about">{{ i }}</option>
                                    </select>
                                </td>
                                <td class="td--th"
                                    :style="{border: aboutToLVL == lvl ? '2px dashed #000' : null}"
                                    :draggable="canEdit"
                                    @dragstart.stop="aboutFromLVL = lvl"
                                    @dragover.stop=""
                                    @dragenter.stop="aboutToLVL = lvl"
                                    @dragend.stop="reorderLevels('about')"
                                    style="cursor: pointer"
                                >{{ lvl }}</td>
                                <td>
                                    <input type="text"
                                           v-model="apt[abo(lvl)].lvl_fname"
                                           :disabled="!canEdit"
                                           @change="chSettingChanged('pivot_table.'+abo(lvl)+'.lvl_fname');"
                                           class="form-control"/>
                                </td>
                                <td>
                                    <select
                                            class="form-control"
                                            v-model="apt[abo(lvl)].abo_type"
                                            :disabled="!canEdit"
                                            :style="{color: apt[abo(lvl)].abo_type ? '' : '#AAA', padding: 0}"
                                            @change="chSettingChanged('pivot_table.'+abo(lvl)+'.abo_type');"
                                    >
                                        <option value="field">Field</option>
                                        <option value="formula">Formula</option>
                                    </select>
                                </td>
                                <td>
                                    <div v-if="apt[abo(lvl)].abo_type === 'formula'" class="full-height" style="position: relative">
                                        <input type="text"
                                               v-model="apt[abo(lvl)].formula_string"
                                               :disabled="!canEdit"
                                               @keyup="recreateForm(lvl)"
                                               @focus="formhelper[lvl] = true"
                                               class="form-control"/>
                                        <formula-helper
                                                v-if="formhelper[lvl]"
                                                :user="$root.user"
                                                :table-meta="tableMeta"
                                                :table-row="apt[abo(lvl)]"
                                                :header-key="'formula_string'"
                                                :can-edit="canEdit"
                                                :no-function="true"
                                                :no_prevent="true"
                                                :pop_width="'100%'"
                                                @close-formula="formhelper[lvl] = false"
                                                @set-formula="formhelper[lvl] = false;chSettingChanged('pivot_table.'+abo(lvl)+'.formula_string');"
                                        ></formula-helper>
                                    </div>
                                    <select
                                            v-else=""
                                            class="form-control"
                                            v-model="apt[abo(lvl)].field"
                                            :disabled="!canEdit"
                                            :style="{color: apt[abo(lvl)].field ? '' : '#AAA'}"
                                            @change="chSettingChanged('pivot_table.'+abo(lvl)+'.field')"
                                    >
                                        <option :style="{color: '#AAA'}" disabled :value="null">Field</option>
                                        <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                            {{ $root.uniqName( fld.name ) }}
                                        </option>
                                    </select>
                                </td>

                                <td>
                                    <select class="form-control"
                                            :disabled="!canEdit || !apt[abo(lvl)].field"
                                            v-model="apt[abo(lvl)].calc_val"
                                            @change="chSettingChanged('pivot_table.'+abo(lvl)+'.calc_val');"
                                    >
                                        <option v-for="gr in calc_values" :value="gr.func">{{ gr.show }}</option>
                                    </select>
                                </td>
                                <td>
                                    <select v-show="apt[abo(lvl)].calc_val == 1"
                                            class="form-control"
                                            :disabled="!canEdit || !apt[abo(lvl)].field"
                                            v-model="apt[abo(lvl)].group_function"
                                            @change="chSettingChanged('pivot_table.'+abo(lvl)+'.group_function');"
                                    >
                                        <option v-for="gr in group_functions" :value="gr.val">{{ gr.show }}</option>
                                    </select>
                                </td>
                                <td>
                                    <select
                                        class="form-control"
                                        v-model="apt[abo(lvl)].label_field"
                                        :disabled="!canEdit"
                                        :style="{color: apt[abo(lvl)].label_field ? '' : '#AAA'}"
                                        @change="chSettingChanged('pivot_table.'+abo(lvl)+'.label_field')"
                                    >
                                        <option :style="{color: '#AAA'}" :value="null">Field</option>
                                        <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                            {{ $root.uniqName( fld.name ) }}
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check"
                                              :class="{'disabled': !canEdit || !apt[abo(lvl)].field}"
                                              @click="!canEdit || !apt[abo(lvl)].field
                                                ? null
                                                : chSettingChanged('pivot_table.'+abo(lvl)+'.show_zeros',apt[abo(lvl)],'show_zeros');"
                                        >
                                            <i v-if="apt[abo(lvl)].show_zeros" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="" style="position: relative;">
                    <div class="flex flex--center-v">
                        <button class="btn btn-default blue-gradient"
                                :disabled="!all_settings.table_to_export"
                                :style="$root.themeButtonStyle"
                                @click="exportConfirm()"
                        >Export</button>
                        <label style="margin: 0;">&nbsp;to table:&nbsp;</label>
                        <div class="control-300 bcs-swfs-wrapper">
                            <select-with-folder-structure
                                :cur_val="all_settings.table_to_export"
                                :available_tables="$root.settingsMeta.available_tables"
                                :user="$root.user"
                                class="form-control full-height"
                                @sel-changed="tableToExpUpdate">
                            </select-with-folder-structure>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TABLE PART -->

            <!-- START CHART PART -->
            <div class="full-frame settings-tab" v-if="all_settings.elem_type === 'bi_chart'">
                <div class="form-group"></div>
                <div class="">
                    <div class="inline-fld">
                        <label>Type:</label>
                        <select
                                class="form-control control-inline control-200"
                                v-model="abc.chart_sub_type"
                                :readonly="!canEdit"
                                @change="changedType();chSettingChanged('bi_chart.chart_sub_type');"
                        >
                            <option v-for="type in available_types" :value="type">
                                {{ type[0].toUpperCase() + type.substring(1) }}
                            </option>
                        </select>
                    </div>
                    <div class="inline-fld">
                        <label>Sub Type:</label>
                        <select
                                class="form-group form-control control-200"
                                v-model="abc.chart_type"
                                :readonly="!canEdit"
                                @change="chSettingChanged('bi_chart.chart_type');"
                        >
                            <option value="basic">Basic</option>
                            <option v-if="abc.chart_sub_type === 'column'" value="grouped_stacked">Grouped &amp; Stacked</option>
                        </select>
                    </div>
                </div>
                <div class="">
                    <div class="inline-fld">
                        <input class="form-group form-control control-200"
                               placeholder="Title"
                               :disabled="!canEdit"
                               v-model="abc.labels.general"
                               @change="chSettingChanged('bi_chart.labels.general')"/>
                    </div>
                    <div class="inline-fld">
                        <input class="form-group form-control control-200"
                               placeholder="X Label"
                               :disabled="!canEdit"
                               v-model="abc.labels.x_label"
                               @change="chSettingChanged('bi_chart.labels.x_label')"/>
                    </div>
                    <div class="inline-fld">
                        <input class="form-group form-control control-200"
                               placeholder="Y Label"
                               :disabled="!canEdit"
                               v-model="abc.labels.y_label"
                               @change="chSettingChanged('bi_chart.labels.y_label')"/>
                    </div>
                    <div class="inline-fld" v-show="abc.chart_sub_type !== 'TSLH'">
                        <label>Show Legend:</label>
                        <label class="switch_t">
                            <input type="checkbox"
                                   :disabled="!canEdit"
                                   v-model="abc.show_legend"
                                   @change="chSettingChanged('bi_chart.show_legend')">
                            <span class="toggler round"></span>
                        </label>
                    </div>
                </div>

                <!-- ALL CHARTS EXCEPT 'TSLH' TYPE -->
                <div v-show="abc.chart_sub_type !== 'TSLH'">
                    <div class="">
                        <div class="inline-fld">
                            <label class="left-label">Grouping L1 (X):</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.x_axis.field"
                                    :disabled="!canEdit"
                                    :style="{color: abc.x_axis.field ? '' : '#AAA'}"
                                    @change="changedXfield();chSettingChanged('bi_chart.x_axis.field')"
                            >
                                <option :style="{color: '#AAA'}" disabled :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                        <div class="inline-fld" v-if="abc.chart_type == 'grouped_stacked' || abc.chart_sub_type == 'line'">
                            <label class="left-label">{{ abc.chart_sub_type == 'line' ? 'M-Lines' : 'L2' }}:</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.x_axis.l1_group_fld"
                                    :disabled="!canEdit || !abc.x_axis.field"
                                    :style="{color: abc.x_axis.l1_group_fld ? '' : '#AAA'}"
                                    @change="changedL1X();chSettingChanged('bi_chart.x_axis.l1_group_fld')"
                            >
                                <option :style="{color: '#AAA'}" :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                        <div class="inline-fld" v-if="abc.chart_type == 'grouped_stacked'">
                            <label class="left-label">Stacking:</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.x_axis.l2_group_fld"
                                    :disabled="!canEdit || !abc.x_axis.l1_group_fld"
                                    :style="{color: abc.x_axis.l2_group_fld ? '' : '#AAA'}"
                                    @change="chSettingChanged('bi_chart.x_axis.l2_group_fld')"
                            >
                                <option :style="{color: '#AAA'}" :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="">
                        <div class="inline-fld">
                            <label class="left-label">About (Y):</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.y_axis.field"
                                    :disabled="!canEdit"
                                    :style="{color: abc.y_axis.field ? '' : '#AAA'}"
                                    @change="chSettingChanged('bi_chart.y_axis.field')"
                            >
                                <option :style="{color: '#AAA'}" disabled :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                        <div class="inline-fld">
                            <select class="form-group form-control control-130"
                                    :disabled="!canEdit || !abc.y_axis.field"
                                    v-model="abc.y_axis.calc_val"
                                    @change="chSettingChanged('bi_chart.y_axis.calc_val')"
                            >
                                <option v-for="gr in calc_values" :value="gr.func">{{ gr.show }}</option>
                            </select>
                        </div>
                        <div class="inline-fld" v-show="abc.y_axis.calc_val == 1">
                            <select v-if="abc.x_axis.l1_group_fld"
                                    class="form-group form-control control-100"
                                    :disabled="!canEdit || !abc.y_axis.field"
                                    v-model="abc.y_axis.group_function"
                                    @change="chSettingChanged('bi_chart.y_axis.group_function')"
                            >
                                <option v-for="gr in group_functions" :value="gr.val">{{ gr.show }}</option>
                            </select>
                            <select-block v-else
                                    class="form-group form-control control-200"
                                    :options="group_functions"
                                    :is_multiselect="true"
                                    :is_disabled="!canEdit || !abc.y_axis.field"
                                    :sel_value="y_axis_group_function"
                                    @option-select="storeXaxisGroupFunction"
                            ></select-block>
                        </div>
                    </div>
                </div>
                <!-- SETTINGS FOR 'TSLH' CHART -->
                <div v-show="abc.chart_sub_type === 'TSLH'">
                    <div class="">
                        <div class="inline-fld">
                            <label class="label-width">Bottom:</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.tslh.bottom"
                                    :disabled="!canEdit"
                                    :style="{color: abc.tslh.bottom ? '' : '#AAA'}"
                                    @change="chSettingChanged('bi_chart.tslh.bottom')"
                            >
                                <option :style="{color: '#AAA'}" disabled :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="">
                        <div class="inline-fld">
                            <label class="label-width">Top:</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.tslh.top"
                                    :disabled="!canEdit"
                                    :style="{color: abc.tslh.top ? '' : '#AAA'}"
                                    @change="chSettingChanged('bi_chart.tslh.top')"
                            >
                                <option :style="{color: '#AAA'}" disabled :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="">
                        <div class="inline-fld">
                            <label class="label-width">Start:</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.tslh.start"
                                    :disabled="!canEdit"
                                    :style="{color: abc.tslh.start ? '' : '#AAA'}"
                                    @change="chSettingChanged('bi_chart.tslh.start')"
                            >
                                <option :style="{color: '#AAA'}" disabled :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                        <div class="inline-fld">
                            <label class="label-width">End:</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.tslh.end"
                                    :disabled="!canEdit"
                                    :style="{color: abc.tslh.end ? '' : '#AAA'}"
                                    @change="chSettingChanged('bi_chart.tslh.end')"
                            >
                                <option :style="{color: '#AAA'}" disabled :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.field">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="">
                        <div class="inline-fld">
                            <label class="label-width">Tooltip 1:</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.tslh.tooltip1"
                                    :disabled="!canEdit"
                                    :style="{color: abc.tslh.tooltip1 ? '' : '#AAA'}"
                                    @change="chSettingChanged('bi_chart.tslh.tooltip1')"
                            >
                                <option :style="{color: '#AAA'}" :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.id">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                        <div class="inline-fld">
                            <label class="label-width">Tooltip 2:</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.tslh.tooltip2"
                                    :disabled="!canEdit"
                                    :style="{color: abc.tslh.tooltip2 ? '' : '#AAA'}"
                                    @change="chSettingChanged('bi_chart.tslh.tooltip2')"
                            >
                                <option :style="{color: '#AAA'}" :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.id">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="">
                        <div class="inline-fld">
                            <label class="label-width">Front ID:</label>
                            <select
                                    class="form-group form-control control-200"
                                    v-model="abc.tslh.front_fld"
                                    :disabled="!canEdit"
                                    :style="{color: abc.tslh.front_fld ? '' : '#AAA'}"
                                    @change="chSettingChanged('bi_chart.tslh.front_fld')"
                            >
                                <option :style="{color: '#AAA'}" :value="null">Field</option>
                                <option v-for="fld in tableMeta._fields" :style="{color: '#444'}" :value="fld.id">
                                    {{ $root.uniqName( fld.name ) }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END CHART PART -->

        </div>

        <info-popup v-if="export_confirm_show"
                    :pop_width="400"
                    :title_html="'Confirm to Export'"
                    :content_html="'All existing data in the target table will be erased!'"
                    :add_btn="'Proceed'"
                    :cancel_btn="'Cancel'"
                    @hide="export_confirm_show = false"
                    @add-click="exportToTable()"
                    @cancel-click="export_confirm_show = false"
        ></info-popup>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";
    import {ChartFunctions} from './ChartFunctions';

    import SortFieldsForVerticalMixin from "./../../../../_Mixins/SortFieldsForVerticalMixin.vue";

    import VueCkeditor from 'vue-ckeditor2';
    import BiChartSettingsVariable from "./BiChartSettingsVariable";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import SelectWithFolderStructure from "../../../../CustomCell/InCell/SelectWithFolderStructure";
    import InfoPopup from "../../../../CustomPopup/InfoPopup";

    export default {
        name: "BiChartSettings",
        components: {
            InfoPopup,
            SelectWithFolderStructure,
            BiChartSettingsVariable,
            SelectBlock,
            VueCkeditor,
        },
        mixins: [
            SortFieldsForVerticalMixin,
        ],
        data: function () {
            return {
                horToLVL: null,
                horFromLVL: null,
                vertToLVL: null,
                vertFromLVL: null,
                aboutToLVL: null,
                aboutFromLVL: null,

                //activeTab: 'table',
                available_types: ['area', 'bar' ,'column', 'line', 'pie', 'TSLH'],

                //#app_avail_formulas
                calc_values: [
                    {func: '1', show: 'Value'},
                    {func: '0', show: 'Count'},
                    {func: '-1', show: 'Countunique'},
                ],
                group_functions: [
                    {val: 'sum', show: 'SUM'},
                    {val: 'min', show: 'MIN'},
                    {val: 'max', show: 'MAX'},
                    {val: 'mean', show: 'MEAN'},
                    {val: 'avg', show: 'AVG'},
                    {val: 'var', show: 'VAR'},
                    {val: 'std', show: 'STD'},
                ],
                should_save: false,

                conf_ready: false,
                config_ck: {
                    allowedContent: true,
                    height: 500,
                    toolbarGroups: [
                        { name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
                        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
                        { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
                        '/',
                        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
                        '/',
                        { name: 'styles' },
                        { name: 'colors' },
                        { name: 'tools' },
                        { name: 'others' },
                        { name: 'about' },
                    ],
                },
                ckeditor_content: this.all_settings.text_data.content,
                text_tab: 'editor',
                var_table: ChartFunctions.empVar('table'),
                var_chart: ChartFunctions.empVar('chart'),
                vars_tabs: 'tables',
                forbiddenColumns: this.$root.systemFields,
                formhelper: { 1: false, 2: false, 3: false, },
                export_confirm_show: false,
            }
        },
        props:{
            tableMeta: Object,
            canEdit: Boolean,
            all_settings: Object,
            request_params: Object,
        },
        computed: {
            apt_fld_meta() {
                let obj = {};
                let somemsel = false;
                for (let i = 1; i <= this.apt.hor_l; i++) {
                    obj['hor'+i] = _.find(this.tableMeta._fields, {'field': this.apt.horizontal['l'+i+'_field']});
                    obj['hor'+i+'_ismsel'] = obj['hor'+i] && this.$root.isMSEL(obj['hor'+i].input_type);
                    somemsel = somemsel || obj['hor'+i+'_ismsel'];
                }
                for (let i = 1; i <= this.apt.vert_l; i++) {
                    obj['vert'+i] = _.find(this.tableMeta._fields, {'field': this.apt.vertical['l'+i+'_field']});
                    obj['vert'+i+'_ismsel'] = obj['vert'+i] && this.$root.isMSEL(obj['vert'+i].input_type);
                    somemsel = somemsel || obj['vert'+i+'_ismsel'];
                }
                obj['some_has_mselect'] = somemsel;
                return obj;
            },
            apt() {
                return this.all_settings.pivot_table;
            },
            abc() {
                return this.all_settings.bi_chart;
            },
            max_hor() {
                return ChartFunctions.maxHor();
            },
            max_vert() {
                return ChartFunctions.maxVert();
            },
            max_about() {
                return ChartFunctions.maxAbout();
            },
            y_axis_group_function() {
                return _.filter(_.split(this.abc.y_axis.group_function, ','));
            },
        },
        watch: {
            ckeditor_content(val) {
                this.all_settings.text_data.content = val;
                this.should_save = true;
            },
        },
        methods: {
            storeXaxisGroupFunction(opt) {
                let arr = _.clone(this.y_axis_group_function);
                if (arr.indexOf(opt.val) > -1) {
                    arr.splice( arr.indexOf(opt.val), 1 );
                } else {
                    arr.push(opt.val);
                }
                this.abc.y_axis.group_function = _.join(arr, ',');
                this.chSettingChanged('bi_chart.y_axis.group_function');
            },
            reorderLevels(type) {
                if (type === 'about' && this.aboutFromLVL != this.aboutToLVL) {
                    this.all_settings = ChartFunctions.reorderSettings(this.all_settings, type, this.aboutFromLVL, this.aboutToLVL);
                    this.sendChangeSignal(this.canEdit ? 'about_reorder' : '__update_cache');
                }
                if (type === 'vertical' && this.vertFromLVL != this.vertToLVL) {
                    this.all_settings = ChartFunctions.reorderSettings(this.all_settings, type, this.vertFromLVL, this.vertToLVL);
                    this.sendChangeSignal(this.canEdit ? 'vertical_reorder' : '__update_cache');
                }
                if (type === 'horizontal' && this.horFromLVL != this.horToLVL) {
                    this.all_settings = ChartFunctions.reorderSettings(this.all_settings, type, this.horFromLVL, this.horToLVL);
                    this.sendChangeSignal(this.canEdit ? 'horizontal_reorder' : '__update_cache');
                }
                this.horFromLVL = null;
                this.horToLVL = null;
                this.vertFromLVL = null;
                this.vertToLVL = null;
                this.aboutFromLVL = null;
                this.aboutToLVL = null;
            },
            recreateForm(param) {
                this.formhelper[param] = false;
                this.$nextTick(() => {
                    this.formhelper[param] = true;
                });
            },
            abo(lvl) {
                return lvl === 1 ? 'about' : 'about_'+lvl;
            },
            //vars
            chobj(ch_id) {
                return _.find(this.tableMeta._bi_charts, {id: Number(ch_id)});
            },
            cha_about(vt) {
                let cho = this.chobj(vt.chart_id);
                return cho ? this.fldName(cho.chart_settings.bi_chart.y_axis.field) : '';
            },
            fldName(db_field) {
                let fld = _.find(this.tableMeta._fields, {field: db_field});
                return fld ? fld.name : db_field;
            },
            delTabl(i) {
                this.all_settings.text_data.vars_table.splice(i,1);
                this.variableSave('vars_table');
            },
            delChar(i) {
                this.all_settings.text_data.vars_chart.splice(i,1);
                this.variableSave('vars_chart');
            },
            addTabl() {
                this.all_settings.text_data.vars_table.push(this.var_table);
                this.variableSave('vars_table');
                this.var_table = ChartFunctions.empVar('table');
            },
            addChar() {
                this.all_settings.text_data.vars_chart.push(this.var_chart);
                this.variableSave('vars_chart');
                this.var_chart = ChartFunctions.empVar('chart');
            },
            variableSave(type) {
                if (this.canEdit) {
                    this.sendChangeSignal(type);
                }
            },
            setTit(type) {
                let find = (type === 'chart' ? this.var_chart : this.var_table);
                let ch = _.find(this.tableMeta._bi_charts, {id: find.chart_id});
                find.title = ch ? ch.title : '';
            },
            get_vals(type, key) {
                let find_id = Number(type === 'chart' ? this.var_chart.chart_id : this.var_table.chart_id);
                let data = (type === 'chart' ? 'chart_data' : 'table_data');
                let ch = _.find(this.tableMeta._bi_charts, {id: Number(find_id)});
                return ch && ch.cached_data
                    ? _.groupBy(ch.cached_data[data], key)
                    : [];
            },
            //others
            changedType() {
                this.abc.chart_type = 'basic';
                this.abc.x_axis.l1_group_fld = null;
                this.abc.x_axis.l2_group_fld = null;
            },
            changedXfield() {
                this.abc.x_axis.l1_group_fld = null;
                this.abc.x_axis.l2_group_fld = null;
            },
            changedL1X() {
                this.abc.x_axis.l2_group_fld = null;
            },
            chSettingChanged(type, obj, key) {
                if (obj && key) {
                    obj[key] = !obj[key];
                }
                this.should_save = true;
            },
            sendChangeSignal(paramName) {
                this.$emit('settings-changed', this.all_settings, paramName);
                this.should_save = false;
            },
            clearFields(key) {
                key === 'horizontal' ? this.clearExcludedH() : this.clearExcludedV();
            },
            clearExcludedH() {
                let obj = {};
                for (let i = 1; i <= ChartFunctions.maxHor(); i++) {
                    if (i > this.apt.hor_l) {
                        this.apt.horizontal['l'+i+'_field'] = null;
                        this.apt.horizontal['l'+i+'_sub_total'] = false;
                    }
                    if (this.apt.horizontal['l'+i+'_field']) { obj[this.apt.horizontal['l'+i+'_field']] = []; }
                }
                this.all_settings.excluded_hors = obj;
            },
            clearExcludedV() {
                let obj = {};
                for (let i = 1; i <= ChartFunctions.maxVert(); i++) {
                    if (i > this.apt.vert_l) {
                        this.apt.vertical['l'+i+'_field'] = null;
                        this.apt.vertical['l'+i+'_sub_total'] = false;
                    }
                    if (this.apt.vertical['l'+i+'_field']) { obj[this.apt.vertical['l'+i+'_field']] = []; }
                }
                this.all_settings.excluded_verts = obj;
            },

            //Referenced Tables
            refFieldChanged(refObj, type, lvl) {
                if (type === 'horizontal') {
                    this.clearExcludedH();
                } else {
                    this.clearExcludedV();
                }

                if (this.apt.referenced_tables && to_float(refObj.val)) {//is referenced field with 'id' instead 'field'.
                    this.apt[type]['l'+lvl+'_reference'] = refObj.val;
                    this.apt[type]['l'+lvl+'_field'] = refObj.main_fld;
                    this.apt[type]['l'+lvl+'_ref_link'] = refObj.ref_link;
                } else {
                    this.apt[type]['l'+lvl+'_field'] = refObj.val;
                    this.apt[type]['l'+lvl+'_reference'] = refObj.val;
                    this.apt[type]['l'+lvl+'_ref_link'] = refObj.ref_link;
                }

                this.chSettingChanged('pivot_table.'+type+'.l'+lvl+'_field');
            },
            referencedChange() {
                this.apt.referenced_tables = !this.apt.referenced_tables;
                if (this.apt.referenced_tables) {
                    for (let i = 1; i <= ChartFunctions.maxLVL(); i++) {
                        this.apt.vertical['l'+i+'_reference'] = this.apt.vertical['l'+i+'_field'];
                        this.apt.horizontal['l'+i+'_reference'] = this.apt.horizontal['l'+i+'_field'];
                    }
                } else {
                    for (let i = 1; i <= ChartFunctions.maxLVL(); i++) {
                        this.apt.vertical['l'+i+'_field'] = this.apt.vertical['l'+i+'_reference'] == this.apt.vertical['l'+i+'_field'] ? this.apt.vertical['l'+i+'_field'] : null;
                        this.apt.horizontal['l'+i+'_field'] = this.apt.horizontal['l'+i+'_reference'] == this.apt.horizontal['l'+i+'_field'] ? this.apt.horizontal['l'+i+'_field'] : null;
                        this.apt.vertical['l'+i+'_reference'] = null;
                        this.apt.horizontal['l'+i+'_reference'] = null;
                        this.apt.vertical['l'+i+'_ref_link'] = null;
                        this.apt.horizontal['l'+i+'_ref_link'] = null;
                    }
                }
                this.chSettingChanged('pivot_table.referenced_tables');
            },
            getFieldsAndRef() {
                let sorted_main = this.sortAndFilterFields(this.tableMeta, this.tableMeta._fields, {}, true);
                let fields = _.map(sorted_main, (ff) => {
                    return {
                        val: ff.field,
                        show: this.$root.uniqName( ff.name ),
                        style: { color: '#00F' },
                    };
                });

                fields.unshift({
                    val: null,
                    show: 'Field',
                    style: { color: '#AAA' },
                    main_fld: null,
                    ref_link: null,
                });//empty val

                if (this.apt.referenced_tables) {
                    _.each(this.tableMeta._fields, (source_fld) => {
                        if (source_fld.input_type !== 'Input' && source_fld.ddl_id) {
                            let ddl = _.find(this.tableMeta._ddls, {id: Number(source_fld.ddl_id)});
                            if (ddl && ddl._references && ddl._references.length) {
                                fields.push({
                                    val: null,
                                    show: ddl.name,
                                    style: { color: '#777', backgroundColor: '#448' },
                                    isTitle: true,
                                });
                                _.each(ddl._references, (ref) => {
                                    let rc = _.find(this.tableMeta._ref_conditions, {id: Number(ref.table_ref_condition_id)});
                                    let ref_tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(rc ? rc.ref_table_id : null)});
                                    //Just for DDL 'id/show'
                                    if (!rc.target_field_id && ref_tb && ref_tb._fields) {
                                        let sorted_ref = _.sortBy(ref_tb._fields, 'order');
                                        sorted_ref = this.sortAndFilterFields(ref_tb, sorted_ref, {}, true);
                                        _.each(sorted_ref || [], (ref_fld) => {
                                            let ref_link = _.find(ref_tb._fields, {id: Number(ref.target_field_id)});
                                            fields.push({
                                                val: ref_fld.id,
                                                show: this.$root.uniqName( ref_fld.name ),
                                                style: { color: '#444' },
                                                main_fld: source_fld.field,
                                                ref_link: ref_link ? ref_link.field : 'id',
                                            });
                                        });
                                    }
                                });
                            }
                        }
                    });
                }
                return fields;
            },

            //export
            exportConfirm() {
                this.export_confirm_show = true;
            },
            exportToTable() {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table/chart/export', {
                    chart_settings: this.all_settings,
                    target_table_id: this.all_settings.table_to_export,
                    request_params: SpecialFuncs.dataRangeRequestParams(this.all_settings.data_range, this.tableMeta.id, this.request_params),
                }).then(({ data }) => {
                    Swal('Info', 'Export is finished!');
                    this.all_settings.table_to_export = data.new_id;
                    this.sendChangeSignal('');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
                this.export_confirm_show = false;
            },
            tableToExpUpdate(val) {
                this.all_settings.table_to_export = val;
                this.sendChangeSignal('');

                axios.put('/ajax/table/chart/settings', {
                    chart_id: this.all_settings.id,
                    chart_settings: {table_to_export: val},
                });
            },
        },
        created() {
        },
        mounted() {
            if (this.all_settings.elem_type === 'text_data' && this.$refs.ch_body) {
                let bounds = this.$refs.ch_body.getBoundingClientRect();
                this.config_ck.height = bounds.height*10 - 145;//bounds.height*10 - because of popupAminationMixin //110 - height of panels
                this.conf_ready = true;
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "BiModule";

    .chart-bsett {
        height: calc(100% - 50px);
        padding: 0 5px;
    }
    .l1-part-header {
        width: 70px;
    }
    .table_settings {
        width: calc(100% - 1px);
        border-collapse: collapse;
        table-layout: fixed;

        th,td {
            vertical-align: middle;
            text-align: center;
            border: 1px solid #777;
            padding: 2px 5px;
        }
        th {
            background-color: #DDD;
            font-weight: bold;
            line-height: 1em;
        }
        .td--th {
            text-align: left;
            font-weight: bold;
        }
    }
    .form-group {
        margin-bottom: 5px;
    }
    .ck_textarea {
        margin-top: 5px;
        height: calc(100% - 5px);
    }
    .opac {
        opacity: 0.5;
    }
    .p-sm {
        padding: 5px;
    }
    select {
        .ref-option {
            &:hover {
                text-decoration: underline;
            }
        }
    }
</style>

<style lang="scss">
    .bcs-swfs-wrapper {
        .select2-container {
            height: 36px !important;
            z-index: 1000;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px;
        }
    }
</style>