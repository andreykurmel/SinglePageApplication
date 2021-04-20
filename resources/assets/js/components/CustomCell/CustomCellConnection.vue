<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">

                    <label class="switch_t" v-if="tableHeader.f_type === 'Boolean'">
                        <input type="checkbox" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                        <span class="toggler round"></span>
                    </label>

                    <input
                            v-else-if="tableHeader.f_type === 'Radio' && !isAddRow"
                            :checked="tableRow[tableHeader.field]"
                            @click="updateRadio()"
                            type="radio"
                            ref="inline_input"
                            class="checkbox-input"/>

                    <moment-timezones
                            v-else-if="tableHeader.f_type === 'Timezone' && tableRow[tableHeader.field]"
                            :cur_tz="tableRow[tableHeader.field]"
                            :name="'Timezone'"
                            style="padding: 0;height: 16px;font-size: 13px;"
                            @changed-tz="updateCheckedDDL"
                    ></moment-timezones>

                    <div v-else-if="tableHeader.field === '_eml_pop' && !isAddRow" class="flex flex--center">
                        <button class="btn btn-default"
                                title="Email Settings"
                                :style="{padding: '0 5px', lineHeight: $root.themeTextFontSize+'px',}"
                                @click="emlSett()"
                        >Email</button>
                    </div>

                    <span v-else-if="tableHeader.field === 'key'">{{ tableRow.key ? String(tableRow.key).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'login'">{{ tableRow.login ? String(tableRow.login).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'app_pass'">{{ tableRow.app_pass ? String(tableRow.app_pass).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'secret_key'">{{ tableRow.secret_key ? String(tableRow.secret_key).replace(/./gi, '*') : '' }}</span>
                    <span v-else-if="tableHeader.field === 'public_key'">{{ tableRow.public_key ? String(tableRow.public_key).replace(/./gi, '*') : '' }}</span>

                    <div v-else="">
                        <span v-if="!isAddRow && tableHeader.field === 'msg_to_user'">
                            <a v-if="tableRow.msg_to_user" target="_blank" :href="this.tableRow.msg_to_user" @click="linkClicked()">Not Connect</a>
                            <template v-else="">
                                <span style="color: #0A0">Connected</span>
                                <span class="inactivate-cloud" @click="inactivateCloudRow()">&times;</span>
                            </template>
                        </span>
                        <span v-else="" v-html="showField()"></span>
                    </div>
                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <input
                    v-if="tableHeader.f_type === 'Password'"
                    type="password"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"
            />

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'cloud'"
                    :options="[
                        {val: 'Dropbox', show: 'Dropbox'},
                        {val: 'Google', show: 'Google'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
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
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'user_cloud_id'"
                    :options="userClouds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
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
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <input v-else-if="inArray(tableHeader.f_type, ['Date', 'Date Time', 'Time'])"
                   ref="inline_input"
                   @blur="hideDatePicker()"
                   class="form-control full-height"
                   :style="getEditStyle"/>

            <input
                    v-else-if="tableHeader.field === 'time'"
                    v-model="editValue"
                    v-mask="'##:##'"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"
                    placeholder="hh:mm"
            />

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
    import {eventBus} from './../../app';

    import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

    import {mask} from 'vue-the-mask';

    import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
    import MomentTimezones from "../MomentTimezones";

    export default {
        components: {
            MomentTimezones,
            TabldaSelectSimple,
        },
        name: "CustomCellConnection",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
        ],
        directives: {
            mask
        },
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        data: function () {
            return {
                editing: false,
                oldValue: this.tableRow[this.tableHeader.field],
                editValue: null,
            }
        },
        props:{
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            rowIndex: Number,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            user: Object,
            cellValue: String|Number,
        },
        watch: {
            cellValue(val) {
                this.editValue = this.unitConvert(val);
            }
        },
        computed: {
            getCustomCellStyle() {
                let obj = this.getCellStyle;
                obj.textAlign = (this.inArray(this.tableHeader.f_type, ['Boolean', 'Radio']) ? 'center' : '');
                return obj;
            },
            checkBoxOn() {
                return Boolean(this.editValue);
            },
            canCellEdit() {
                return this.tableHeader.f_type !== 'Radio'
                    && (this.tableHeader.f_type !== 'Timezone')
                    && (this.tableHeader.field !== 'cloud' || this.isAddRow)
                    && (this.tableHeader.field !== '_eml_pop')
                    && (this.tableHeader.field !== 'msg_to_user');
            }
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit() {
                if (!this.canCellEdit || this.inArray(this.tableHeader.f_type, ['Boolean'])) {
                    return;
                }
                //edit cell
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.editValue;
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            if (this.inArray(this.tableHeader.f_type, ['Date Time', 'Date', 'Time'])) {
                                this.showHideDatePicker();
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
                this.editValue = $(this.$refs.inline_input).val();
                this.hideEdit();
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
                    this.$emit('updated-cell', this.tableRow);
                } else
                if (this.tableHeader.f_type === 'Boolean' || this.tableHeader.f_type === 'Radio') {
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
                        res = this.$root.timeToUTC(val, this.tableRow.timezone || this.user.timezone);
                    } else {
                        res = this.$root.timeToLocal(val, this.tableRow.timezone || this.user.timezone);
                    }
                }

                return res;
            },
            showField() {
                let res = '';
                if (this.tableHeader.f_type === 'Password' && this.tableRow[this.tableHeader.field]) {
                    res = '*'.repeat( this.tableRow[this.tableHeader.field].length );
                }
                else
                if (this.tableHeader.field === 'user_cloud_id' && this.tableRow.user_cloud_id) {
                    let user_cloud = _.find(this.$root.settingsMeta.user_clouds_data, {id: Number(this.tableRow.user_cloud_id)});
                    res = user_cloud ? user_cloud.name : '';
                }
                else
                if (this.tableHeader.field === 'mode' && this.tableRow.mode) {
                    switch (this.tableRow.mode) {
                        case 'sandbox': res = 'Sandbox'; break;
                        case 'live': res = 'Live'; break;
                    }
                }
                else {
                    res = this.editValue;
                }
                return this.$root.strip_tags(res);
            },
            updateCheckBox() {
                this.tableRow[this.tableHeader.field] = !Boolean(this.tableRow[this.tableHeader.field]);
                this.$emit('updated-cell', this.tableRow, this.tableHeader);
                this.$forceUpdate();
            },
            inactivateCloudRow() {
                Swal({
                    title: "",
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
                            Swal('', getErrors(errors));
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
            emlSett() {
                eventBus.$emit('show-backup-settings-popup', this.tableRow.id);
            },
        },
        mounted() {
            if (this.tableHeader.f_type === 'Timezone' && !this.tableRow[this.tableHeader.field]) {
                this.tableRow[this.tableHeader.field] = this.user.timezone || moment.tz.guess();
            }
            this.editValue = this.unitConvert(this.cellValue);
        },
        beforeDestroy() {
        }
    }
</script>

<style scoped>
    @import "./CustomCell.scss";

    .inactivate-cloud {
        cursor: pointer;
        font-size: 18px;
        line-height: 12px;
        font-weight: bold;
    }
</style>