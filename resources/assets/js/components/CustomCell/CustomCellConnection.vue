<template>
    <td :style="getCustomCellStyle()"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="getCustomWrapperStyle()">
                <div class="inner-content">

                    <label class="switch_t" v-if="headerFtype === 'Boolean'">
                        <input type="checkbox"
                               :disabled="disabledCheckBox"
                               v-model="tableRow[tableHeader.field]"
                               @click="disabledCheckBox ? '' : updateCheckBox()">
                        <span class="toggler round" :class="[disabledCheckBox ? 'disabled' : '']"></span>
                    </label>

                    <input
                            v-else-if="headerFtype === 'Radio' && !isAddRow"
                            :checked="tableRow[tableHeader.field]"
                            @click="updateRadio()"
                            type="radio"
                            ref="inline_input"
                            class="checkbox-input"/>

                    <moment-timezones
                            v-else-if="headerFtype === 'Timezone' && tableRow[tableHeader.field]"
                            :cur_tz="tableRow[tableHeader.field]"
                            :name="'Timezone'"
                            style="padding: 0;height: 16px;font-size: 13px;"
                            @changed-tz="updateCheckedDDL"
                    ></moment-timezones>

                    <span v-else-if="tableHeader.field === 'key'">{{ tableRow.key ? String(tableRow.key).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'login'">{{ tableRow.login ? String(tableRow.login).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'app_pass'">{{ tableRow.app_pass ? String(tableRow.app_pass).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'auth_token'">{{ tableRow.auth_token ? String(tableRow.auth_token).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'secret_key'">{{ tableRow.secret_key ? String(tableRow.secret_key).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'public_key'">{{ tableRow.public_key ? String(tableRow.public_key).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'twiml_app_id'">{{ tableRow.twiml_app_id ? String(tableRow.twiml_app_id).replace(/./gi, '*') : '' }}</span>

                    <a v-else-if="tableHeader.field === 'user_cloud_id'"
                       title="Open cloud settings in popup."
                       @click.stop="showCloudPopup()"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'table_view_id'"
                       title="Open views in popup."
                       @click.stop="showTbViewsPop()"
                    >{{ showField() }}</a>

                    <div v-else="">
                        <span v-if="!isAddRow && tableHeader.field === 'msg_to_user'">
                            <a v-if="tableRow.msg_to_user"
                               :href="tableRow.msg_to_user"
                               @click="linkClicked()"
                            >
                                <span>Connect</span>
                            </a>
                            <template v-else="">
                                <span style="color: #0A0">Connected</span>
                                <span class="inactivate-cloud" @click="inactivateCloudRow()">&times;</span>
                            </template>
                        </span>
                        <span v-else="" v-html="showField()"></span>
                    </div>
                    
                    <!--Tooltip-->
                    <tooltip-block
                            v-if="tableHeader.tooltip_show && tableHeader.tooltip"
                            :html_str="tableHeader.tooltip"
                            :outer_offset="1"
                            :left_move="5"
                            style="position: absolute; right: 0; top: calc(50% - 8px); text-align: left;"
                    ></tooltip-block>
                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <input
                    v-if="headerFtype === 'Password'"
                    type="password"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"
            />

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'cloud'"
                    :options="cloudOpt()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'type'"
                    :options="aiTypes()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'model'"
                    :options="aiModels()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'mode'"
                    :options="[
                        {val: 'sandbox', show: 'Sandbox'},
                        {val: 'live', show: 'Live'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'air_type'"
                    :options="[
                        {val: 'public_rest', show: 'Public REST'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'user_cloud_id'"
                    :options="userClouds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_view_id'"
                    :options="tableViews()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'day'"
                    :options="[
                        {val: 'Daily', show: 'Daily'},
                        {val: 'Monday', show: 'Monday'},
                        {val: 'Tuesday', show: 'Tuesday'},
                        {val: 'Wednesday', show: 'Wednesday'},
                        {val: 'Thursday', show: 'Thursday'},
                        {val: 'Friday', show: 'Friday'},
                        {val: 'Saturday', show: 'Saturday'},
                        {val: 'Sunday', show: 'Sunday'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <input v-else-if="inArray(headerFtype, ['Date', 'Date Time', 'Time'])"
                   ref="inline_input"
                   @blur="hideDatePicker()"
                   class="form-control full-height"
                   :style="getEditStyle"/>

            <input
                    v-else=""
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"/>

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {eventBus} from "../../app";

import {SpecialFuncs} from '../../classes/SpecialFuncs';

import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import MomentTimezones from "../MomentTimezones";
import TooltipBlock from "../CommonBlocks/TooltipBlock";

export default {
        components: {
            TooltipBlock,
            MomentTimezones,
            TabldaSelectSimple,
        },
        name: "CustomCellConnection",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                editing: false,
                oldValue: this.tableRow[this.tableHeader.field],
                editValue: null,
            }
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            rowIndex: Number,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            user: Object,
            cellValue: String|Number,
            special_extras: {
                type: Object,
                default: function () { return {}; }
            },
        },
        watch: {
            cellValue(val) {
                this.editValue = this.unitConvert(val);
            }
        },
        computed: {
            checkBoxOn() {
                return Boolean(this.editValue);
            },
            canCellEdit() {
                return this.headerFtype !== 'Radio'
                    && (this.headerFtype !== 'Timezone')
                    && (this.tableHeader.field !== 'cloud' || this.isAddRow)
                    && (this.tableHeader.field !== '_eml_pop')
                    && (this.tableHeader.field !== 'msg_to_user');
            },
            disabledCheckBox() {
                return !!this.$root.sm_msg_type;
            },
            headerFtype() {
                return this.special_extras.active_is_boolean && this.tableHeader.field == 'is_active'
                    ? 'Boolean'
                    : this.tableHeader.f_type;
            },
        },
        methods: {
            aiTypes() {
                return [
                    {val: 'openai', show: 'OpenAI'},
                    {val: 'gemini', show: 'Gemini'},
                ];
            },
            aiModels() {
                let models = {
                    'openai': [
                        {val: 'GPT-4.1', show: 'GPT-4.1'},
                        {val: 'GPT-4.1-mini', show: 'GPT-4.1-mini'},
                        {val: 'GPT-4.1-nano', show: 'GPT-4.1-nano'},
                        {val: 'GPT-4o', show: 'GPT-4o'},
                        {val: 'GPT-4o-mini', show: 'GPT-4o-mini'},
                        {val: 'o3', show: 'o3'},
                    ],
                    'gemini': [
                        {val: '2.5-Flash', show: '2.5 Flash'},
                        {val: '2.5-Pro', show: '2.5 Pro'},
                    ]
                };
                return models[this.tableRow.type] || [];
            },
            getCustomCellStyle() {
                let obj = this.getCellStyle();
                obj.textAlign = (this.inArray(this.headerFtype, ['Boolean', 'Radio']) ? 'center' : '');
                return obj;
            },
            getCustomWrapperStyle() {
                let obj = this.getWrapperStyle();
                if (this.headerFtype === 'Timezone') {
                    obj.overflow = 'visible';
                }
                if (this.special_extras.active_is_boolean && this.tableHeader.field == 'is_active') {
                    obj.maxHeight = '17px';
                }
                return obj;
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit() {
                if (!this.canCellEdit || this.inArray(this.headerFtype, ['Boolean'])) {
                    return;
                }
                //edit cell
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.editValue;
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            if (this.inArray(this.headerFtype, ['Date Time', 'Date', 'Time'])) {
                                this.showHideDatePicker(true);
                            } else
                            if (this.$refs.inline_input && this.$refs.inline_input.nodeName === 'SELECT') {
                                this.showHideDDLs(this.$root.selectParam);
                                this.ddl_cached = false;
                            } else {
                                this.$refs.inline_input.focus();
                            }
                        }
                    });
                } else {
                    this.editing = false;
                }
            },
            hideEdit() {
                this.editing = false;
            },
            hideDatePicker() {
                this.hideEdit();
                let value = $(this.$refs.inline_input).val();
                switch (this.headerFtype) {
                    case 'Date': value = moment( value ).format('YYYY-MM-DD'); break;
                    case 'Date Time': value = moment( value ).format('YYYY-MM-DD HH:mm:ss'); break;
                    case 'Time': value = moment( '0001-01-01 '+value ).format('HH:mm:ss'); break;
                }
                if (value === 'Invalid date') {
                    value = '';
                }
                this.editValue = value;
                this.updateValue();
            },
            updateCheckedDDL(item) {
                this.editValue = item;
                this.updateValue();
            },
            updateValue() {
                if (this.editValue !== this.oldValue) {
                    this.tableRow[this.tableHeader.field] = this.unitConvert(this.editValue, true);
                    this.tableRow._changed_field = this.tableHeader.field;
                    if (this.tableHeader.field == 'type') {
                        let def = _.first(this.aiModels());
                        if (def && def.val) {
                            this.tableRow.model = def.val;
                        }
                    }
                    this.$emit('updated-cell', this.tableRow);
                } else
                if (this.headerFtype === 'Boolean' || this.headerFtype === 'Radio') {
                    this.tableRow._changed_field = this.tableHeader.field;
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            updateRadio() {
                this.tableRow[this.tableHeader.field] = this.tableRow[this.tableHeader.field] ? 0 : 1;
                this.updateValue();
            },
            unitConvert(val, reverse) {
                reverse = !!reverse;
                let res = val;

                if (this.tableHeader.field === 'time' && val) {
                    if (reverse) {
                        res = SpecialFuncs.timeToUTC(val, this.tableRow.timezone || this.user.timezone, 'HH:mm:ss');
                    } else {
                        res = SpecialFuncs.timeToLocal(val, this.tableRow.timezone || this.user.timezone, 'HH:mm:ss');
                    }
                }

                return res;
            },
            showField() {
                let res = '';
                if (this.headerFtype === 'Password' && this.tableRow[this.tableHeader.field]) {
                    res = '*'.repeat( this.tableRow[this.tableHeader.field].length );
                }
                else
                if (this.tableHeader.field === 'user_cloud_id' && this.tableRow.user_cloud_id) {
                    let user_cloud = _.find(this.$root.settingsMeta.user_clouds_data, {id: Number(this.tableRow.user_cloud_id)});
                    res = user_cloud ? user_cloud.name : '';
                }
                else
                if (this.tableHeader.field === 'table_view_id' && this.tableRow.table_view_id) {
                    let tbview = _.find(this.tableViews(), {val: Number(this.tableRow.table_view_id)});
                    res = tbview ? tbview.show : this.tableRow.table_view_id;
                }
                else
                if (this.tableHeader.field === 'cloud' && this.tableRow.cloud) {
                    switch (this.tableRow.cloud) {
                        case 'Dropbox': res = '<img src="/assets/img/Dropbox_512.png" height="14"> Dropbox'; break;
                        case 'Google': res = '<img src="/assets/img/google-drive.png" height="14"> Google Drive'; break;
                        case 'OneDrive': res = '<img src="/assets/img/OneDrive_512.png" height="14"> OneDrive'; break;
                        case 'Jira': res = '<img src="/assets/img/jira-logo.png" height="14"> Jira'; break;
                        case 'Salesforce': res = '<img src="/assets/img/salesforce-with-type-logo.svg" height="14"> Salesforce'; break;
                    }
                }
                else
                if (this.tableHeader.field === 'mode' && this.tableRow.mode) {
                    switch (this.tableRow.mode) {
                        case 'sandbox': res = 'Sandbox'; break;
                        case 'live': res = 'Live'; break;
                    }
                }
                else
                if (this.tableHeader.field === 'type') {
                    let t = _.find(this.aiTypes(), {val: this.tableRow.type}) || {};
                    res = t.show || this.tableRow.type;
                }
                else
                if (this.tableHeader.field === 'model') {
                    let t = _.find(this.aiModels(), {val: this.tableRow.type}) || {};
                    res = t.show || this.tableRow.model;
                }
                else
                if (this.tableHeader.field === 'air_type') {
                    switch (this.tableRow.air_type) {
                        case 'public_rest': res = 'Public Rest'; break;
                    }
                }
                else
                if (this.tableHeader.field === 'root_folder') {
                    res = this.tableRow.root_folder || '$username (default)';
                }
                else {
                    res = this.editValue;
                }
                return this.$root.strip_danger_tags(res);
            },
            updateCheckBox() {
                this.tableRow[this.tableHeader.field] = !Boolean(this.tableRow[this.tableHeader.field]);
                this.$emit('updated-cell', this.tableRow, this.tableHeader);
                this.$forceUpdate();
            },
            inactivateCloudRow() {
                Swal({
                    title: 'Info',
                    text: "Disconnect Cloud?",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    showCancelButton: true,
                    closeOnConfirm: true,
                    animation: "slide-from-top"
                }).then(resp => {
                    if (resp.value) {
                        this.$root.sm_msg_type = 2;
                        axios.delete('/ajax/user-cloud/set-inactive', {
                            params: {
                                user_cloud_id: this.tableRow.id,
                            }
                        }).then(({ data }) => {
                            this.tableRow['msg_to_user'] = data['msg_to_user'];
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            this.$root.sm_msg_type = 0;
                        });
                    }
                });
            },
            linkClicked() {
                Cookies.set('last-url-cloud', location.href, { domain: this.$root.app_domain });
            },
            userClouds() {
                return _.map(this.$root.settingsMeta.user_clouds_data, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            tableViews() {
                let vvs = _.map(this.$root.tableMeta._views, (tw) => {
                    return { val: tw.id, show: tw.name, }
                });
                vvs.unshift({ val: null, show: '', });
                return vvs;
            },
            showCloudPopup() {
                let id = this.tableRow.user_cloud_id;
                let idx = _.findIndex(this.$root.settingsMeta.user_clouds_data, {id: id});
                eventBus.$emit('open-resource-popup', 'connections', idx, 'cloud');
            },
            showTbViewsPop() {
                eventBus.$emit('show-table-views-popup', this.$root.tableMeta.db_name);
            },
            cloudOpt() {
                return [
                    {val: 'Dropbox', show: 'Dropbox', img: location.origin+'/assets/img/Dropbox_512.png'},
                    {val: 'Google', show: 'Google Drive', img: location.origin+'/assets/img/google-drive.png'},
                    {val: 'OneDrive', show: 'OneDrive', img: location.origin+'/assets/img/OneDrive_512.png'},
                    {val: 'Jira', show: 'Jira', img: location.origin+'/assets/img/jira-logo.png'},
                    {val: 'Salesforce', show: 'Salesforce', img: location.origin+'/assets/img/salesforce-with-type-logo.svg'},
                ];
            },
        },
        mounted() {
            if (this.headerFtype === 'Timezone' && !this.tableRow[this.tableHeader.field]) {
                this.tableRow[this.tableHeader.field] = this.user.timezone || moment.tz.guess();
            }
            if (this.tableHeader.field === 'jira_email' && !this.tableRow[this.tableHeader.field]) {
                this.tableRow[this.tableHeader.field] = this.$root.user.email;
            }
            this.editValue = this.unitConvert(this.cellValue);
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomCell.scss";

    .inactivate-cloud {
        cursor: pointer;
        font-size: 18px;
        line-height: 12px;
        font-weight: bold;
    }
</style>