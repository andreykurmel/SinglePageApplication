<template>
    <div v-if="final_html" class="popup_wrap" ref="popup_ref" :style="hovPos" @mouseleave="$emit('tooltip-blur')">
        <div class="popup_html_link_preview" v-html="final_html"></div>
    </div>
</template>

<script>
    import {SpecialFuncs} from  './../../classes/SpecialFuncs';

    import {eventBus} from './../../app';

    import CanViewEditMixin from './../_Mixins/CanViewEditMixin.vue';

    export default {
        name: "LinkPreviewBlock",
        mixins: [
            CanViewEditMixin,
        ],
        components: {
        },
        data: function () {
            return {
                max_width_symbols: 0,
                max_height_symbols: 0,
                final_html: '',
                linkTableMeta: null,
            };
        },
        computed: {
            hovWidth() {
                return Math.min(this.max_width_symbols*7.5 + 10, 300);//10: padding left*2
            },
            hovPos() {
                let style = {};
                let c_off = to_float(this.c_offset);

                let x = (this.p_left || this.$root.lastMouseClick.clientX) - 5;
                style.left = (x + this.hovWidth) < window.innerWidth
                    ? x + c_off
                    : x - this.hovWidth - c_off;

                let y = (this.p_top || this.$root.lastMouseClick.clientY) - 5;
                style.top = (y + this.htmlHe) < window.innerHeight
                    ? y + c_off
                    : y - this.htmlHe - c_off;

                style.top += 'px';
                style.left += 'px';
                return style;
            },
            htmlHe() {
                return this.max_height_symbols * 22 + 4;//4: padding top*2
            },
        },
        props:{
            meta_table: Object,
            meta_header: Object,
            meta_row: Object,
            link_object: Object,
            p_top: Number,
            p_left: Number,
            c_offset: Number,
        },
        methods: {
            loadLink() {
                this.$root.sm_msg_type = 1;
                let mRow = _.cloneDeep(this.meta_row);
                mRow[this.meta_header.field] = this.link_object._c_value;

                let params = SpecialFuncs.tableMetaRequest(null, this.link_object.table_ref_condition_id);
                params.field = this.meta_header.field;
                params.link = this.link_object;
                params.table_row = mRow;
                params.page = 1;
                params.maxlimit = 10;

                axios.post('/ajax/table-data/field/get-rows', params).then(({ data }) => {
                    let first_rows = data.rows;
                    let total_rows = Number(data.rows_count);
                    this.linkTableMeta = data.table_right;

                    let html_arr = [];
                    this.final_html = '';
                    this.max_width_symbols = 0;
                    this.max_height_symbols = 0;
                    let availfields = SpecialFuncs.parseMsel(this.link_object.link_preview_fields);

                    if (first_rows.length && this.linkTableMeta && this.linkTableMeta._fields) {
                        _.each(first_rows, (row) => {
                            let str_arr = [];
                            _.each(this.linkTableMeta._fields, (fld) => {
                                if (availfields.indexOf(fld.id) > -1 && this.canViewHdr(fld)) {
                                    let titl = this.link_object.link_preview_show_flds ? this.$root.uniqName(fld.name)+': ' : '';
                                    str_arr.push(titl + row[fld.field]);
                                }
                            });
                            let valuestr = str_arr.length ? str_arr.join(' | ') : 'No Fields Available!';
                            html_arr.push('&#x1F784; ' + valuestr);//first - small black circle
                        });
                        if (total_rows > 10) {
                            html_arr.push('Total Rows: '+total_rows);
                        }
                    } else {
                        html_arr.push('No Records Found!');
                    }

                    this.final_html = '<div>'+html_arr.join('</div><div>')+'</div>';
                    _.each(html_arr, (str) => {
                        this.max_width_symbols = Math.max(String(str).length, this.max_width_symbols);
                    });
                    this.max_height_symbols = html_arr.length;
                    
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        created() {
            this.loadLink();
            eventBus.$on('global-click', () => {
                this.$emit('another-click');
            });
        },
    }
</script>

<style lang="scss" scoped>
    @keyframes popup_fadein {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }

    .popup_wrap {
        animation-name: popup_fadein;
        animation-duration: 1s;
        position: fixed;
        z-index: 5000;
        padding: 2px 6px;
        font-weight: bold;
        color: #FFF;
        background-color: #444;
        border: 1px solid #000;
        border-radius: 5px;
        max-width: 25%;
        transition: opacity 1s;
    }
</style>
<style lang="scss">
    .popup_html_link_preview {
        div {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
</style>