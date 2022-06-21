<template>
    <div class="full-size-image" ref="full_block" @click="$emit('close-full-img')">
        <div class="image-wrapper" @click.stop="">
            <carousel-block :images="file_arr" :image_idx="file_idx"></carousel-block>
        </div>
    </div>
</template>

<script>
    import CarouselBlock from "./CarouselBlock";
    export default {
        components: {CarouselBlock}, name: "FullSizeImgBlock",
        data: function () {
            return {
            };
        },
        props:{
            file_arr: Array,
            file_idx: Number,
        },
        mounted() {
            $(this.$refs.full_block).appendTo(this.$root.$el);
        }
    }
</script>

<style lang="scss" scoped>
    .full-size-image {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        z-index: 3500;
        background-color: rgba(0,0,0,0.2);

        .image-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #FFF;
            border: 3px solid #CCC;
            border-radius: 5px;
            width: 70%;
            height: 80%;
        }
    }
</style>