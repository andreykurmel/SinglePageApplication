<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <div class="permissions-panel full-height">
                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'overall'}" :style="textSysStyle" @click="activeTab = 'overall'">
                        Overall
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'title'}" :style="textSysStyle" @click="activeTab = 'title'">
                        Title
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'form'}" :style="textSysStyle" @click="activeTab = 'form'">
                        Form
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'top_msg'}" :style="textSysStyle" @click="activeTab = 'top_msg'">
                        Top Message
                    </button>

                    <div class="pull-right flex" v-if="with_edit" style="position: relative;top: -2px;">
                        <div class="flex flex--center">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="templateClick">
                                    <i v-if="requestRow['is_template'] == 1" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <label style="margin: 0;">&nbsp;Template</label>
                        </div>
                        <div class="flex flex--center" style="margin-left: 10px;">
                            <select class="form-control" v-model="from_dcr_id" style="width: 150px;height: 26px;padding: 0 3px;">
                                <option v-for="tmplte in $root.settingsMeta.template_dcrs"
                                        v-if="tmplte.id != requestRow.id"
                                        :style="{color: tmplte._table.user_id == $root.user.id ? '#00F' : ''}"
                                        :value="tmplte.id">{{ tmplte.name }}</option>
                            </select>
                            <button style="padding: 0 5px;" class="btn btn-success" :disabled="!from_dcr_id" @click="copyDcrDesign">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="permissions-menu-body">

                    <!--OVERALL-->
                    <div class="full-frame defaults-tab" v-show="activeTab === 'overall'">
                        <table class="spaced-table">
                            <tbody v-if="requestFields">
                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--20 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_sec_scroll_style'].name) }}&nbsp;:&nbsp;</label>
                                            <select class="form-control"
                                                    :style="textSysStyle"
                                                    :disabled="!with_edit"
                                                    v-model="requestRow['dcr_sec_scroll_style']"
                                                    style="max-width: 110px;"
                                                    @change="updatedCell">
                                                <option value="scroll">Scroll</option>
                                                <option value="flow">Flow</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--100 h-32" :style="getTdStyle">
                                            <label>Division Line:&nbsp;&nbsp;&nbsp;</label>
                                            <label>{{ $root.uniqName(requestFields['dcr_sec_line_top'].name) }}&nbsp;:&nbsp;</label>
                                            <label class="switch_t">
                                                <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_sec_line_top']" @change="updatedCell">
                                                <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                                            </label>
                                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_sec_line_bot'].name) }}&nbsp;:&nbsp;</label>
                                            <label class="switch_t">
                                                <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_sec_line_bot']" @change="updatedCell">
                                                <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                                            </label>
                                            <label>&nbsp;&nbsp;&nbsp;&nbsp;</label>

                                            <label>{{ $root.uniqName(requestFields['dcr_sec_line_color'].name) }}&nbsp;:&nbsp;</label>
                                            <div class="color-wrapper clr-min">
                                                <tablda-colopicker
                                                        :init_color="requestRow['dcr_sec_line_color']"
                                                        :fixed_pos="true"
                                                        :can_edit="with_edit"
                                                        :avail_null="true"
                                                        class="h-32"
                                                        @set-color="updateSecColorLine"
                                                ></tablda-colopicker>
                                            </div>
                                            <label>&nbsp;&nbsp;&nbsp;&nbsp;</label>

                                            <label>{{ $root.uniqName(requestFields['dcr_sec_line_thick'].name) }}:&nbsp;</label>
                                            <input type="number"
                                                   :style="textSysStyle"
                                                   v-model="requestRow['dcr_sec_line_thick']"
                                                   :disabled="!with_edit"
                                                   @change="updatedCell"
                                                   class="form-control"
                                                   style="max-width: 60px"/>
                                            <label>px</label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--30 h-32" :style="getTdStyle">
                                            <label ref="sec_tooltip_bgc" @mouseover="showSecBGC">{{ $root.uniqName(requestFields['dcr_sec_background_by'].name) }}:&nbsp;&nbsp;</label>
                                            <select class="form-control"
                                                    :style="textSysStyle"
                                                    :disabled="!with_edit"
                                                    v-model="requestRow['dcr_sec_background_by']"
                                                    @change="updatedCell"
                                            >
                                                <option value="color">Color</option>
                                                <option value="image">Image</option>
                                            </select>
                                        </div>
                                        <div class="flex flex--center-v td td--60 h-32" :style="getTdStyle">
                                            <template v-if="requestRow['dcr_sec_background_by'] == 'color'">
                                                <hover-block v-if="sec_tooltip_bgc && requestFields['dcr_sec_bg_top'].tooltip"
                                                             :html_str="requestFields['dcr_sec_bg_top'].tooltip"
                                                             :p_left="help_left"
                                                             :p_top="help_top"
                                                             :c_offset="help_offset"
                                                             @another-click="sec_tooltip_bgc = false"
                                                             @tooltip-blur="sec_tooltip_bgc = false"
                                                ></hover-block>
                                                <label>&nbsp;{{ $root.uniqName(requestFields['dcr_sec_bg_top'].name) }}&nbsp;:&nbsp;</label>
                                                <div class="color-wrapper clr-min">
                                                    <tablda-colopicker
                                                            :init_color="requestRow['dcr_sec_bg_top']"
                                                            :fixed_pos="true"
                                                            :can_edit="with_edit"
                                                            :avail_null="true"
                                                            class="h-32"
                                                            @set-color="updateSecTopColor"
                                                    ></tablda-colopicker>
                                                </div>
                                                <label>&nbsp;{{ $root.uniqName(requestFields['dcr_sec_bg_bot'].name) }}&nbsp;:&nbsp;</label>
                                                <div class="color-wrapper clr-min">
                                                    <tablda-colopicker
                                                            :init_color="requestRow['dcr_sec_bg_bot']"
                                                            :fixed_pos="true"
                                                            :can_edit="with_edit"
                                                            :avail_null="true"
                                                            class="h-32"
                                                            @set-color="updateSecBotColor"
                                                    ></tablda-colopicker>
                                                </div>
                                            </template>

                                            <template v-if="requestRow['dcr_sec_background_by'] == 'image'">
                                                <hover-block v-if="sec_bgi_tooltip && requestFields['dcr_sec_bg_img'].tooltip"
                                                             :html_str="requestFields['dcr_sec_bg_img'].tooltip"
                                                             :p_left="help_left"
                                                             :p_top="help_top"
                                                             :c_offset="help_offset"
                                                             @another-click="sec_bgi_tooltip = false"
                                                             @tooltip-blur="sec_bgi_tooltip = false"
                                                ></hover-block>
                                                <label ref="sec_bgi_tooltip" @mouseover="showBGISec">&nbsp;{{ $root.uniqName(requestFields['dcr_sec_bg_img'].name) }}:&nbsp;</label>
                                                <img v-if="requestRow['dcr_sec_bg_img']"
                                                     :src="$root.fileUrl({url:requestRow['dcr_sec_bg_img']})"
                                                     class="img-preview h-32"
                                                />
                                                <input type="file" :style="textSysStyle" ref="bg_img_sec" :disabled="!with_edit" @change="uploadSecFile" class="form-control"/>
                                                <button
                                                        v-if="requestRow['dcr_sec_bg_img']"
                                                        class="btn flex flex--center btn-danger btn-del-bg"
                                                        :disabled="!with_edit"
                                                        @click="delSecFile"
                                                >&times;</button>
                                                <label>&nbsp;{{ $root.uniqName(requestFields['dcr_sec_bg_img_fit'].name) }}:&nbsp;</label>
                                                <select v-model="requestRow['dcr_sec_bg_img_fit']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                                                    <option>Height</option>
                                                    <option>Width</option>
                                                    <option>Fill</option>
                                                </select>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!--TITLE-->
                    <div class="full-frame defaults-tab" v-show="activeTab === 'title'">
                        <table class="spaced-table">
                            <tbody v-if="requestFields">
                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--100 h-32" :style="getTdStyle">
                                            <!--<label>{{ $root.uniqName(requestFields['dcr_title'].name) }}:&nbsp;</label>-->
                                            <input type="text" :style="textSysStyle" ref="dcr_title_input" v-model="requestRow['dcr_title']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                                            <cell-table-data-expand
                                                    v-if="dcr_title_expand"
                                                    style="background-color: #FFF; bottom: 5px; right: 3px;"
                                                    :table-meta="$root.settingsMeta['table_permissions']"
                                                    :table-row="requestRow"
                                                    :table-header="requestFields['dcr_title']"
                                                    :html="requestRow['dcr_title']"
                                                    :uniqid="titleuniqid"
                                                    :can-edit="with_edit"
                                                    :user="$root.user"
                                            ></cell-table-data-expand>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_title_font_type'].name) }}:&nbsp;</label>
                                            <select v-model="requestRow['dcr_title_font_type']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                                                <option v-for="fnt in avail_fonts">{{ fnt }}</option>
                                            </select>
                                        </div>
                                        <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_title_font_size'].name) }}:&nbsp;</label>
                                            <input type="number" :style="textSysStyle" v-model="requestRow['dcr_title_font_size']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                                            <label>pt</label>
                                        </div>
                                        <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_title_font_color'].name) }}:&nbsp;</label>
                                            <div class="color-wrapper clr-min">
                                                <tablda-colopicker
                                                        :init_color="requestRow['dcr_title_font_color']"
                                                        :fixed_pos="true"
                                                        :can_edit="with_edit"
                                                        :avail_null="true"
                                                        @set-color="updateColorFont"
                                                ></tablda-colopicker>
                                            </div>
                                        </div>
                                        <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_title_font_style'].name) }}:&nbsp;</label>
                                            <div class="full-height full-width" style="position:relative;">
                                                <tablda-select-simple
                                                        :options="[
                                                            {val: 'Normal', show: 'Normal'},
                                                            {val: 'Italic', show: 'Italic'},
                                                            {val: 'Bold', show: 'Bold'},
                                                            {val: 'Strikethrough', show: 'Strikethrough'},
                                                            {val: 'Overline', show: 'Overline'},
                                                            {val: 'Underline', show: 'Underline'},
                                                        ]"
                                                        :table-row="requestRow"
                                                        :hdr_field="'dcr_title_font_style'"
                                                        :fld_input_type="'M-Select'"
                                                        :style="getEditStyle"
                                                        :is_disabled="!with_edit"
                                                        @selected-item="(item) => {updateMSelect(item, 'dcr_title_font_style')}"
                                                ></tablda-select-simple>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--75 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_title_width'].name) }}:&nbsp;</label>
                                            <input type="number" :style="textSysStyle" v-model="requestRow['dcr_title_width']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                                            <label>px</label>

                                            <label>&nbsp;&nbsp;{{ $root.uniqName(requestFields['dcr_title_height'].name) }}:&nbsp;</label>
                                            <input type="number" :style="textSysStyle" v-model="requestRow['dcr_title_height']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                                            <label>px</label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--40 h-32" :style="getTdStyle">
                                            <label ref="sec_tooltip_bgc" @mouseover="showSecBGC">{{ $root.uniqName(requestFields['dcr_title_background_by'].name) }}:&nbsp;&nbsp;</label>
                                            <select class="form-control" :style="textSysStyle" :disabled="!with_edit" v-model="requestRow['dcr_title_background_by']" @change="updatedCell">
                                                <option value="color">Color</option>
                                                <option value="image">Image</option>
                                            </select>
                                        </div>
                                        <div class="flex flex--center-v td td--60 h-32" :style="getTdStyle">
                                            <template v-if="requestRow['dcr_title_background_by'] == 'color'">
                                                <hover-block v-if="tit_tooltip_bgc && requestFields['dcr_title_bg_color'].tooltip"
                                                             :html_str="requestFields['dcr_title_bg_color'].tooltip"
                                                             :p_left="help_left"
                                                             :p_top="help_top"
                                                             :c_offset="help_offset"
                                                             @another-click="tit_tooltip_bgc = false"
                                                             @tooltip-blur="tit_tooltip_bgc = false"
                                                ></hover-block>
                                                <label ref="tit_tooltip_bgc" @mouseover="showTitBGC">{{ $root.uniqName(requestFields['dcr_title_bg_color'].name) }}:&nbsp;</label>
                                                <div class="color-wrapper">
                                                    <tablda-colopicker
                                                            :init_color="requestRow['dcr_title_bg_color']"
                                                            :fixed_pos="true"
                                                            :can_edit="with_edit"
                                                            :avail_null="true"
                                                            @set-color="updateColorBg"
                                                    ></tablda-colopicker>
                                                </div>
                                            </template>

                                            <template v-if="requestRow['dcr_title_background_by'] == 'image'">
                                                <hover-block v-if="tit_bgi_tooltip && requestFields['dcr_title_bg_img'].tooltip"
                                                             :html_str="requestFields['dcr_title_bg_img'].tooltip"
                                                             :p_left="help_left"
                                                             :p_top="help_top"
                                                             :c_offset="help_offset"
                                                             @another-click="tit_bgi_tooltip = false"
                                                             @tooltip-blur="tit_bgi_tooltip = false"
                                                ></hover-block>
                                                <label ref="tit_bgi_tooltip" @mouseover="showBGITit">{{ $root.uniqName(requestFields['dcr_title_bg_img'].name) }}:&nbsp;</label>
                                                <img v-if="requestRow['dcr_title_bg_img']"
                                                     :src="$root.fileUrl({url:requestRow['dcr_title_bg_img']})"
                                                     class="img-preview h-32"
                                                />
                                                <input type="file" :style="textSysStyle" ref="bg_img" :disabled="!with_edit" @change="uploadFile" class="form-control"/>
                                                <button
                                                        v-if="requestRow['dcr_title_bg_img']"
                                                        class="btn flex flex--center btn-danger btn-del-bg"
                                                        :disabled="!with_edit"
                                                        @click="delFile"
                                                >&times;</button>

                                                <label>&nbsp;{{ $root.uniqName(requestFields['dcr_title_bg_fit'].name) }}:&nbsp;</label>
                                                <select v-model="requestRow['dcr_title_bg_fit']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                                                    <option>Height</option>
                                                    <option>Width</option>
                                                    <option>Fill</option>
                                                </select>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!--FORM-->
                    <div class="full-frame defaults-tab" v-show="activeTab === 'form'">
                        <table class="spaced-table">
                            <tbody v-if="requestFields">
                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--100 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_form_width'].name) }}:&nbsp;</label>
                                            <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_width']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                                            <label>&nbsp;px&nbsp;</label>

                                            <hover-block v-if="form_tooltip_bgc && requestFields['dcr_form_bg_color'].tooltip"
                                                         :html_str="requestFields['dcr_form_bg_color'].tooltip"
                                                         :p_left="help_left"
                                                         :p_top="help_top"
                                                         :c_offset="help_offset"
                                                         @another-click="form_tooltip_bgc = false"
                                                         @tooltip-blur="form_tooltip_bgc = false"
                                            ></hover-block>
                                            <label ref="form_tooltip_bgc" @mouseover="showFormBGC">&nbsp;{{ $root.uniqName(requestFields['dcr_form_bg_color'].name) }}&nbsp;:&nbsp;</label>
                                            <div class="color-wrapper">
                                                <tablda-colopicker
                                                        :init_color="requestRow['dcr_form_bg_color']"
                                                        :fixed_pos="true"
                                                        :can_edit="with_edit"
                                                        :avail_null="true"
                                                        class="h-32"
                                                        @set-color="updateColorFormBg"
                                                ></tablda-colopicker>
                                            </div>

                                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_form_transparency'].name) }}:&nbsp;</label>
                                            <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_transparency']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                                            <label>&nbsp;%</label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--100 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_form_line_type'].name) }}:&nbsp;</label>
                                            <select v-model="requestRow['dcr_form_line_type']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                                                <option value="line">Line</option>
                                                <option value="space">Space</option>
                                            </select>

                                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_form_line_top'].name) }}&nbsp;:&nbsp;</label>
                                            <label class="switch_t">
                                                <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_form_line_top']" @change="updatedCell">
                                                <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                                            </label>
                                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_form_line_bot'].name) }}&nbsp;:&nbsp;</label>
                                            <label class="switch_t">
                                                <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_form_line_bot']" @change="updatedCell">
                                                <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                                            </label>

                                            <template v-if="requestRow['dcr_form_line_type'] === 'line'">
                                                <label>&nbsp;{{ $root.uniqName(requestFields['dcr_form_line_color'].name) }}&nbsp;:&nbsp;</label>
                                                <div class="color-wrapper clr-min">
                                                    <tablda-colopicker
                                                            :init_color="requestRow['dcr_form_line_color']"
                                                            :fixed_pos="true"
                                                            :can_edit="with_edit"
                                                            :avail_null="true"
                                                            class="h-32"
                                                            @set-color="updateColorLine"
                                                    ></tablda-colopicker>
                                                </div>
                                            </template>

                                            <template v-if="requestRow['dcr_form_line_type'] === 'space'">
                                                <label>&nbsp;{{ $root.uniqName(requestFields['dcr_form_line_radius'].name) }}:&nbsp;</label>
                                                <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_line_radius']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                                                <label>px</label>
                                            </template>

                                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_form_line_thick'].name) }}:&nbsp;</label>
                                            <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_line_thick']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                                            <label>px</label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--100 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_form_shadow'].name) }}:&nbsp;</label>
                                            <label class="switch_t">
                                                <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_form_shadow']" @change="updatedCell">
                                                <span class="toggler round" :class="[!with_edit ? 'disabled' : '']"></span>
                                            </label>

                                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_form_shadow_color'].name) }}:&nbsp;</label>
                                            <div class="color-wrapper clr-min">
                                                <tablda-colopicker
                                                        :init_color="requestRow['dcr_form_shadow_color']"
                                                        :fixed_pos="true"
                                                        :can_edit="with_edit"
                                                        :avail_null="true"
                                                        @set-color="updateShadowColor"
                                                ></tablda-colopicker>
                                            </div>

                                            <label>&nbsp;{{ $root.uniqName(requestFields['dcr_form_shadow_dir'].name) }}:&nbsp;</label>
                                            <select v-model="requestRow['dcr_form_shadow_dir']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                                                <option value="BR">BR</option>
                                                <option value="BL">BL</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td :style="getTdStyle">
                                        <div class="flex flex--center-v td td--100 h-32" :style="getTdStyle">
                                            <label>{{ $root.uniqName(requestFields['dcr_form_line_height'].name) }}:&nbsp;</label>
                                            <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_line_height']" :disabled="!with_edit" @change="updatedCell" class="form-control max-sm"/>
                                            <label>&nbsp;px&nbsp;</label>

                                            <label>&nbsp;&nbsp;&nbsp;{{ $root.uniqName(requestFields['dcr_form_font_size'].name) }}:&nbsp;</label>
                                            <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_font_size']" :disabled="!with_edit" @change="updatedCell" class="form-control max-sm"/>
                                            <label>&nbsp;px&nbsp;</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!--FORM-->
                    <div class="full-frame defaults-tab" v-if="activeTab === 'top_msg'">
                        <tab-ckeditor
                                :table-meta="tableMeta"
                                :target-row="requestRow"
                                :field-name="'dcr_form_message'"
                                @save-row="updatedCell"
                        ></tab-ckeditor>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    import {eventBus} from '../../../../../app';

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";
    import ReqRowMixin from "./ReqRowMixin.vue";

    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker.vue";
    import HoverBlock from "../../../../CommonBlocks/HoverBlock";
    import TabldaSelectSimple from "../../../../CustomCell/Selects/TabldaSelectSimple";
    import CellTableDataExpand from "../../../../CustomCell/InCell/CellTableDataExpand";
    import TabCkeditor from "../../../../CommonBlocks/TabCkeditor";

    export default {
        components: {
            TabCkeditor,
            CellTableDataExpand,
            TabldaSelectSimple,
            HoverBlock,
            TabldaColopicker,
        },
        mixins: [
            CellStyleMixin,
            ReqRowMixin,
        ],
        name: "TabSettingsRequestsRow",
        data: function () {
            return {
                from_dcr_id: null,
                titleuniqid: uuidv4(),
                topmsguniqid: uuidv4(),
                activeTab: 'overall',
            };
        },
        props:{
            table_id: Number,
            cellHeight: Number,
            maxCellRows: Number,
            tableRequest: Object,
            requestRow: Object,
            tableMeta: Object,
            with_edit: Boolean
        },
        computed: {
            getTdStyle() {
                return {
                    height: this.tdCellHGT+'px',
                    ...this.textSysStyle,
                };
            },
            dcr_title_expand() {
                return this.$refs.dcr_title_input
                    ? String(this.requestRow['dcr_title']).length * 8 > this.$refs.dcr_title_input.clientWidth
                    : false;
            },
            dcr_topmsg_expand() {
                return this.$refs.dcr_form_message_input
                    ? String(this.requestRow['dcr_form_message']).length * 8 > this.$refs.dcr_form_message_input.clientWidth
                    : false;
            },
        },
        watch: {
            table_id(val) {
                this.setAvailFields();
                this.activeTab = 'overall';
            },
        },
        methods: {
            copyDcrDesign() {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-data-request/copy', {
                    from_data_request_id: this.from_dcr_id,
                    to_data_request_id: this.requestRow.id,
                    as_template: false,
                }).then(({ data }) => {
                    if (data && _.first(data)) {
                        SpecialFuncs.assignProps(this.requestRow, _.first(data));
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            templateClick() {
                this.requestRow['is_template'] = this.requestRow['is_template'] == 1 ? 0 : 1;
                if (this.requestRow['is_template'] == 2) {
                    let permis = _.clone(this.requestRow);
                    permis._table = _.clone(this.tableMeta);
                    this.$root.settingsMeta.template_dcrs.push(permis);
                } else {
                    this.$root.settingsMeta.template_dcrs = _.filter(this.$root.settingsMeta.template_dcrs, (dcr) => {
                        return dcr.id != this.requestRow.id;
                    });
                }
                this.updatedCell();
            },
            tableDataStringUpdateHandler(uniq_id, val) {
                if (uniq_id === this.titleuniqid) {
                    this.requestRow['dcr_title'] = val;
                    this.updatedCell();
                }
                if (uniq_id === this.topmsguniqid) {
                    this.requestRow['dcr_form_message'] = val;
                    this.updatedCell();
                }
            },
        },
        mounted() {
            this.setAvailFields();
            eventBus.$on('table-data-string-popup__update', this.tableDataStringUpdateHandler);
        },
        beforeDestroy() {
            eventBus.$off('table-data-string-popup__update', this.tableDataStringUpdateHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettingsPermissions";
    @import "./ReqRowStyle";

    .ck_textarea {
        margin-top: 5px;
        height: 300px;
    }

    .add_link {
        white-space: nowrap;
        margin-bottom: 10px;

        label {
            margin: 0;
        }
        select {
            max-width: 250px;
            height: 30px;
            padding: 3px 6px;
        }
    }

    .max-sm {
        max-width: 75px;
    }
    .btn-default {
        height: 30px;
    }
</style>