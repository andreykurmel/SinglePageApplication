<template>
    <div>
        <i class="fa fa-info-circle" ref="fatooltip" @click.stop="showToolts"></i>
        <hover-block v-if="help_tooltip"
                     :html_str="html_str"
                     :p_left="help_left"
                     :p_top="help_top"
                     :c_offset="outer_offset || help_offset"
                     @another-click="help_tooltip = false"
        ></hover-block>
    </div>
</template>

<script>
    import HoverBlock from "./HoverBlock";

    export default {
        name: "TooltipBlock",
        data: function () {
            return {
                help_tooltip: false,
                help_left: 0,
                help_top: 0,
                help_offset: 0,
            };
        },
        props:{
            html_str: String,
            outer_offset: Number,
            left_move: Number,
            top_move: Number,
        },
        methods: {
            showToolts(e) {
                let bounds = this.$refs.fatooltip ? this.$refs.fatooltip.getBoundingClientRect() : {};
                let px = (bounds.left + bounds.right) / 2;
                let py = (bounds.top + bounds.bottom) / 2;
                this.help_tooltip = true;
                this.help_left = (px || e.clientX) + (this.left_move || 0);
                this.help_top = (py || e.clientY) + (this.top_move || 0);
                this.help_offset = Math.abs(bounds.top - bounds.bottom) || 0;
            },
        },
        created() {
        },
    }
</script>

<style lang="scss" scoped>
</style>