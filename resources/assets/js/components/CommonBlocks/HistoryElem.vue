<template>
    <div class="history-pane">

        <template v-if="current_history">
            <div class="history-head">
                <history-user-info
                        :user="user"
                        :history="current_history"
                        :table-meta="tableMeta"
                ></history-user-info>
            </div>
            <div class="history-body">
                <span v-html="$root.strip_tags(current_history.value)"></span>
            </div>
        </template>

        <template v-for="fld in field_history">
            <div class="history-head">
                <history-user-info
                        :user="user"
                        :history="fld"
                        :table-meta="tableMeta"
                ></history-user-info>
            </div>
            <div class="history-body">
                <span v-if="table_field.f_type === 'Date Time'">{{ $root.convertToLocal(fld.value, user.timezone) }}</span>
                <span v-else v-html="$root.strip_tags(fld.value)"></span>
                <span
                        v-if="user.is_admin || user.id === fld.created_by"
                        class="history-del"
                        @click="delHistory(fld)"
                >×</span>
            </div>
        </template>
    </div>
</template>

<script>
    import HistoryMixin from "./../_Mixins/HistoryMixin";

    import HistoryUserInfo from "./HistoryUserInfo";

    export default {
        components: {
            HistoryUserInfo
        },
        mixins: [
            HistoryMixin
        ],
        name: "HistoryElem",
        data: function () {
            return {
            };
        },
        props:{
            user: Object,
            tableMeta: Object,
            row_id: Number,
            table_field: Object,
            redraw_history: Number,
        },
        watch: {
            redraw_history(val) {
                setTimeout(() => {
                    this.loadOneHistory(this.tableMeta.id, this.table_field.id, this.row_id);
                }, 500);
            },
        },
        methods: {
        },
        mounted() {
            this.loadOneHistory(this.tableMeta.id, this.table_field.id, this.row_id);
        }
    }
</script>

<style lang="scss" scoped>
    .history-pane {
        flex-basis: 300px;
        margin-left: 5px;

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