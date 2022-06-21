<template>
    <div class="wrap">
        <div v-for="i in level"
             class="border"
             :style="{left: ((i-1)*hdr_margin_lft)+'px'}"
        ></div>
    </div>
</template>

<script>
    export default {
        name: "VerticalTableBorder",
        data: function () {
            return {
                hdr_margin_lft: 15,
            };
        },
        props:{
            level: Number,
        },
        methods: {
        },
    }
</script>

<style lang="scss" scoped>
    .wrap {
        .border {
            position: absolute;
            width: 5px;
            top: -4px;
            bottom: -4px;
            background-color: #CCC;
        }
    }
</style>