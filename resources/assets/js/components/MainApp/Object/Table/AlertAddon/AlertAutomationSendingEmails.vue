<template>
    <div class="full-height" :style="$root.themeMainBgStyle">
        <div class="flex flex--center" style="margin: 10px 0;" :style="textSysStyleSmart">
            <label>Select an email defined in the Email add-on to be automatically sent through triggers:</label>
            <select-block
                :options="getAnrEmails()"
                :sel_value="alert_sett.automation_email_addon_id"
                :is_disabled="!can_edit"
                style="width: 50%;"
                @option-select="(opt) => { updAlert('automation_email_addon_id', opt.val); }"
            ></select-block>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    export default {
        name: "AlertAutomationSendingEmails",
        components: {
            SelectBlock,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
            }
        },
        props:{
            can_edit: Boolean|Number,
            tableMeta: Object,
            alert_sett: Object,
        },
        methods: {
            updAlert(key, val) {
                this.alert_sett[key] = val;
                this.$emit('update-alert', this.alert_sett);
            },
            getAnrEmails() {
                let emls = _.map(this.tableMeta._email_addon_settings, (eas) => {
                    return { val:eas.id, show:eas.name };
                });
                emls.unshift({ val:null, show:'' });
                return emls;
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    label {
        margin: 0;
    }
</style>