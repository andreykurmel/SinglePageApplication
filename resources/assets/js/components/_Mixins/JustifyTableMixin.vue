<script>
/**
 *  should be present:
 *
 *  ref: tbWrap
 *
 *  this.tableMeta: Object
 *  this.widths: Object
 *  this.behavior: String
 *  */
import StickyColumnsMixin from './StickyColumnsMixin.vue';

export default {
        mixins: [
            StickyColumnsMixin,
        ],
        data: function () {
            return {
                r_margin: this.$root.inArray(this.behavior, ['list_view','favorite']) ? 50 : 0,
                b_margin: this.$root.inArray(this.behavior, ['list_view','favorite']) ? 50 : 0,
            }
        },
        computed: {
            primaryAlign() {
                let align = this.external_align || (this.tableMeta ? this.tableMeta.primary_align : 'start');
                return align === 'start' ? align : 'safe '+align;
            },
        },
        methods: {
            mountJustifier() {
                this.r_margin = in_array(this.behavior, ['list_view','link_popup','request_view','favorite']) ? this.r_margin : 0;

                if (this.$root.isIphone) {
                    this.b_margin = in_array(this.behavior, ['list_view','link_popup','favorite','dcr_linked_tb']) ? this.b_margin : 0;
                } else {
                    this.b_margin = in_array(this.behavior, ['list_view','link_popup','favorite']) ? this.b_margin : 0;
                }
            },
        },
    }
</script>