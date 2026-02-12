<template>
    <table class="spaced-table" v-if="tableMeta && tbBackup">
        <tbody v-if="requestFields">

        <tr>
            <td :style="getTdStyle">
                <div class="td td--100 h-32" :style="getTdStyle">
                    <div class="flex flex--center-v full-height">
                        <label :style="{width: label_wi}">{{ $root.uniqName(requestFields['bkp_email_field_id'].name) }}:&nbsp;</label>
                        <input
                            type="text"
                            v-model="tbBackup['bkp_email_field_static']"
                            :disabled="!with_edit"
                            @change="updateBackup('bkp_email_field_static')"
                            class="form-control"
                            placeholder="Enter email addresses separated with comma, space or semicolon."/>
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td :style="getTdStyle">
                <div class="td td--100" :style="getTdStyle">
                    <div class="flex full-height">
                        <label :style="{width: label_wi}">{{ $root.uniqName(requestFields['bkp_email_subject'].name) }}:&nbsp;</label>
                        <div class="full-width full-height" style="position: relative;">
                            <textarea
                                    rows="2"
                                    v-model="tbBackup['bkp_email_subject']"
                                    :disabled="!with_edit"
                                    class="form-control"
                                    @change="updateBackup('bkp_email_subject')"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td :style="getTdStyle">
                <div class="td td--100 h-32" :style="getTdStyle">
                    <div class="flex flex--center-v full-height">
                        <label :style="{width: label_wi}">{{ $root.uniqName(requestFields['bkp_addressee_txt'].name) }}:&nbsp;</label>
                        <div class="full-width full-height" style="position: relative;">
                            <input type="text"
                                   v-model="tbBackup['bkp_addressee_txt']"
                                   :disabled="!with_edit"
                                   @change="updateBackup('bkp_addressee_txt')"
                                   class="form-control"
                                   placeholder="Hello, there (default)."/>
                        </div>
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td :style="getTdStyle">
                <div class="td td--100" :style="getTdStyle">
                    <div class="flex full-height">
                        <label :style="{width: label_wi}">{{ $root.uniqName(requestFields['bkp_email_message'].name) }}:&nbsp;</label>
                        <textarea
                                rows="6"
                                v-model="tbBackup['bkp_email_message']"
                                :disabled="!with_edit"
                                class="form-control"
                                @change="updateBackup('bkp_email_message')"
                        ></textarea>
                    </div>
                </div>
            </td>
        </tr>

        </tbody>
    </table>
</template>

<script>
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin.vue";

    export default {
        name: "BackupAddSettings",
        mixins: [
            CellStyleMixin,
        ],
        components: {
        },
        data: function () {
            return {
                label_wi: '85px',
                i_avail_fields: [
                    'bkp_email_field_id',
                    'bkp_email_field_static',
                    'bkp_email_subject',
                    'bkp_addressee_field_id',
                    'bkp_addressee_txt',
                    'bkp_email_message',
                ],
                requestFields: null,
            }
        },
        props:{
            tableMeta: Object,
            tbBackup: Object,
        },
        computed: {
            getTdStyle() {
                return {
                    height: this.tdCellHGT+'px',
                };
            },
            with_edit() {
                return !!this.tableMeta._is_owner;
            },
        },
        watch: {
            'tableMeta.id': function(val) {
                this.setAvailFields();
            }
        },
        methods: {
            inArray(type, array) {
                return array.indexOf(type) > -1;
            },
            updateBackup(fld) {
                this.tbBackup._changed_field = fld;
                this.$emit('updated-row', this.tbBackup);
            },
            setAvailFields() {
                this.requestFields = null;
                this.$nextTick(() => {

                    this.requestFields = {};
                    _.each(this.i_avail_fields, (fld_key) => {
                        this.requestFields[fld_key] = _.find(this.$root.settingsMeta.table_backups._fields, {field: fld_key}) || {};
                    });

                });
            },
        },
        mounted() {
            this.setAvailFields();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./SettingsModule/ReqRowStyle";

    .spaced-table {
        td {
            label {
                white-space: normal;
                flex-shrink: 0;
            }
        }
    }
</style>