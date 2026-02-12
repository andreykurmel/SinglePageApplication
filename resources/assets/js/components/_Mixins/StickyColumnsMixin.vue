<script>
/**
 *  should be present:
 *
 *  this.tableMeta: Object
 *  this.widths: Object
 *  this.listViewActions: Boolean
 *  this.hasFloatActions: Boolean
 *  */
import HeaderRowColSpanMixin from './HeaderRowColSpanMixin.vue';
import IsShowFieldMixin from './IsShowFieldMixin.vue';
import CellStyleMixin from './CellStyleMixin.vue';

export default {
        mixins: [
            HeaderRowColSpanMixin,
            IsShowFieldMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                zi: {
                    f_hdr: 200,
                    head: 150,
                    f_data: 100,
                },
                metaFieldsRights: {},
            }
        },
        computed: {
            showMetaFields() {
                let showed = _.filter(this.tableMeta._fields, (hdr) => {
                    return this.isShowFieldElem(hdr);
                });
                showed = _.orderBy(showed, ['is_floating'], ['desc']);
                this.fillMetaWidths(showed);
                this.fillHeaderRowColSpanCache(showed);
                return showed;
            },
            hasFloating() {
                return _.findIndex(this.showMetaFields, {is_floating: 1}) > -1;
            },
            lastFloating() {
                return _.findLastIndex(this.showMetaFields, {is_floating: 1});
            },
        },
        methods: {
            fillMetaWidths(fields) {
                let leftwidths = this.widths.index_col + this.widths.favorite_col;

                this.metaFieldsRights = {};
                _.each(fields, (fld, i) => {
                    this.metaFieldsRights[i] = this.$root.getFloat(fld.width)
                        + (this.metaFieldsRights[i-1] || 0)
                        + (i == 0 ? leftwidths : 0);
                });
            },
            sticky(style, h_idx, is_header) {
                let is_flo = this.showMetaFields[h_idx] ? this.showMetaFields[h_idx].is_floating : this.hasFloating;
                if (is_flo) {
                    return {
                        ...style,
                        ...this.stickyCell(h_idx, is_header)
                    };
                } else {
                    return style;
                }
            },
            stickyCell(h_idx, is_header) {
                return {
                    position: 'sticky',
                    zIndex: (is_header ? this.zi.f_hdr : this.zi.f_data),
                    left: this.calcLeft(h_idx)+'px',
                    borderRight: h_idx === this.lastFloating ? '1px solid #222' : null,
                };
            },
            calcLeft(h_idx) {
                let left = 0;
                left += (-2 < h_idx ? this.widths.index_col : 0);
                left += (-1 < h_idx && this.listViewActions ? this.widths.favorite_col : 0);
                _.each(this.showMetaFields, (fld, i) => {
                    if (i < h_idx) {
                        left += this.tdCellWidth(fld);
                    }
                });
                return left;
            },

            stickyRight(style, h_idx, is_header) {
                if (this.hasFloatActions) {
                    return {
                        ...style,
                        ...this.stickyCellRight(h_idx, is_header)
                    };
                } else {
                    return style;
                }
            },
            stickyCellRight(h_idx, is_header) {
                return {
                    position: 'sticky',
                    zIndex: (is_header ? this.zi.f_hdr : this.zi.f_data),
                    right: 0,//this.widths.action_col+'px',
                    borderLeft: '1px solid #222',
                };
            },
        },
    }
</script>