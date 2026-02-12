<template>
    <div class="tabs-vertical" style="padding: 5px;">
        <div class="tabs-vertical--header">
            <div class="tabs-vertical--buttons">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'tw_new_call'}"
                        @click="acttab = 'tw_new_call';$emit('setWidth',600)"
                >New</button>
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'tw_sent_call'}"
                        @click="acttab = 'tw_sent_call';$emit('setWidth',750)"
                >History</button>
            </div>

            <div v-if="tableHeader.twilio_voice_acc_id && twAcc" style="position: absolute; right: 10px; top: 10px; font-size: 16px;">
                Twilio/Voice: <a @click="showResPop('twilio_tab')">{{ twAcc.name || '#'+(twAccIdx+1) }}</a>
            </div>
        </div>
        <div class="tabs-vertical--body" :style="$root.themeMainBgStyle">

            <call-ui
                v-show="acttab === 'tw_new_call'"
                :can_show_call="differentPhones(cellItem)"
                :from_active="from_active"
                :to_active="to_active"
                :timer_value="timer_value"
                :timer-ui="timerUi"
                :tw-acc="twAcc"
                :to-name="tableHeader.name"
                :to-phone="cellItem"
                :w_theme="true"
                @browser-call="browserCall(cellItem)"
                @hang-up="hangUp()"
            ></call-ui>

            <div class="full-frame" v-show="acttab === 'tw_sent_call'">
                <custom-table
                    :cell_component_name="'custom-cell-twilio'"
                    :global-meta="callMeta"
                    :table-meta="callMeta"
                    :settings-meta="$root.settingsMeta"
                    :all-rows="tw_histories"
                    :rows-count="tw_histories.length"
                    :cell-height="1"
                    :user="$root.user"
                    :behavior="'tw_history'"
                    :is-full-width="true"
                    @delete-row="histDelete"
                ></custom-table>
            </div>

        </div>
    </div>
</template>

<script>
    import CallMixin from "./CallMixin";

    import CallUi from "./CallUi";
    import CustomTable from "../../../../../CustomTable/CustomTable";

    export default {
        name: "TwilioPopupCall",
        mixins: [
            CallMixin,
        ],
        components: {
            CustomTable,
            CallUi,
        },
        data: function () {
            return {
                acttab: 'tw_new_call',
                mixin_type: 'call',
            }
        },
        props: {
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            cellItem: String,
        },
        computed: {
            twAcc() {
                return _.find(this.$root.user._twilio_api_keys, {id: Number(this.tableHeader.twilio_voice_acc_id)});
            },
            twAccIdx() {
                return _.findIndex(this.$root.user._twilio_api_keys, {id: Number(this.tableHeader.twilio_voice_acc_id)});
            },
        },
        methods: {
        },
        mounted() {
            this.twilio_acc_id = this.tableHeader.twilio_voice_acc_id;
        },
        beforeDestroy() {
        }
    }
</script>

<style scoped lang="scss">
.fsize {
    font-size: 72px;
    margin: 36px;
}
.w-33 {
    width: 33%;
}
.number_top, .number_bot {
    font-size: 18px;
    font-weight: bold;
}
.number_top {
    position: absolute;
    top: 20%;
}
.number_bot {
    position: absolute;
    bottom: 12%;
}
</style>