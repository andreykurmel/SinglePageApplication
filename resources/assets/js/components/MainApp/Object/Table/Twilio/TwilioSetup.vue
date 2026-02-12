<template xmlns="http://www.w3.org/1999/html">
    <div class="full-height setup_wrapper flex flex--col" :style="textSysStyle">

        <div class="divider"></div>

        <div class="flex flex--center-v">
            <label :style="$root.themeMainTxtColor">Recipients (phone numbers. Use comma, semi-colon or space to separate multiple addresses):</label>
        </div>
        <div class="form-group flex flex--center-v">
            <label :style="$root.themeMainTxtColor" style="min-width: 45px">&nbsp;&nbsp;&nbsp;To:&nbsp;</label>
            <select class="form-control"
                    @change="emitUpd()"
                    v-model="twilioSettings.recipient_field_id"
                    :style="textSysStyle"
                    :disabled="!can_edit"
            >
                <option :value="null"></option>
                <option v-for="fld in tableMeta._fields"
                        v-if="$root.inArray(fld.f_type, ['String','Text','Long Text'])"
                        :value="fld.id"
                >{{ fld.name }}</option>
            </select>
            <label :style="$root.themeMainTxtColor">&nbsp;&nbsp;&nbsp;and&nbsp;</label>
            <input class="form-control"
                   @change="emitUpd()"
                   v-model="twilioSettings.recipient_phones"
                   :disabled="!can_edit"
                   :style="textSysStyle"/>
        </div>

        <div class="form-group flex flex--center" style="width: 80%">
            <label :style="$root.themeMainTxtColor">Generate messages for records (row) group:&nbsp;</label>
            <select class="form-control"
                    @change="emitUpd('limit_row_group_id')"
                    v-model="twilioSettings.limit_row_group_id"
                    :disabled="!can_edit"
                    :style="textSysStyle">
                <option :value="null"></option>
                <option v-for="rowgr in tableMeta._row_groups" :value="rowgr.id">{{ rowgr.name }}</option>
            </select>
            <label :style="$root.themeMainTxtColor">&nbsp;Total {{ total_messages }} records.</label>
        </div>

        <div class="form-group flex flex--center-v">
            <label :style="$root.themeMainTxtColor">Preview background, header:&nbsp;</label>
            <div class="clr_wrap">
                <tablda-colopicker
                    :init_color="twilioSettings.preview_background_header"
                    :avail_null="true"
                    :can_edit="can_edit"
                    @set-color="(clr) => {twilioSettings.preview_background_header = clr; emitUpd('preview_background_header')}"
                ></tablda-colopicker>
            </div>

            <label :style="$root.themeMainTxtColor">&nbsp;&nbsp;&nbsp;&nbsp;body:&nbsp;</label>
            <div class="clr_wrap">
                <tablda-colopicker
                    :init_color="twilioSettings.preview_background_body"
                    :avail_null="true"
                    :can_edit="can_edit"
                    @set-color="(clr) => {twilioSettings.preview_background_body = clr; emitUpd('preview_background_body')}"
                ></tablda-colopicker>
            </div>
        </div>

        <div class="form-group">
            <label :style="$root.themeMainTxtColor">Message:</label>
            <div style="position: relative;">
                <textarea rows="3"
                       :style="textSysStyle"
                       :disabled="!can_edit"
                       v-model="twilioSettings.sms_body"
                       @keyup="remadeFormula('formula_sms')"
                       @focus="formula_sms = true"
                       class="form-control"
                ></textarea>
                <formula-helper
                    v-if="formula_sms"
                    :user="$root.user"
                    :table-meta="tableMeta"
                    :table-row="twilioSettings"
                    :header-key="'sms_body'"
                    :can-edit="true"
                    :no-function="true"
                    :no_prevent="true"
                    :pop_width="'100%'"
                    style="padding: 0;"
                    @close-formula="formula_sms = false"
                    @set-formula="emitUpd('sms_body')"
                ></formula-helper>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import CellStyleMixin from "./../../../../_Mixins/CellStyleMixin.vue";

    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker";

    export default {
        name: "TwilioSetup",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            TabldaColopicker,
        },
        data: function () {
            return {
                formula_sms: false,
            }
        },
        props:{
            tableMeta: Object,
            twilioSettings: Object,
            total_messages: Number,
            can_edit: Boolean|Number,
        },
        computed: {
        },
        methods: {
            emitUpd(type) {
                if (!this.can_edit) {
                    return;
                }
                this.formula_sms = false;
                this.$emit('save-backend', this.twilioSettings, type);
            },
            remadeFormula(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .setup_wrapper {
        font-size: 1.1em;
        white-space: nowrap;

        select {
            font-size: 1.1em !important;
        }
        label {
            margin: 0;
        }

        .l-inl-control {
            display: inline-block;
            margin: 0 5px;
        }
        .top_elem {
        }
        .rest_elem {
            border: 1px solid #CCC;
            padding: 10px;
            border-radius: 10px;
        }

        .divider {
            border-top: 3px solid #666;
            margin: 15px 0;
        }

        .clr_wrap {
            height: 36px;
            width: 72px;
            position: relative;
            border: 1px solid #CCC;
            border-radius: 5px;
        }
    }
</style>