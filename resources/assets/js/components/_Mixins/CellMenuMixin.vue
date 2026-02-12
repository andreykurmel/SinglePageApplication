<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    /**
     *
     */
    export default {
        data: function () {
            return {
                row_menu: {
                    hdr: null,
                    row: null,
                    idx: -1,
                    can_del: false,
                },
                row_menu_show: false,
                row_menu_left: 0,
                row_menu_top: 0,
            }
        },
        computed: {
            rowMenuStyle() {
                return {
                    top: this.row_menu_top+'px',
                    left: this.row_menu_left+'px',
                };
            },
        },
        methods: {
            copyCell(row, hdr) {
                let str = SpecialFuncs.showhtml(hdr, row, row[hdr.field]);
                SpecialFuncs.strToClipboard(str);
            },
            showRowMenu(tableRow, index, tableHeader) {
                if (this.special_extras && this.special_extras.no_row_menu) {
                    return;
                }
                this.row_menu_show = true;
                this.row_menu_left = window.event.clientX;
                this.row_menu_top = window.event.clientY;
                this.row_menu.hdr = tableHeader || null;
                this.row_menu.row = tableRow;
                this.row_menu.idx = index;
                this.row_menu.can_del = this.canDeleteRow ? this.canDeleteRow(tableRow) : false;
            },
            clickHandler(e) {
                if (e.button == 0 && this.row_menu_show && this.$refs.row_menu && !$(this.$refs.row_menu).has(e.target).length) {
                    this.row_menu_show = false;
                }
            },
        },
    }
</script>