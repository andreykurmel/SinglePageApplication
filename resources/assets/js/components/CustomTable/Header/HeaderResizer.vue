<template>
    <div class="head-resizer"
         @mousedown="startResizeHeader()"
    ></div>
</template>

<script>
    export default {
        name: "HeaderResizer",
        data: function () {
            return {
                resize: {
                    startPos: null,
                    delMoveListener: null,
                    delEndListener: null
                },
            }
        },
        props: {
            tableHeader: Object, //required {width: number}
            init_width: Number|String,
            user: Object,
        },
        methods: {
            //change width of the headers
            startResizeHeader() {
                this.$emit('start-resize');
                this.resize.startPos = window.event.clientX - Number(this.init_width || this.tableHeader.width);
                this.resize.delMoveListener = this.onDocument('mousemove', this.continueResizeHeader);
                this.resize.delEndListener = this.onDocument('mouseup', this.endResizeHeader);
            },
            continueResizeHeader() {
                if (this.tableHeader) {
                    this.tableHeader.width = Math.abs(window.event.clientX - this.resize.startPos);

                    if (this.tableHeader.width <= this.tableHeader.min_width) {
                        this.tableHeader.width = this.tableHeader.min_width + 1;
                    }

                    if (this.tableHeader.width >= this.tableHeader.max_width) {
                        this.tableHeader.width = this.tableHeader.max_width - 1;
                    }
                }
            },
            endResizeHeader() {
                this.resize.delMoveListener();
                this.resize.delEndListener();
                this.saveResized(this.tableHeader);
            },
            onDocument(event, func) {
                document.addEventListener(event, func);
                return function() {
                    document.removeEventListener(event, func);
                };
            },
            //save changing width of the headers
            saveResized(tableHeader) {
                if (this.user.id) {
                    axios.put('/ajax/settings/data', {
                        table_field_id: tableHeader.id,
                        field: 'width',
                        val: tableHeader.width,
                    }).then(({ data }) => {
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    });
                }
                this.$emit('resize-finished');
            },
        },
    }
</script>

<style scoped>
    .head-resizer {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 5px;
        cursor: col-resize;
    }
</style>