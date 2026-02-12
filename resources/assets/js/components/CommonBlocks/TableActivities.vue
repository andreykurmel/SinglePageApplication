<template>
    <div class="history-pane">
        <div :style="{ height: 'calc(100% - '+add_msg_height+'px)', overflow: 'auto' }">
            <div v-for="hist in histories">

                <div class="history-head">
                    <history-user-info
                        :user="user"
                        :history="hist"
                        :table-meta="tableMeta"
                        :is-activity="true"
                    ></history-user-info>
                </div>

                <div v-if="hist._to_user" class="history-body">
                    <span v-html="$root.strip_tags(hist.comment)"></span>
                </div>
                <div v-else-if="hist._table_field" class="history-body">
                    <span v-if="hist._table_field.f_type === 'Date Time'">{{ $root.convertToLocal(hist.value, user.timezone) }}</span>
                    <span v-else v-html="$root.strip_tags(hist.value)"></span>
                    <span
                        v-if="user.is_admin || user.id === hist.created_by"
                        class="history-del"
                        @click="delHistory(hist)"
                    >×</span>
                </div>

            </div>

            <div v-if="total_histories > histories.length">
                <label style="cursor: pointer;" @click="loadMore">More {{ total_histories - histories.length }} activities...</label>
            </div>
        </div>

        <send-message-block
            :add-msg-height="add_msg_height"
            :owner="user.id == tableMeta.user_id"
            :owner_id="user.id"
            :table_id="tableMeta.id"
            :with_group="false"
            @send-message="sendMessage"
        ></send-message-block>
    </div>
</template>

<script>
    import HistoryMixin from "./../_Mixins/HistoryMixin";

    import HistoryUserInfo from "./HistoryUserInfo";
    import SendMessageBlock from "./SendMessageBlock";

    export default {
        components: {
            SendMessageBlock,
            HistoryUserInfo
        },
        mixins: [
            HistoryMixin
        ],
        name: "TableActivities",
        data: function () {
            return {
                page: 1,
                add_msg_height: this.user.id == this.tableMeta.user_id ? 110 : 80,
            };
        },
        props: {
            tableMeta: Object,
            tableRow: Object,
            user: Object,
            redraw_history: Number,
        },
        watch: {
            redraw_history(val) {
                setTimeout(() => {
                    this.page = 1;
                    this.loadAllHistories(this.tableMeta.id, this.tableRow.id);
                }, 500);
            },
        },
        methods: {
            loadMore() {
                this.page++;
                this.loadAllHistories(this.tableMeta.id, this.tableRow.id, this.page);
            },
            sendMessage(message, to_user_id) {
                $.LoadingOverlay('show');
                axios.post('/ajax/history/comment', {
                    table_id: this.tableMeta.id,
                    row_id: this.tableRow.id,
                    from_user_id: this.user.id,
                    to_user_id: to_user_id,
                    comment: message
                }).then(({ data }) => {
                    this.page = 1;
                    this.loadAllHistories(this.tableMeta.id, this.tableRow.id);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
            this.page = 1;
            this.loadAllHistories(this.tableMeta.id, this.tableRow.id);
        }
    }
</script>

<style lang="scss" scoped>
    .history-pane {
        .history-del {
            float: right;
            font-size: 2em;
            line-height: 0.7em;
            cursor: pointer;
        }
        .history-head, .history-body {
            padding: 5px 8px;
        }
        .history-head {
            background-color: #E2F0D9;
        }
        .history-body {
            border-bottom: 1px solid #ccc;
        }
    }
</style>