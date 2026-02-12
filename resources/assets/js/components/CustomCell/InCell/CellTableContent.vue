<template>
    <div ref="content_elem" :style="tdContent">

        <template v-if="Array.isArray(editValue)">
            <span v-for="(str, idx) in editValue">
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
                        :is_select="tableHeader.input_type !== 'Mirror'"
                        :is-vert-table="isVertTable"
                        :can_edit="can_edit"
                        :style="{display: inline ? 'inline' : null}"
                        @open-app-as-popup="openAppAsPopup"
                        @show-src-record="showSrcRecord"
                        @full-size-image="imgFromEmit"
                        @unselect-val="unselectVal(str)"
                ></cell-table-content-data>
                <span v-if="idx + 1 < editValue.length">&nbsp;</span>
            </span>
            <cell-table-content-data
                v-if="! editValue.length"
                :global-meta="globalMeta"
                :table-meta="tableMeta"
                :table-row="tableRow"
                :table-header="tableHeader"
                :html-value="showField('', tableHeader)"
                :real-value="''"
                :user="user"
                :behavior="behavior"
                :is_def_fields="is_def_fields"
                :is_td_single="is_td_single"
                :no_height_limit="no_height_limit"
                :is_select="tableHeader.input_type !== 'Mirror'"
                :is-vert-table="isVertTable"
                :can_edit="can_edit"
                :style="{display: inline ? 'inline' : null}"
                @open-app-as-popup="openAppAsPopup"
                @show-src-record="showSrcRecord"
                @full-size-image="imgFromEmit"
            ></cell-table-content-data>
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
                    :can_edit="can_edit"
                    :style="{display: inline ? 'inline' : null}"
                    @open-app-as-popup="openAppAsPopup"
                    @show-src-record="showSrcRecord"
                    @full-size-image="imgFromEmit"
                    @unselect-val="unselectVal(null)"
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
                avail_behave_links: ['list_view','favorite','link_popup','bi_module','map_view','single-record-view',
                    'kanban_view','request_view','grouping_table'],
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
            can_edit: Boolean|Number,
            behavior: String,
            is_def_fields: Boolean,
            is_td_single: Object,
            no_height_limit: Boolean,
        },
        watch: {
        },
        computed: {
            tdContent() {
                let maxFront = this.maxLinkNameBy('front');
                let maxEnd = this.maxLinkNameBy('end');

                let st = {
                    position: 'relative',
                    backgroundColor: this.tableHeader.f_type === 'Color' ? this.editValue : null,
                    paddingLeft: maxFront + 'px',
                    paddingRight: maxEnd + 'px',
                    paddingBottom: '1px',
                    paddingTop: '1px',
                };
                if (this.inline) {
                    st.display = 'inline';
                }
                return st;
            },
            fldTypeSelect() {
                return $.inArray(this.tableHeader.input_type, this.$root.ddlInputTypes) > -1;
            },
            availLinks() {
                if (
                    $.inArray(this.behavior, this.avail_behave_links) > -1
                    && (this.tableRow.id || this.$root.is_dcr_page)
                    && !this.is_td_single
                ) {
                    return _.filter(this.tableHeader._links, (lnk) => {
                        return lnk.icon !== 'Underlined'
                            && (lnk.link_pos === 'front' || lnk.link_pos === 'end');
                    });
                }
                return [];
            },
        },
        methods: {
            maxLinkNameBy(prop) {
                let allNamesLen = 0;
                _.each(this.availLinks, (lnk) => {
                    if (lnk.link_pos === prop && lnk.icon.length) {
                        allNamesLen += Number(lnk.icon.length) * Number(this.$root.themeTextFontSize) * 0.5 + 6;
                    }
                });
                if (allNamesLen) {
                    allNamesLen += 2;
                }
                return allNamesLen;
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            showField(ediVal, tableHeader) {
                let res = ediVal;

                //for field types == User
                if (this.inArray(tableHeader.f_type, ['User'])) {
                    res = this.$root.getUserOneStr(ediVal, this.tableRow, this.tableHeader, this.tableMeta._owner_settings);
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
        },
        mounted() {
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