<template>
    <div class="email-phone-wrapper" @mousedown.stop="" @mouseup.stop="" @click.stop="openEdit">
        <template v-if="items.length">
            <span v-for="(item,idx) in items">
                <span style="white-space: nowrap">
                    <template v-for="lnk in table_header._links" v-if="canLink(lnk, 'before') || canLink(lnk, 'front')">
                        <link-object :table-meta="table_meta"
                                     :global-meta="table_meta"
                                     :table-header="table_header"
                                     :table-row="table_row"
                                     :cell-value="show_items[item] || item"
                                     :link="lnk"
                                     :user="$root.user"
                                     :class="[canLink(lnk, 'front') ? 'link-absolute link-left' : '']"
                                     @show-src-record="showSrcRecord"
                                     @open-app-as-popup="openAppAsPopup"
                        ></link-object>
                    </template>

                    <i v-if="type==='Email' && (table_header.twilio_google_acc_id || table_header.twilio_sendgrid_acc_id)"
                       class="fas fa-envelope green"
                       @click.stop="twilioSendPop('Email', item)"
                    ></i>
                    <i v-if="type==='Phone Number' && table_header.twilio_voice_acc_id"
                       class="fas fa-phone green"
                       @click.stop="twilioSendPop('Phone Call', item)"
                    ></i>
                    <i v-if="type==='Phone Number' && table_header.twilio_sms_acc_id"
                       class="fas fa-sms green"
                       @click.stop="twilioSendPop('Message', item)"
                    ></i>
                    <span v-html="show_items[item] || item"></span>
                    <i v-if="can_edit" class="glyphicon glyphicon-remove gray hover-red" @click.stop="removItem(idx)"></i>
                    <span>&nbsp;</span>

                    <template v-for="lnk in table_header._links" v-if="canLink(lnk, 'after') || canLink(lnk, 'end')">
                        <link-object :table-meta="table_meta"
                                     :global-meta="table_meta"
                                     :table-header="table_header"
                                     :table-row="table_row"
                                     :cell-value="show_items[item] || item"
                                     :link="lnk"
                                     :user="$root.user"
                                     :class="[canLink(lnk, 'end') ? 'link-absolute link-right' : '']"
                                     @show-src-record="showSrcRecord"
                                     @open-app-as-popup="openAppAsPopup"
                        ></link-object>
                    </template>
                </span>
                <br v-if="table_header.f_format === 'one_per_row'">
            </span>
        </template>
        <div v-else>&nbsp;</div>
    </div>
</template>

<script>
    import {eventBus} from '../../../app';

    import LinkObject from "./LinkObject.vue";

    export default {
        name: "EmailPhoneElement",
        components: {
            LinkObject,
        },
        data() {
            return {
                type: '',
                items: [],
                show_items: {},
                uniqid: uuidv4(),
            }
        },
        props: {
            can_edit: Boolean|Number,
            table_meta: Object,
            table_header: Object,
            table_row: Object,
            cell_val: String|Number,
        },
        computed: {
        },
        watch: {
            cell_val: {
                handler(val) {
                    this.getItems(val);
                },
                immediate: true,
            },
        },
        methods: {
            canLink(lnk, needed_pos) {
                return this.table_row.id
                    && lnk.icon !== 'Underlined'
                    && lnk.link_pos === needed_pos;
            },
            showSrcRecord(link, header, tableRow) {
                this.$emit('show-src-record', link, header, tableRow);
            },
            openAppAsPopup(tb_app, app_link) {
                this.$emit('open-app-as-popup', tb_app, app_link);
            },

            removItem(idx) {
                this.items.splice(idx, 1);
                this.sendUpdate();
            },
            getItems(val) {
                this.type = this.table_header.f_type;
                if (this.type === 'Phone Number') {
                    this.items = String(val || '')
                        .replace(/[^+0-9;]/gi, '')
                        .split(';')
                        .filter((v) => {
                            return !!v;
                        });
                    this.show_items = {};
                    _.each(this.items, (v) => {
                        this.show_items[v] = this.$root.telFormat(v);
                    });
                }
                if (this.type === 'Email') {
                    this.items = String(val || '')
                        .replace(/\s/gi, '')
                        .split(';')
                        .filter((val) => {
                            return String(val).match(/\S+@\S+\.\S+/gi);
                        });
                    this.show_items = {};
                }
            },
            twilioSendPop(type, item) {
                eventBus.$emit('show-twilio-send-popup', type, item, this.table_header, this.table_row);
            },
            openEdit() {
                if (!this.can_edit) {
                    return;
                }

                let obj = {
                    type: this.type,
                    header: this.table_header,
                    row: this.table_row,
                    items: this.items,
                    uniq_id: this.uniqid
                };
                eventBus.$emit('cell-email-phone-popup__show', obj);
            },
            eventUpdateItems(uuid, items) {
                if (uuid === this.uniqid) {
                    this.items = items;
                    this.sendUpdate();
                }
            },
            sendUpdate() {
                this.$emit('element-update', this.items.join('; '));
            },
        },
        mounted() {
            eventBus.$on('cell-email-phone-popup__update', this.eventUpdateItems);
        },
        beforeDestroy() {
            eventBus.$off('cell-email-phone-popup__update', this.eventUpdateItems);
        }
    }
</script>

<style lang="scss" scoped>
    .email-phone-wrapper {
        cursor: context-menu;
    }
    i {
        cursor: pointer;
    }
    .link-absolute {
        position: absolute;
        //z-index: 10; - creates "web link popup overflow issue"
        background: white;
        top: 1px;
    }
    .link-left {
        left: 0;
    }
    .link-right {
        right: 0;
    }
</style>