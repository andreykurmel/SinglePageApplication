<template>
    <div v-if="tbHeader" :style="{width: vert_widths ? vert_widths.width+'px' : ''}">
        <template v-for="elem in editVals">
            <template v-for="lnk in tbHeader._links" v-if="canLinks && tbHeader.active_links">
                <link-object :table-meta="tableMeta"
                             :global-meta="tableMeta"
                             :table-header="tbHeader"
                             :table-row="{}"
                             :cell-value="getElem(elem)"
                             :link="lnk"
                             :user="$root.user"
                             @show-src-record="showSrcRecord"
                ></link-object>
            </template>
            <span :class="{'is_select': $root.issel(tbHeader.input_type), 'm_sel__wrap': $root.isMSEL(tbHeader.input_type)}">{{ getElem(elem) }}</span>
        </template>
        <header-resizer v-if="vert_widths"
                        :table-header="vert_widths"
                        :init_width="vert_widths.width"
                        :user="{}"
                        @start-resize="$emit('start-resize')"
                        @resize-finished="$emit('resize-finished')"
        ></header-resizer>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    import LinkObject from "../../../../CustomCell/InCell/LinkObject";
    import HeaderResizer from "../../../../CustomTable/Header/HeaderResizer";

    export default {
        components: {
            HeaderResizer,
            LinkObject,
        },
        name: "BiPivotHeader",
        data: function () {
            return {
            }
        },
        props:{
            tableMeta: Object,
            editVals: Array,
            corrDb: String,
            canLinks: Boolean,
            vert_widths: Object,
        },
        computed: {
            tbHeader() {
                return _.find(this.tableMeta._fields, {field: this.corrDb});
            },
        },
        methods: {
            getElem(elem) {
                switch (elem) {
                    case '__sub_total':
                        return 'Sub Total';
                    default:
                        return SpecialFuncs.showhtml(this.tbHeader, {}, elem);
                }
            },
            //show link
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'bi_module');
            },
        },
    }
</script>

<style lang="scss" scoped>
</style>