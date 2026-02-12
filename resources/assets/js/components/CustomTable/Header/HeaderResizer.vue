<template>
    <div class="head-resizer"
         @mousedown.stop.prevent="startResizeHeader"
         @dblclick.stop.prevent="resizeToContent"
         @click.stop.prevent=""
         @drag.stop.prevent=""
         @dragstart.stop.prevent=""
         @dragstop.stop.prevent=""
         :style="getStl()"
    >
        <!-- Used for auto-resize width, temporal, is not visible for users -->
        <single-td-field
            v-if="table_meta && resizeHeader && resizeContentString && resizeContentRow"
            :table-meta="table_meta"
            :table-header="resizeHeader"
            :td-value="resizeContentString"
            :ext-row="resizeContentRow"
            :with_edit="true"
            :no_width="true"
            :draw_links="true"
            :style="{position: 'fixed', zIndex: -1, left: '100%', whiteSpace: 'nowrap'}"
            ref="resize_content"
        ></single-td-field>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../classes/SpecialFuncs";

    export default {
        name: "HeaderResizer",
        data: function () {
            return {
                resize: {
                    startPos: null,
                    startWidth: null,
                    startHeight: null,
                    delMoveListener: null,
                    delEndListener: null,
                },
                resizeContentString: '',
                resizeContentRow: null,
                resizeHeader: null,
            }
        },
        props: {
            tableHeader: Object, //required {width: number}
            hdr_key: {
                type: String,
                default: "width",
            },
            init_width: Number|String,
            user: Object,
            reversed: Boolean,
            vertical: Boolean,
            all_rows: Object|Array,
            table_meta: Object,
            resizeOnly: Boolean,
            step: Number,
        },
        methods: {
            getStl() {
                if (this.vertical) {
                    return {
                        top: this.reversed ? 0 : 'auto',
                        bottom: this.reversed ? 'auto' : 0,
                        height: '5px',
                        cursor: 'row-resize',
                    };
                } else {
                    return {
                        left: this.reversed ? 0 : 'auto',
                        right: this.reversed ? 'auto' : 0,
                        width: '5px',
                        cursor: 'col-resize',
                    };
                }
            },
            hasId() {
                return ! this.resizeOnly && this.tableHeader.id;
            },
            //change width of the headers
            resizeToContent() {
                if (this.all_rows && this.table_meta && this.hasId()) {
                    //Remove listeners if present
                    if (this.resize.delMoveListener) {
                        this.resize.delMoveListener();
                    }
                    if (this.resize.delEndListener) {
                        this.resize.delEndListener();
                    }

                    //Initialize
                    let headerName = _.last(_.split(this.tableHeader.name, ','));
                    let availableForHeader = [
                        'Boolean', 'Rating', 'Progress Bar', 'Vote', 'Connected Clouds', 'Table', 'Table Field', 'Email', 'Phone Number', 'Color', 'Attachment', 'User'
                    ].indexOf(this.tableHeader.f_type) === -1;//Not in excluded

                    this.resizeContentString = [''];
                    this.resizeContentRow = null;
                    this.resizeHeader = _.cloneDeep(this.tableHeader);

                    //Find max value in rows
                    if (this.tableHeader.f_type !== 'Boolean') {
                        _.each(this.all_rows, (row) => {
                            if (String(row[this.tableHeader.field]).length > this.resizeContentString[0].length) {
                                this.resizeContentString = [String(row[this.tableHeader.field])];
                                this.resizeContentRow = row;
                            } else if (availableForHeader && String(row[this.tableHeader.field]).length == this.resizeContentString[0].length) {
                                this.resizeContentString.push(String(row[this.tableHeader.field]));
                            }
                        });
                    } else {
                        this.resizeHeader.f_type = 'String';
                    }

                    //Add header name if possible
                    if (! this.resizeContentRow || ! this.resizeContentString) {
                        this.resizeContentString = [ headerName ];
                        this.resizeContentRow = this.tableHeader;
                    } else
                    if (availableForHeader) {
                        this.resizeContentString.push(headerName);
                    }

                    this.resizeContentString = _.join(this.resizeContentString, '<br>');

                    //Set width after the redrawing
                    window.setTimeout(() => {
                        let width = this.$refs.resize_content && this.$refs.resize_content.$el
                            ? parseInt(this.$refs.resize_content.$el.getBoundingClientRect().width)
                            : 0;

                        if (width) {
                            if (this.tableHeader.f_type === 'Boolean' && width < 45) {
                                width = 45;
                            }

                            this.tableHeader[this.hdr_key] = parseInt(width) + 8;
                            this.checkMinMax();
                            window.setTimeout(() => {
                                this.saveResized(this.tableHeader);
                            }, 1000);
                        }

                        this.resizeContentString = '';
                        this.resizeContentRow = null;
                        this.resizeHeader = null;
                    }, 100);
                }
            },
            startResizeHeader() {
                this.$emit('start-resize');
                this.resize.startPos = this.vertical ? window.event.clientY : window.event.clientX;
                this.resize.startWidth = Number(this.init_width || this.tableHeader[this.hdr_key]);
                this.resize.delMoveListener = this.onDocument('mousemove', this.continueResizeHeader);
                this.resize.delEndListener = this.onDocument('mouseup', this.endResizeHeader);
            },
            continueResizeHeader() {
                if (this.tableHeader) {
                    let client = this.vertical ? window.event.clientY : window.event.clientX;
                    this.tableHeader[this.hdr_key] = (this.reversed ? -1 : 1) * (client - this.resize.startPos);
                    this.tableHeader[this.hdr_key] += this.resize.startWidth;
                    if (this.step) {
                        let extra = this.tableHeader[this.hdr_key] % this.step;
                        this.tableHeader[this.hdr_key] -= (this.reversed ? -1 : 1) * extra;
                    }
                    this.checkMinMax();
                }
            },
            checkMinMax() {
                if (this.tableHeader.min_width && this.tableHeader[this.hdr_key] <= this.tableHeader.min_width) {
                    this.tableHeader[this.hdr_key] = this.tableHeader.min_width + 1;
                }

                if (this.tableHeader.max_width && this.tableHeader[this.hdr_key] >= this.tableHeader.max_width) {
                    this.tableHeader[this.hdr_key] = this.tableHeader.max_width - 1;
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
                if (this.user && this.user.id && tableHeader.grouping_id && tableHeader.indent) {
                    axios.put('/ajax/addon-grouping/field', {
                        table_grouping_field_id: tableHeader.id,
                        fields: { indent: tableHeader.indent }
                    }).then(({ data }) => {
                        this.$emit('col-resized');
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
                else if (this.user && this.user.id && hasId()) {
                    axios.put('/ajax/settings/data', {
                        table_field_id: tableHeader.id,
                        field: 'width',
                        val: tableHeader.width,
                    }).then(({ data }) => {
                        this.$emit('col-resized');
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
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
        left: 0;
    }
</style>