<template>
    <div class="wrap" v-if="level > 0">
        <div v-for="i in level"
             class="border"
             :style="{
                left: ((i-1)*hdr_margin_lft)+'px',
                top: '-1px',
                bottom: '1px',
             }"
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
        props: {
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
            background-color: #CCC;
        }
    }
</style>