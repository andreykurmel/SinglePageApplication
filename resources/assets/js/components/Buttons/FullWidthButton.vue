<template>
    <button class="full_width_button btn btn-default blue-gradient" :style="$root.themeButtonStyle" title="Toggle between Max. and Default Width">
        <i class="fa fa-arrows-alt-h i-top" :style="{color: (fullWidthCell ? '#222' : '#FFF')}"></i>
        <i class="fa fa-arrows-alt-h i-bottom" :style="{color: (!fullWidthCell ? '#222' : '#FFF')}"></i>
        <div class="i-bottom-border"></div>
    </button>
</template>

<script>
    export default {
        name: "FullWidthButton",
        mixins: [
        ],
        data: function () {
            return {}
        },
        props:{
            fullWidthCell: Boolean,
        },
        methods: {}
    }
</script>

<style scoped>
    .full_width_button {
        cursor: pointer;
        width: 35px;
        height: 30px;
        padding: 0;
        font-size: 22px;
        background-color: transparent;
        position: relative;
        overflow: hidden;
        outline: none;
    }
    .full_width_button > .i-top {
        font-size: 1.5em;
        position: absolute;
        top: -9px;
        left: 0;
    }
    .full_width_button > .i-bottom {
        position: absolute;
        bottom: -6px;
        left: 0;
    }
    .full_width_button > .i-bottom-border {
        width: 23px;
        height: 12px;
        position: absolute;
        bottom: 0;
        left: 0;
        border-right: 1px solid #222;
    }
</style>