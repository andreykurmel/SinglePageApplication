<template>
    <div ref="settings_button" class="settings_wrapper">
        <button class="btn btn-default blue-gradient flex flex--center settings_btn"
                :style="$root.themeButtonStyle"
                @click="menu_opened = !menu_opened"
        >
            <i class="fas fa-cog"></i>
        </button>

        <div v-show="menu_opened" class="settings_menu">
            <div class="flex flex--center-v mb5">
                <label class="no-margin">BKGD Colors:</label>
            </div>
            <div class="flex flex--center-v mb5" style="justify-content: flex-end;">
                <label class="no-margin">- Interface:&nbsp;</label>
                <div style="width: 120px; height: 28px; position: relative; border: 1px solid #ccc; border-radius: 3px;">
                    <tablda-colopicker
                        :init_color="selAi.bg_color"
                        :avail_null="true"
                        :can_edit="can_edit"
                        @set-color="(clr) => {selAi.bg_color = clr; $emit('ai-updated')}"
                    ></tablda-colopicker>
                </div>
            </div>
            <div class="flex flex--center-v mb5" style="justify-content: flex-end;">
                <label class="no-margin">- Question:&nbsp;</label>
                <div style="width: 120px; height: 28px; position: relative; border: 1px solid #ccc; border-radius: 3px;">
                    <tablda-colopicker
                        :init_color="selAi.bg_me_color"
                        :avail_null="true"
                        :can_edit="can_edit"
                        @set-color="(clr) => {selAi.bg_me_color = clr; $emit('ai-updated')}"
                    ></tablda-colopicker>
                </div>
            </div>
            <div class="flex flex--center-v mb5" style="justify-content: flex-end;">
                <label class="no-margin">- Answer:&nbsp;</label>
                <div style="width: 120px; height: 28px; position: relative; border: 1px solid #ccc; border-radius: 3px;">
                    <tablda-colopicker
                        :init_color="selAi.bg_gpt_color"
                        :avail_null="true"
                        :can_edit="can_edit"
                        @set-color="(clr) => {selAi.bg_gpt_color = clr; $emit('ai-updated')}"
                    ></tablda-colopicker>
                </div>
            </div>
            <div class="flex flex--center-v mb5" style="justify-content: flex-end;">
                <label class="no-margin">Font:&nbsp;</label>
                <div style="width: 120px; height: 28px; position: relative; z-index: 30;">
                    <tablda-select-simple
                        :options="[
                            {val: 'Arial', show: 'Arial'},
                            {val: 'Calibri', show: 'Calibri'},
                            {val: 'Courier New', show: 'Courier New'},
                            {val: 'Helvetica', show: 'Helvetica'},
                            {val: 'Times New Roman', show: 'Times New Roman'},
                        ]"
                        :table-row="selAi"
                        :hdr_field="'font_family'"
                        :fld_input_type="'S-Select'"
                        :init_no_open="true"
                        @selected-item="(clr) => {selAi.font_family = clr; $emit('ai-updated')}"
                    ></tablda-select-simple>
                </div>
            </div>
            <div class="flex flex--center-v mb5" style="justify-content: flex-end;">
                <label class="no-margin">Effects:&nbsp;</label>
                <div style="width: 120px; height: 28px; position: relative; z-index: 20;">
                    <tablda-select-simple
                        :options="[
                            {val: 'Normal', show: 'Normal'},
                            {val: 'Italic', show: 'Italic'},
                            {val: 'Bold', show: 'Bold'},
                            {val: 'Strikethrough', show: 'Strikethrough'},
                            {val: 'Overline', show: 'Overline'},
                            {val: 'Underline', show: 'Underline'},
                        ]"
                        :table-row="selAi"
                        :hdr_field="'font_style'"
                        :fld_input_type="'M-Select'"
                        :init_no_open="true"
                        @selected-item="updateArrayFont"
                    ></tablda-select-simple>
                </div>
            </div>
            <div class="flex flex--center-v mb5" style="justify-content: flex-end;">
                <label class="no-margin">Size:&nbsp;</label>
                <div style="width: 120px; height: 28px; position: relative; z-index: 10;">
                    <tablda-select-simple
                        :options="[
                            {val: '10', show: '10'},
                            {val: '12', show: '12'},
                            {val: '14', show: '14'},
                            {val: '16', show: '16'},
                            {val: '20', show: '20'},
                        ]"
                        :table-row="selAi"
                        :hdr_field="'font_size'"
                        :fld_input_type="'S-Select'"
                        :init_no_open="true"
                        @selected-item="(clr) => {selAi.font_size = clr; $emit('ai-updated')}"
                    ></tablda-select-simple>
                </div>
            </div>
            <div class="flex flex--center-v" style="justify-content: flex-end;">
                <label class="no-margin">Color:&nbsp;</label>
                <div style="width: 120px; height: 28px; position: relative; border: 1px solid #ccc; border-radius: 3px;">
                    <tablda-colopicker
                        :init_color="selAi.txt_color"
                        :avail_null="true"
                        :can_edit="can_edit"
                        @set-color="(clr) => {selAi.txt_color = clr; $emit('ai-updated')}"
                    ></tablda-colopicker>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import TabldaSelectSimple from "../../../../CustomCell/Selects/TabldaSelectSimple.vue";
    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker.vue";

    export default {
        name: 'AiSettings',
        mixins: [
        ],
        components: {
            TabldaColopicker,
            TabldaSelectSimple,
        },
        data() {
            return {
                menu_opened: false,
            }
        },
        computed: {
        },
        props: {
            selAi: Object,
            can_edit: Boolean|Number,
        },
        watch: {
        },
        methods: {
            updateArrayFont(item) {
                this.selAi.font_style = Array.isArray(this.selAi.font_style)
                    ? this.selAi.font_style
                    : [String(this.selAi.font_style || '')];

                if (this.selAi.font_style.indexOf(item) > -1) {
                    this.selAi.font_style.splice( this.selAi.font_style.indexOf(item), 1 );
                } else {
                    this.selAi.font_style.push(item);
                }
                this.$emit('ai-updated');
            },
            hideMenu(e) {
                let container = $(this.$refs.settings_button);
                if (this.menu_opened && container.has(e.target).length === 0) {
                    this.menu_opened = false;
                }
            },
        },
        mounted() {
            eventBus.$on('global-click', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .settings_wrapper {
        .settings_btn {
            width: 35px;
            height: 30px;
            padding: 0px;
        }
        .settings_menu {
            position: absolute;
            left: 0;
            top: 100%;
            padding: 5px;
            background: #fff;
            border: 1px solid #777;
            border-radius: 5px;
            z-index: 10;
            color: #222;
        }
    }
</style>