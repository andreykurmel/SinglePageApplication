<script>
    /**
     *  should be present:
     *
     *  this.fixed_pos: Boolean
     *  */
    import {eventBus} from '../../app';

    export default {
        name: "MixinSmartPosition",
        components: {
        },
        mixins: [
        ],
        data: function () {
            return {
                smart_limit: 150,
                smart_horizontal: 150,
                smart_wrapper: 'select_wrapper',
                opened: false,
                fix_left: 0,
                fix_top: 0,
                fix_bottom: 0,
                fix_right: 0,
                fix_width: 0,
            }
        },
        methods: {
            fourDimsStyle() {
                let style = {};
                if (window.innerHeight - this.fix_bottom > this.smart_limit) {
                    style.top = '100%';//DDL below the cell
                    style.bottom = 'initial';
                } else {
                    style.top = 'initial';
                    style.bottom = '100%';//DDL above the cell
                }
                if (window.innerWidth - this.fix_right > this.smart_horizontal) {
                    style.left = '0';//DDL left from the cell
                    style.right = 'initial';
                } else {
                    style.left = 'initial';
                    style.right = '0';//DDL right from the cell
                }
                return style;
            },
            ItemsListStyle() {
                let style = {};

                if (this.fixed_pos) {
                    style.position = 'fixed';
                    style.left = this.fix_left+'px';
                    style.width = this.fix_width+'px';
                    if (window.innerHeight - this.fix_bottom > this.smart_limit) {
                        style.top = this.fix_bottom+'px';//DDL below the cell
                    } else {
                        style.top = 'initial';
                        style.bottom = (window.innerHeight - this.fix_top)+'px';//DDL above the cell
                    }
                } else {
                    if (window.innerHeight - this.fix_bottom > this.smart_limit) {
                        style.top = '100%';//DDL below the cell
                    } else {
                        style.top = 'initial';
                        style.bottom = '100%';//DDL above the cell
                    }
                }

                return style;
            },
            //Show Items List
            showItemsList() {
                this.fix_left = 0;
                this.fix_top = 0;
                this.fix_bottom = 0;
                this.fix_right = 0;
                this.fix_width = 0;

                this.$nextTick(() => {
                    let html = this.foreign_element || this.$refs[this.smart_wrapper];
                    if (html) {
                        let rect = html.getBoundingClientRect();

                        this.fix_left = rect.left;
                        this.fix_top = rect.top;
                        this.fix_bottom = rect.bottom;
                        this.fix_right = rect.right;
                        this.fix_width = rect.width;
                    }

                    this.opened = true;
                });
            },

            //Show/Hide Helper Popup
            hideTabldaSelect(e) {
                let container = $(this.$refs.select_wrapper);
                if (container.has(e.target).length === 0) {
                    this.hideSelect();
                }
            },
            hideSelect() {
                this.opened = false;
                this.$emit('hide-select');
            },
        },
        created() {
            setTimeout(() => {
                eventBus.$on('global-click', this.hideTabldaSelect);
            }, 100);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideTabldaSelect);
        }
    }
</script>