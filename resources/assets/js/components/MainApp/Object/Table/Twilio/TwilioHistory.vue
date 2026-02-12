<template>
    <div class="full-height preview-wrap">
        <template v-if="incorrect_settings">
            <div class="form-group label_blocks">
                <label>Server/email settings are incorrect!</label>
                <label v-if="!twilioSettings.acc_twilio_key_id">
                    <span>Empty Twilio account.</span>
                </label>
                <label v-if="!twilioSettings.recipient_field_id && !twilioSettings.recipient_phones">
                    <span>Empty recipients.</span>
                </label>
                <label v-if="!twilioSettings.sms_body">
                    <span>Empty message body.</span>
                </label>
                <label>Or rows are not found!</label>
            </div>
        </template>
        <div v-else-if="all_rows && all_rows.length" class="flex full-frame">
            <div :style="{width: hideFIlter ? '0' : '30%'}">
                <filters-block
                    :table-meta="filter_meta"
                    :input_filters="filter_filters"
                    :no_right_click="true"
                    style="background: white;"
                ></filters-block>
            </div>
            <div class="menu-body" :style="{width: hideFIlter ? '100%' : '70%'}">
                <div class="full-height body-view">
                    <div style="height: 32px; padding: 0 5px;" class="flex flex--center-v flex--space">
                        <span class="glyphicon"
                              :class="[ !hideFIlter ? 'glyphicon-triangle-left': 'glyphicon-triangle-right']"
                              @click="hideFIlter = !hideFIlter"
                        ></span>

                        <button v-if="hasHistory(selected_id)"
                                class="btn btn-primary btn-sm blue-gradient pull-right"
                                :style="$root.themeButtonStyle"
                                @click="clearHistory()"
                        >Clear History</button>
                    </div>
                    <div v-for="prev in preview_messages">
                        <twilio-preview-element
                            v-if="prev.history && prev.history.length"
                            :element="prev"
                            :table-meta="tableMeta"
                            :twilio-settings="twilioSettings"
                            :short-view="false"
                            :no-main="true"
                            :with-filters="filter_filters"
                            @history-delete="clearHistory"
                            class="form-group"
                        ></twilio-preview-element>
                    </div>
                </div>
            </div>
        </div>
        <template v-else="">
            <div class="form-group">
                <label>Loading ...</label>
            </div>
        </template>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import TwilioPreviewElement from "./TwilioPreviewElement";
    import FiltersBlock from "../../../../CommonBlocks/FiltersBlock";

    export default {
        name: "TwilioHistory",
        mixins: [
        ],
        components: {
            FiltersBlock,
            TwilioPreviewElement,
        },
        data: function () {
            return {
                hideFIlter: false,
                selected_id: null,

                incorrect_settings: false,
                preview_messages: {},
                all_rows: null,

                filter_meta: {
                    _is_owner: true,
                    _fields: [
                        { field:'preview_from', name:'From', is_showed:1 },
                        { field:'preview_to', name:'To', is_showed:1 },
                        { field:'send_date', name:'Sent', is_showed:1 },
                    ],
                },
                filter_filters: [
                    {
                        applied_index: 0,
                        filter_type: 'value',
                        field: 'preview_from',
                        name: 'From',
                        values: [],
                    },
                    {
                        applied_index: 0,
                        filter_type: 'value',
                        field: 'preview_to',
                        name: 'To',
                        values: [],
                    },
                    {
                        applied_index: 0,
                        filter_type: 'value',
                        field: 'send_date',
                        name: 'Sent',
                        values: [],
                    },
                ],
            }
        },
        props:{
            tableMeta: Object,
            twilioSettings: Object,
            total_messages: Number,
            can_edit: Boolean|Number,
        },
        computed: {
            selPreview() {
                return this.preview_messages[this.selected_id];
            },
        },
        methods: {
            hasHistory(selected_id) {
                let find = _.find(this.preview_messages, (prev) => {
                    if (selected_id) {
                        return prev.history && prev.history.length && _.find(prev.history, {row_id: selected_id});
                    } else {
                        return prev.history && prev.history.length;
                    }
                });
                return !!find;
            },
            getPreview(special) {
                if (
                    !this.twilioSettings.acc_twilio_key_id
                    || (!this.twilioSettings.recipient_field_id && !this.twilioSettings.recipient_phones)
                    || !this.twilioSettings.sms_body
                ) {
                    this.incorrect_settings = true;
                    return;
                }

                this.incorrect_settings = false;
                axios.post('/ajax/addon-twilio-sett/preview', {
                    twilio_add_id: this.twilioSettings.id,
                    row_id: null,
                    special: special || '',
                }).then(({data}) => {
                    if (data && data.all_rows && data.all_rows.length) {
                        if (!this.all_rows) {
                            this.all_rows = data.all_rows;
                            let row = _.first(this.all_rows);
                            this.selected_id = row ? row.id : null;
                        }
                        this.$root.assignObject(data.previews, this.preview_messages);
                        this.buildFilters();
                    } else {
                        this.incorrect_settings = true;
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            buildFilters() {
                _.each(this.filter_filters, (filter) => {
                    let vals = [];
                    _.each(this.preview_messages, (prev) => {
                        _.each(prev.history, (hist) => {
                            let subs = typeof hist[filter.field] === 'object' ? hist[filter.field] : [hist[filter.field]];
                            vals = _.concat(vals, subs);
                        });
                    });
                    vals = _.uniq(vals);
                    filter.values = _.map(vals, (vl) => {
                        return {
                            checked: 1,
                            show: vl,
                            val: vl,
                        };
                    });
                });
            },
            clearHistory(history_id) {
                if (!this.can_edit) {
                    return;
                }
                axios.delete('/ajax/addon-twilio-sett/history', {
                    params: {
                        twilio_add_id: this.twilioSettings.id,
                        history_id: history_id,
                    },
                }).then(({data}) => {
                    this.getPreview();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
        },
        mounted() {
            this.getPreview('initial');
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettings";

    .preview-wrap {
        label {
            margin: 0;
        }
        .form-group {
            border: 1px solid #ccd0d2;
            border-radius: 4px;
            padding: 5px;
            font-size: 14px;
        }
        .label_blocks {
            label {
                display: block;
            }
        }
        .glyphicon {
            cursor: pointer;
        }

        .menu-body {
            padding: 0;
            margin-left: 5px;

            .body-view {
                position: relative;
                overflow: auto;
                background: #FFF;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
        }
    }
</style>