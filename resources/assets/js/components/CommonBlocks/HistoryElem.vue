<template>
    <div class="full-height">
        <div v-if="canAdd && historyHeader.show_history" class="flex mb5">
            <div class="full-width" style="border: 1px solid #ccc; border-radius: 5px;">
                <single-td-field
                    :table-meta="tableMeta"
                    :table-header="historyHeader"
                    :td-value="cellValue"
                    :with_edit="true"
                    class="full-frame"
                    @updated-td-val="updateHist"
                ></single-td-field>
            </div>
            <button class="btn btn-default blue-gradient"
                    :style="$root.themeButtonStyle"
                    @click="storeUpdate"
            >Update</button>
        </div>
        
        <div class="history-pane">
            <!-- Show updated value immediately, while it is syncing with server -->
            <template v-if="duplicator && current_history">
                <div class="history-head">
                    <history-user-info
                        :user="user"
                        :history="current_history"
                        :table-meta="tableMeta"
                    ></history-user-info>
                </div>
                <div class="history-body">
                    <span v-html="duplicator"></span>
                </div>
            </template>
    
            <template v-if="current_history">
                <div class="history-head">
                    <history-user-info
                        :user="user"
                        :history="current_history"
                        :table-meta="tableMeta"
                    ></history-user-info>
                </div>
                <div class="history-body">
                    <span v-html="displayHist(current_history)"></span>
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
                    <span v-html="displayHist(fld)"></span>
                    <span
                        v-if="canDel && (user.is_admin || user.id === fld.created_by)"
                        class="history-del"
                        @click="delHistory(fld)"
                    >×</span>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import {SpecialFuncs} from "../../classes/SpecialFuncs";
    import {Endpoints} from "../../classes/Endpoints";

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
                cellValue: null,
            };
        },
        props:{
            user: Object,
            tableMeta: Object,
            tableRow: Object,
            historyHeader: Object,
            redraw_history: Number,
            canAdd: Boolean,
            canDel: Boolean,
        },
        watch: {
            redraw_history(val) {
                setTimeout(() => {
                    this.loadHist();
                }, 500);
            },
        },
        methods: {
            loadHist() {
                this.loadOneHistory(this.tableMeta.id, this.historyHeader.id, this.tableRow.id);
            },
            displayHist(histRow) {
                let res = '';
                if (this.historyHeader.f_type === 'Date Time') {
                    res = this.$root.convertToLocal(histRow.value, this.user.timezone);
                }
                else if (this.historyHeader.f_type === 'User') {
                    res = histRow._u_value
                        ? this.$root.getUserSimple(histRow._u_value, this.tableMeta._owner_settings, 'history_user')
                        : histRow.value;
                } else {
                    res = histRow.value;
                }
                return this.$root.strip_tags(res);
            },
            //Add row
            updateHist(val, header, ddl_option) {
                this.cellValue = val;
                if (ddl_option) {
                    this.cellValue = ddl_option.show;
                }
            },
            storeUpdate() {
                if (!this.cellValue || this.cellValue == this.tableRow[this.historyHeader.field]) {
                    Swal('Info','Empty or the same value!');
                    return;
                }

                this.tableRow[this.historyHeader.field] = this.cellValue;
                this.tableRow._changed_field = this.historyHeader.field;

                let reqParam = this.$root.request_params
                    ? _.cloneDeep(this.$root.request_params)
                    : SpecialFuncs.tableMetaRequest(this.tableMeta.id);
                reqParam.special_params.for_list_view = true;

                this.duplicator = this.cellValue;
                this.cellValue = null;

                Endpoints.massUpdateRows(this.tableMeta, [this.tableRow], reqParam).then((data) => {
                    eventBus.$emit('list-view-update-row-sync', data);
                    this.loadHist();
                    this.histUpdated();
                }).catch(() => {
                    this.duplicator = null;
                });
            },
        },
        mounted() {
            this.loadHist();
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