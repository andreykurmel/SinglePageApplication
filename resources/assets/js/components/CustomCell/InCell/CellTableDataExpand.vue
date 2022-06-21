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

    export default {
        name: "CellTableDataExpand",
        data: function () {
            return {
            }
        },
        props:{
            cont_height: Number,
            cont_width: Number,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            html: String,
            uniqid: String,
            canEdit: Boolean,
        },
        methods: {
            //popup for long Strings functions
            openPopup() {
                let obj = {
                    height: this.cont_height,
                    width: this.cont_width,
                    top: window.event.clientY,
                    left: window.event.clientX,
                    header: this.tableHeader,
                    meta: this.tableMeta,
                    row: this.tableRow,
                    html: this.html,
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