<template>
    <table class="theme-table">
        <tr>
            <td class="td-header" colspan="2" rowspan="2"></td>
            <td class="td-header" colspan="3">System Themes</td>
            <td class="td-header" colspan="3">Themes by User</td>
        </tr>
        <tr>
            <td class="td-header" v-for="(theme,idx) in $root.user._ava_themes">
                {{ words[idx] }}
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right" colspan="2">Select a Theme --></td>
            <td v-for="theme in $root.user._ava_themes">
                <input type="radio" v-model="$root.user.app_theme_id" :value="theme.id" @change="changedSelTheme(theme)"/>
            </td>
        </tr>
        <tr>
            <td class="td-header" rowspan="7">
                <span class="components-span">Components</span>
            </td>
            <td class="td-header txt-right">Top Panel Background</td>
            <td v-for="theme in $root.user._ava_themes">
                <tablda-colopicker
                        :init_color="theme.navbar_bg_color"
                        :can_edit="canThEdit(theme)"
                        :saved_colors="$root.color_palette"
                        :menu_shift="true"
                        :avail_null="true"
                        @set-color="(clr) => {theme.navbar_bg_color = clr; updateTheme(theme)}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Table Header Background</td>
            <td v-for="theme in $root.user._ava_themes">
                <tablda-colopicker
                        :init_color="theme.table_hdr_bg_color"
                        :can_edit="canThEdit(theme)"
                        :saved_colors="$root.color_palette"
                        :menu_shift="true"
                        :avail_null="true"
                        @set-color="(clr) => {theme.table_hdr_bg_color = clr; updateTheme(theme)}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Buttons</td>
            <td v-for="theme in $root.user._ava_themes">
                <tablda-colopicker
                        :init_color="theme.button_bg_color"
                        :can_edit="canThEdit(theme)"
                        :saved_colors="$root.color_palette"
                        :menu_shift="true"
                        :avail_null="true"
                        @set-color="(clr) => {theme.button_bg_color = clr; updateTheme(theme)}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Ribbon</td>
            <td v-for="theme in $root.user._ava_themes">
                <tablda-colopicker
                        :init_color="theme.ribbon_bg_color"
                        :can_edit="canThEdit(theme)"
                        :saved_colors="$root.color_palette"
                        :menu_shift="true"
                        :avail_null="true"
                        @set-color="(clr) => {theme.ribbon_bg_color = clr; updateTheme(theme)}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Main Background</td>
            <td v-for="theme in $root.user._ava_themes">
                <tablda-colopicker
                        :init_color="theme.main_bg_color"
                        :can_edit="canThEdit(theme)"
                        :saved_colors="$root.color_palette"
                        :menu_shift="true"
                        :avail_null="true"
                        @set-color="(clr) => {theme.main_bg_color = clr; updateTheme(theme)}"
                ></tablda-colopicker>
            </td>
        </tr>
        <tr>
            <td class="td-header txt-right">Text Font Size</td>
            <td v-for="theme in $root.user._ava_themes">
                <select class="form-control full-frame"
                        :disabled="!canThEdit(theme)"
                        style="padding: 0;"
                        v-model="theme.app_font_size"
                        @change="updateTheme(theme)"
                >
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
            <td v-for="theme in $root.user._ava_themes">
                <tablda-colopicker
                        :init_color="theme.app_font_color"
                        :can_edit="canThEdit(theme)"
                        :saved_colors="$root.color_palette"
                        :menu_shift="true"
                        :avail_null="true"
                        @set-color="(clr) => {theme.app_font_color = clr; updateTheme(theme)}"
                ></tablda-colopicker>
            </td>
        </tr>

    </table>
</template>

<script>
    import TabldaColopicker from "../CustomCell/InCell/TabldaColopicker";

    export default {
        name: "ThemeSelectorBlock",
        components: {
            TabldaColopicker
        },
        data: function () {
            return {
                words: ['A','B','C','D','E','F'],
            };
        },
        props:{
        },
        methods: {
            canThEdit(theme) {
                return (theme.obj_type === 'system' && this.$root.user.is_admin) // sys themes can edit only Admin
                    ||
                    (theme.obj_type === 'user' && theme.obj_id === this.$root.user.id); // users themes can edit owner
            },
            updateTheme(theme) {
                $.LoadingOverlay('show');
                axios.put('/ajax/app/theme', {
                    theme_id: theme.id,
                    fields: theme,
                }).then(({ data }) => {
                    if (
                        (this.$root.user._selected_theme.obj_type === theme.obj_type)
                        &&
                        (this.$root.user._selected_theme.obj_id === theme.obj_id)
                    ) {
                        this.$root.user._selected_theme = theme;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            changedSelTheme(theme) {
                $.LoadingOverlay('show');
                axios.put('/ajax/user/set-sel-theme', {
                    app_theme_id: theme.id,
                }).then(({ data }) => {
                    this.$root.user._selected_theme = theme;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "ThemeTable";
</style>