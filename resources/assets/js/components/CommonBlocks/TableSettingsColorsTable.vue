<template>
    <table class="theme-table">
        <tr>
            <td class="td-header" rowspan="7">
                <span class="components-span">Components</span>
            </td>
            <td class="td-header txt-right">Top Panel Background</td>
            <td>
                <div :style="{width: min_wi+'px'}"></div>
                <tablda-colopicker
                        :init_color="tb_theme.navbar_bg_color"
                        :saved_colors="$root.color_palette"
                        :fixed_pos="true"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'navbar_bg_color')}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Table Header Background</td>
            <td>
                <div :style="{width: min_wi+'px'}"></div>
                <tablda-colopicker
                        :init_color="tb_theme.table_hdr_bg_color"
                        :saved_colors="$root.color_palette"
                        :fixed_pos="true"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'table_hdr_bg_color')}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Buttons</td>
            <td>
                <div :style="{width: min_wi+'px'}"></div>
                <tablda-colopicker
                        :init_color="tb_theme.button_bg_color"
                        :saved_colors="$root.color_palette"
                        :fixed_pos="true"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'button_bg_color')}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Ribbon</td>
            <td>
                <div :style="{width: min_wi+'px'}"></div>
                <tablda-colopicker
                        :init_color="tb_theme.ribbon_bg_color"
                        :saved_colors="$root.color_palette"
                        :fixed_pos="true"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'ribbon_bg_color')}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Main Background</td>
            <td>
                <div :style="{width: min_wi+'px'}"></div>
                <tablda-colopicker
                        :init_color="tb_theme.main_bg_color"
                        :saved_colors="$root.color_palette"
                        :fixed_pos="true"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'main_bg_color')}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Text Font Size</td>
            <td>
                <select class="form-control full-frame"
                        style="padding: 0;"
                        v-model="tb_theme.app_font_size"
                        @change="propChanged()"
                >
                    <option></option>
                    <option>10</option>
                    <option>12</option>
                    <option>14</option>
                    <option>16</option>
                    <option>20</option>
                </select>
            </td>
        </tr>

        <tr>
            <td class="td-header txt-right">Text Font Color</td>
            <td>
                <div :style="{width: min_wi+'px'}"></div>
                <tablda-colopicker
                        :init_color="tb_theme.app_font_color"
                        :saved_colors="$root.color_palette"
                        :fixed_pos="true"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'app_font_color')}"
                ></tablda-colopicker>
            </td>
        </tr>
    </table>
</template>

<script>
    import TabldaColopicker from "./../CustomCell/InCell/TabldaColopicker.vue";

    export default {
        name: 'TableSettingsColorsTable',
        components: {
            TabldaColopicker
        },
        data() {
            return {
                re_init: false,
                min_wi: 70,
            }
        },
        props: {
            tb_theme: Object,
        },
        methods: {
            updateColor(clr, save, fld) {
                if (save) {
                    this.$root.color_palette.unshift(clr);
                    localStorage.setItem('color_palette', this.$root.color_palette.join(','));
                }
                this.tb_theme[fld] = clr;
                this.propChanged();
            },
            clearColor(fld) {
                this.tb_theme[fld] = null;
                this.re_init = true;
                this.$nextTick(() => {
                    this.re_init = false;
                });
                this.propChanged();
            },
            propChanged() {
                this.$emit('prop-changed');
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "ThemeTable";
</style>