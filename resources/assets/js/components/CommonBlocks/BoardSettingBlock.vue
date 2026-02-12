<template>
    <table class="tb-rows-padding" :class="{'full-width': no_header}">
        <tr v-if="!no_header">
            <td colspan="2">
                <label>Board display:</label>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="flex flex--center-v" style="white-space: nowrap;">
                    <label v-if="with_theme" :style="labelStyle">Max. Height:</label>
                    <span v-else class="m_l">Max. Height:</span>
                    <input class="form-control l-inl-control"
                           type="number"
                           @change="chang('board_view_height')"
                           v-model="board_settings[boardKey('board_view_height')]"
                           :style="textSysStyle">

                    <label v-if="with_theme" :style="labelStyle">px,&nbsp;&nbsp;&nbsp;Header Width:</label>
                    <span v-else class="m_l">px,&nbsp;&nbsp;&nbsp;Header Width:</span>
                    <input class="form-control l-inl-control"
                           type="number"
                           @change="chang('board_title_width')"
                           v-model="board_settings[boardKey('board_title_width')]"
                           :style="textSysStyle">

                    <template v-if="$root.inArray(board_settings[boardKey('board_display_position')], ['left','right'])">
                        <label v-if="with_theme" :style="labelStyle">&nbsp;&nbsp;&nbsp;Side Image Width:</label>
                        <span v-else>&nbsp;&nbsp;&nbsp;Side Image Width:</span>
                        <input class="form-control l-inl-control"
                               type="number"
                               @change="chang('board_image_width')"
                               v-model="board_settings[boardKey('board_image_width')]"
                               :style="textSysStyle">
                    </template>

                    <template v-if="$root.inArray(board_settings[boardKey('board_display_position')], ['top','bottom'])">
                        <label v-if="with_theme" :style="labelStyle">&nbsp;&nbsp;&nbsp;Top/Bottom Image Height:</label>
                        <span v-else>&nbsp;&nbsp;&nbsp;Top/Bottom Image Height:</span>
                        <input class="form-control l-inl-control"
                               type="number"
                               @change="chang('board_image_height')"
                               v-model="board_settings[boardKey('board_image_height')]"
                               :style="textSysStyle">
                        <label v-if="with_theme" :style="labelStyle">%</label>
                        <span v-else>%</span>
                    </template>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="flex flex--center-v" style="white-space: nowrap;">
                    <label v-if="with_theme" :style="labelStyle">Image:</label>
                    <span v-else class="m_l">Image:</span>
                    <select :style="textSysStyle"
                            class="form-control l-inl-control"
                            v-model="board_settings[boardKey('board_image_fld_id')]"
                            @change="chang('board_image_fld_id')"
                    >
                        <option v-if="noAttachments" disabled>No “Attachment” filed for images</option>
                        <option v-for="fld in tb_meta._fields" v-if="fld.f_type === 'Attachment'" :value="fld.id">{{ fld.name }}</option>
                    </select>

                    <select :style="textSysStyle"
                            class="form-control l-inl-control"
                            v-model="board_settings[boardKey('board_display_position')]"
                            @change="chang('board_display_position')"
                    >
                        <option :value="null"></option>
                        <option value="left">Left</option>
                        <option value="right">Right</option>
                        <option value="top">Top</option>
                        <option value="bottom">Bottom</option>
                    </select>

                    <label v-if="with_theme" :style="labelStyle">&nbsp;View:</label>
                    <span v-else>&nbsp;View:</span>
                    <select :style="textSysStyle"
                            class="form-control l-inl-control"
                            v-model="board_settings[boardKey('board_display_view')]"
                            @change="chang('board_display_view')"
                    >
                        <option value="scroll">Scroll</option>
                        <option value="slide">Slide</option>
                    </select>

                    <label v-if="with_theme" :style="labelStyle">&nbsp;Fit:</label>
                    <span v-else>&nbsp;Fit:</span>
                    <select :style="textSysStyle"
                            class="form-control l-inl-control"
                            v-model="board_settings[boardKey('board_display_fit')]"
                            @change="chang('board_display_fit')"
                    >
                        <option value="fill">Fill</option>
                        <option value="width">Width</option>
                        <option value="height">Height</option>
                    </select>
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
        computed: {
            labelStyle() {
                let style = _.clone(this.tb_meta.is_system ? this.textSysStyle : this.textStyle);
                if (style.fontFamily == 'initial') {
                    style.fontFamily = 'monospace';
                }
                style.color = '#555';
                return style;
            },
            noAttachments() {
                return ! _.find(this.tb_meta._fields, {f_type: 'Attachment'});
            },
        },
        props: {
            tb_meta: Object,
            board_settings: Object,
            no_header: Boolean,
            with_theme: Boolean,
            prefix: {
                type: String,
                default: '',
            },
        },
        methods: {
            boardKey(prop) {
                return this.prefix + prop;
            },
            chang(prop_name) {
                let val = this.board_settings[this.boardKey(prop_name)];
                if (this.$root.inArray(prop_name, ['board_image_width', 'board_title_width', 'board_image_height'])) {
                    if (prop_name === 'board_image_height') {
                        val = (val <= 1 ? val * 100 : val);
                        val = Math.min(val, 100);
                    }
                    val = Math.max(val, 0);
                    this.board_settings[this.boardKey(prop_name)] = val;
                }
                this.$emit('val-changed', this.boardKey(prop_name), val);
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