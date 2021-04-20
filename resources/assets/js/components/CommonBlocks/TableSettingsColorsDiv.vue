<template>
    <div>
        <div class="flex flex--center-v flex--space form-group">
            <div>Top panel background:</div>
            <div class="l-inl-colorpicker" v-if="!re_init">
                <tablda-colopicker
                        :init_color="tb_theme.navbar_bg_color"
                        :saved_colors="$root.color_palette"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'navbar_bg_color')}"
                ></tablda-colopicker>
            </div>
            <button v-if="tb_theme.navbar_bg_color"
                    class="btn btn-danger btn-sm"
                    @click="clearColor('navbar_bg_color')"
            >&times;</button>
        </div>
        <div class="flex flex--center-v flex--space form-group">
            <div>Table header background:</div>
            <div class="l-inl-colorpicker" v-if="!re_init">
                <tablda-colopicker
                        :init_color="tb_theme.table_hdr_bg_color"
                        :saved_colors="$root.color_palette"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'table_hdr_bg_color')}"
                ></tablda-colopicker>
            </div>
            <button v-if="tb_theme.table_hdr_bg_color"
                    class="btn btn-danger btn-sm"
                    @click="clearColor('table_hdr_bg_color')"
            >&times;</button>
        </div>
        <div class="flex flex--center-v flex--space form-group">
            <div>Buttons:</div>
            <div class="l-inl-colorpicker" v-if="!re_init">
                <tablda-colopicker
                        :init_color="tb_theme.button_bg_color"
                        :saved_colors="$root.color_palette"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'button_bg_color')}"
                ></tablda-colopicker>
            </div>
            <button v-if="tb_theme.button_bg_color"
                    class="btn btn-danger btn-sm"
                    @click="clearColor('button_bg_color')"
            >&times;</button>
        </div>
        <div class="flex flex--center-v flex--space form-group">
            <div>Ribbon:</div>
            <div class="l-inl-colorpicker" v-if="!re_init">
                <tablda-colopicker
                        :init_color="tb_theme.ribbon_bg_color"
                        :saved_colors="$root.color_palette"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'ribbon_bg_color')}"
                ></tablda-colopicker>
            </div>
            <button v-if="tb_theme.ribbon_bg_color"
                    class="btn btn-danger btn-sm"
                    @click="clearColor('ribbon_bg_color')"
            >&times;</button>
        </div>
        <div class="flex flex--center-v flex--space form-group">
            <div>Main background:</div>
            <div class="l-inl-colorpicker" v-if="!re_init">
                <tablda-colopicker
                        :init_color="tb_theme.main_bg_color"
                        :saved_colors="$root.color_palette"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'main_bg_color')}"
                ></tablda-colopicker>
            </div>
            <button v-if="tb_theme.main_bg_color"
                    class="btn btn-danger btn-sm"
                    @click="clearColor('main_bg_color')"
            >&times;</button>
        </div>
        <div class="flex flex--center-v flex--space form-group">
            <div>Text Font Size:</div>
            <div class="l-inl-colorpicker" v-if="!re_init">
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
            </div>

        <div class="flex flex--center-v flex--space form-group">
            <div>Text Font Color:</div>
            <div class="l-inl-colorpicker" v-if="!re_init">
                <tablda-colopicker
                        :init_color="tb_theme.app_font_color"
                        :saved_colors="$root.color_palette"
                        :avail_null="true"
                        @set-color="(clr,save)=>{updateColor(clr,save,'app_font_color')}"
                ></tablda-colopicker>
            </div>
            <button v-if="tb_theme.app_font_color"
                    class="btn btn-danger btn-sm"
                    @click="clearColor('app_font_color')"
            >&times;</button>
        </div>

        </div>
    </div>
</template>

<script>
    import TabldaColopicker from "./../CustomCell/InCell/TabldaColopicker.vue";

    export default {
        name: 'TableSettingsColorsDiv',
        components: {
            TabldaColopicker
        },
        data() {
            return {
                re_init: false,
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
    .l-inl-colorpicker {
        position: relative;
        width: 56px;
        margin-left: 5px;
        height: 28px;
        border: 2px solid #AAA;
        border-radius: 5px;
    }

    .flex--center-v {
        width: 75%;
        margin-left: 40px;
        position: relative;

        .btn-sm {
            padding: 3px 6px;
            position: absolute;
            right: -24px;
        }
    }
</style>