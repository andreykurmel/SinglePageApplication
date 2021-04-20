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
                <span v-if="history_field_type === 'Date Time'">{{ $root.convertToLocal(fld.value, user.timezone) }}</span>
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
    import HistoryUserInfo from "./HistoryUserInfo";

    export default {
        components: {
            HistoryUserInfo
        },
        name: "HistoryElem",
        data: function () {
            return {
                field_history: [],
                current_history: null,
                history_field_type: '',
            };
        },
        props:{
            user: Object,
            tableMeta: Object,
            row_id: Number,
            table_field: Object,
        },
        methods: {
            delHistory(hist) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/history', {
                    params: {
                        history_id: hist.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.field_history, {id: hist.id});
                    if (idx > -1) {
                        this.field_history.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
            axios.get('/ajax/history', {
                params: {
                    table_id: this.tableMeta.id,
                    table_field_id: this.table_field.id,
                    row_id: this.row_id
                }
            }).then(({ data }) => {
                this.history_field_type = this.table_field.f_type;
                this.field_history = data.history;
                this.current_history = data.current;
            }).catch(errors => {
                Swal('', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
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