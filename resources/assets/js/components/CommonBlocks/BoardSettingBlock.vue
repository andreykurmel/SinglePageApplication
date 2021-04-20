<template>
    <table class="tb-rows-padding">
        <tr>
            <td colspan="2">
                <label>Board display (of linked records and for mobile display):</label>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="flex flex--center-v" style="white-space: nowrap;">
                    <span class="m_l">Height for single record:</span>
                    <input class="form-control l-inl-control"
                           type="number"
                           @change="(val) => { chang('board_view_height', val) }"
                           :value="board_view_height"
                           :style="textStyle">
                    <span>pixels,&nbsp;&nbsp;&nbsp;Image Width:</span>
                    <input class="form-control l-inl-control"
                           type="number"
                           @change="(val) => { chang('board_image_width', val) }"
                           :value="board_image_width"
                           :style="textStyle">
                    <span>%</span>
                </div>
            </td>
        </tr>
    </table>
</template>

<script>
    import CellStyleMixin from "./../_Mixins/CellStyleMixin.vue";

    export default {
        name: 'BoardSettingBlock',
        mixins: [
            CellStyleMixin,
        ],
        components: {
        },
        data() {
            return {
            }
        },
        props: {
            board_view_height: Number,
            board_image_width: Number,
        },
        methods: {
            chang(prop_name, eve) {
                let val = eve && eve.target ? to_float(eve.target.value) : null;
                if (val === null) {
                    return;
                }
                if (prop_name === 'board_image_width') {
                    val = (val <= 1 ? val*100 : val);
                    val = Math.min(val, 100);
                    val = Math.max(val, 0);
                }
                this.$emit('val-changed', prop_name, val);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import './../CommonBlocks/TabldaLike';

    label {
        margin: 0;
    }

    .tb-rows-padding {
        td {
            padding-bottom: 5px;
            white-space: normal;
        }
    }
</style>