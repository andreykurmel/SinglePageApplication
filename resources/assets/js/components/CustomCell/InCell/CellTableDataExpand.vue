<template>
    <span class="more_elem"
          @mousedown.stop=""
          @mouseup.stop=""
          @click.stop="openPopup()">
        <i class="glyphicon glyphicon-resize-full"></i>
    </span>
</template>

<script>
    import {eventBus} from '../../../app';

    import {SpecialFuncs} from "../../../classes/SpecialFuncs";

    export default {
        name: "CellTableDataExpand",
        data: function () {
            return {
            }
        },
        props: {
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            uniqid: String,
            canEdit: Boolean,
        },
        methods: {
            //popup for long Strings functions
            openPopup() {
                let obj = {
                    top: window.event.clientY,
                    left: window.event.clientX,
                    header: this.tableHeader,
                    meta: this.tableMeta,
                    row: this.tableRow,
                    html: SpecialFuncs.showFullHtml(this.tableHeader, this.tableRow, this.tableMeta),
                    can_edit: this.canEdit,
                    uniq_id: this.uniqid
                };
                eventBus.$emit('table-data-string-popup__show', obj);
            },
        },
    }
</script>

<style lang="scss" scoped>
    .more_elem {
        position: absolute;
        bottom: 0;
        right: 0;
        padding: 0 3px;
        background-color: inherit;
        cursor: pointer;
    }
</style>