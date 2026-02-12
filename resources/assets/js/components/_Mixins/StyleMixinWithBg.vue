<script>
/**
 *  should be present:
 *
 *  this.bg_color: String
 *  */
import CellStyleMixin from './CellStyleMixin.vue';

import {SpecialFuncs} from "../../classes/SpecialFuncs";

export default {
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
            }
        },
        computed: {
            bgColor() {
                let stl = this.$root.themeMainBgStyle;
                if (this.bg_color) {
                    stl = {backgroundColor: this.bg_color};
                }
                return stl;
            },
            textColor() {
                let stl = this.textSysStyleSmart;
                if (this.bg_color) {
                    stl = {
                        color: this.bg_color == 'white' ? '#656565' : SpecialFuncs.smartTextColorOnBg(this.bg_color),
                    };
                }
                return stl;
            },
        },
        methods: {
        },
    }
</script>