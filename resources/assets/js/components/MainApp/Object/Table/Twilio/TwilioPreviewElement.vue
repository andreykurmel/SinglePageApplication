
<template>
    <div>
        <div v-if="!noMain" class="tw_block">

            <div class="tw_header" :style="{backgroundColor: twilioSettings.preview_background_header}">
                <div class="flex flex--center-v">
                    <label>From:</label>
                    <span>{{ element.preview_from }}</span>
                    <label>&nbsp;&nbsp;&nbsp;To:</label>
                    <span v-if="element.preview_to.join(', ')">{{ element.preview_to.join(', ') }}</span>
                    <span v-else class="red">Incorrect recipient! Try to use phone with country code.</span>
                </div>

                <div v-if="shortView && lastHistory && lastHistory.preview_body === element.preview_body" class="sent_at">
                    <label>Sent at: {{ $root.convertToLocal(lastHistory.send_date, $root.user.timezone) }}</label>
                </div>
            </div>

            <div :style="{backgroundColor: twilioSettings.preview_background_body}">
                <div v-html="element.preview_body"></div>
            </div>

        </div>

        <template v-if="!shortView">
            <div v-for="hist in element.history" class="tw_block">
                <template v-if="canDraw(hist)">

                    <div class="tw_header" :style="{backgroundColor: twilioSettings.preview_background_header}">
                        <div class="flex flex--center-v">
                            <label>From:</label>
                            <span>{{ hist.preview_from }}</span>
                            <label>&nbsp;&nbsp;&nbsp;To:</label>
                            <span>{{ hist.preview_to.join(', ') }}</span>
                        </div>

                        <div class="sent_at">
                            <label>Sent at: {{ $root.convertToLocal(hist.send_date, $root.user.timezone) }}</label>
                            <span class="glyphicon glyphicon-remove gray hover-red"
                                  style="cursor: pointer;"
                                  title="Remove history"
                                  @click="$emit('history-delete', hist.id)"
                            ></span>
                        </div>
                    </div>

                    <div :style="{backgroundColor: twilioSettings.preview_background_body}">
                        <div v-html="hist.preview_body"></div>
                    </div>

                </template>
            </div>
        </template>
    </div>
</template>

<script>
    export default {
        name: "TwilioPreviewElement",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
            }
        },
        props:{
            element: Object,
            tableMeta: Object,
            twilioSettings: Object,
            shortView: Boolean,
            noMain: Boolean,
            withFilters: Array,
        },
        computed: {
            emlAttachField() {
                return _.find(this.tableMeta._fields, {id: Number(this.twilioSettings.field_id_attachments)});
            },
            lastHistory() {
                return _.first(this.element.history || []);
            },
        },
        methods: {
            canDraw(hist) {
                if (!this.withFilters) {
                    return true;
                }
                let found = true;
                _.each(this.withFilters, (filter) => {
                    found = found && _.findIndex(filter.values, (vl) => {
                        let arr = typeof hist[filter.field] === 'object' ? hist[filter.field] : [hist[filter.field]];
                        return vl.checked && arr.indexOf(vl.val) > -1;
                    }) > -1;
                });
                return !!found;
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
    .tw_block {
        background-color: #F4f4f4;
        margin-bottom: 30px;

        .tw_header {
            position: relative;
            background-color: #DDD;
        }
        .tw_attach_field {
            position: relative;
        }
        .sent_at {
            position: absolute;
            bottom: 0;
            right: 5px;
        }
    }
</style>