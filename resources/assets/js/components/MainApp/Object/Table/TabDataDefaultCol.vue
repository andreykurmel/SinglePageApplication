<template>
    <div ref="def_col_wrap">
        <input type="text"
               class="form-control"
               :disabled="is_disabled"
               v-model="header.f_formula"
               @focus="header.input_type === 'Formula' ? show_help = true : null"
        />

        <formula-helper
                v-if="show_help"
                :user="$root.user"
                :table-meta="meta"
                :table-row="header"
                :header-key="'f_formula'"
                :pop_width="'100%'"
                @set-formula="show_help = false"
        ></formula-helper>
    </div>
</template>

<script>
    import {eventBus} from './../../../../app';

    import FormulaHelper from "../../../CustomCell/InCell/FormulaHelper";

    export default {
        components: {
            FormulaHelper
        },
        name: 'TabDataDefaultCol',
        data: function () {
            return {
                show_help: false,
            };
        },
        props:{
            meta: Object,
            header: Object,
            is_disabled: Boolean,
        },
        computed: {
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            hideMenu(e) {
                let container = $(this.$refs.def_col_wrap);
                if (this.show_help && container.has(e.target).length === 0){
                    this.show_help = false;
                }
            },
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
        },
    }
</script>

<style lang="scss" scoped>
</style>