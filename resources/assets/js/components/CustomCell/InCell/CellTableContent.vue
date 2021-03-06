<template>
    <div ref="content_elem" :style="tdContent">
        <template v-if="Array.isArray(editValue)">
            <span v-for="str in editValue">
                <cell-table-content-data
                        :global-meta="globalMeta"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
                        :table-header="tableHeader"
                        :html-value="showField(str, tableHeader)"
                        :real-value="str"
                        :user="user"
                        :behavior="behavior"
                        :is_def_fields="is_def_fields"
                        :is_td_single="is_td_single"
                        :no_height_limit="no_height_limit"
                        :is_select="true"
                        :is-vert-table="isVertTable"
                        :style="{display: inline ? 'inline' : null}"
                        @open-app-as-popup="openAppAsPopup"
                        @show-src-record="showSrcRecord"
                        @full-size-image="imgFromEmit"
                        @unselect-val="unselectVal(str)"
                ></cell-table-content-data>
            </span>
        </template>
        <template v-else="">
            <cell-table-content-data
                    :global-meta="globalMeta"
                    :table-meta="tableMeta"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :html-value="showField(editValue, tableHeader)"
                    :real-value="editValue"
                    :user="user"
                    :behavior="behavior"
                    :is_def_fields="is_def_fields"
                    :is_td_single="is_td_single"
                    :no_height_limit="no_height_limit"
                    :is_select="fldTypeSelect"
                    :is-vert-table="isVertTable"
                    :style="{display: inline ? 'inline' : null}"
                    @open-app-as-popup="openAppAsPopup"
                    @show-src-record="showSrcRecord"
                    @full-size-image="imgFromEmit"
            ></cell-table-content-data>
        </template>
    </div>
</template>

<script>
import CellTableContentData from "./CellTableContentData.vue";

export default {
        name: "CellTableContent",
        mixins: [
        ],
        components: {
            CellTableContentData
        },
        data: function () {
            return {
                cont_height: 0,
                cont_width: 0,
                bg_color: null,
            }
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            editValue: String|Number,
            maxCellRows: Number,
            curWidth: String|Number,
            fontSize: String|Number,
            user: Object,
            isVertTable: Boolean,
            inline: Boolean,
            can_edit: Boolean,
            behavior: String,
            is_def_fields: Boolean,
            is_td_single: Boolean,
            no_height_limit: Boolean,
        },
        watch: {
            maxCellRows(val) {
                this.$nextTick(() => {
                    this.recalcContent();
                });
            },
            editValue(val) {
                this.$nextTick(() => {
                    this.recalcContent();
                });
            },
            curWidth(val) {
                this.$nextTick(() => {
                    this.recalcContent();
                });
            },
            fontSize(val) {
                this.$nextTick(() => {
                    this.recalcContent();
                });
            }
        },
        computed: {
            tdContent() {
                let st = {
                    position: 'relative',
                    backgroundColor: this.tableHeader.f_type === 'Color' ? this.editValue : null,
                };
                if (this.inline) {
                    st.display = 'inline';
                }
                return st;
            },
            fldTypeSelect() {
                return $.inArray(this.tableHeader.input_type, this.$root.ddlInputTypes) > -1;
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            showField(ediVal, tableHeader) {
                let res = ediVal;

                //for field types == User
                if (this.inArray(tableHeader.f_type, ['User'])) {
                    let usr = this.$root.smallUserStr(this.tableRow, this.tableHeader, ediVal, true);
                    res = this.$root.getUserSimple(usr, this.tableMeta._owner_settings);
                    if (!res) {
                        res = ediVal && (!isNaN(ediVal) || ediVal[0] == '_')
                            ? 'loading...'
                            : ediVal;
                    }
                }
                else
                //for field types == Color
                if (this.inArray(tableHeader.f_type, ['Color'])) {
                    res = '';
                }
                return res;
            },

            //show link popup
            showSrcRecord(link, header, tableRow) {
                this.$emit('show-src-record', link, header, tableRow);
            },
            openAppAsPopup(tb_app, app_link) {
                this.$emit('open-app-as-popup', tb_app, app_link);
            },
            unselectVal(m_select_part) {
                this.$emit('unselect-val', m_select_part);
            },
            imgFromEmit(images, idx) {
                this.$emit('full-size-image', images, idx);
            },

            //content sizes
            recalcContent() {
                if (this.tableHeader.f_type !== 'User') {
                    this.cont_height = Math.floor($(this.$refs.content_elem).height());
                    this.cont_width = Math.floor($(this.$refs.content_elem).width());
                    this.$emit('changed-cont-size', this.cont_height, this.cont_width, this.showField(this.editValue, this.tableHeader.f_type));
                }
            },
        },
        mounted() {
            this.recalcContent();
        }
    }
</script>

<style lang="scss" scoped>
    div {
        text-decoration: inherit;
    }
    span {
        text-decoration: inherit;
        display: inline-block;
    }
    /*.content_elem {
        overflow-wrap: break-word;
    }*/
    .btn-deletable {
        position: absolute;
        top: 10%;
        right: 3px;
        bottom: 10%;
        padding: 0 3px;

        span {
            font-size: 1.4em;
            line-height: 0.7em;
            display: inline-block;
        }
    }
</style>