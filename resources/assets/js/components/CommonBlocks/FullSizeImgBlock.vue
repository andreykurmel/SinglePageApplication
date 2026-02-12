<template>
    <div class="full-size-image" ref="full_block" @click.self="close()">

        <div v-if="single_full_size_image" class="image-wrapper" :style="imgWrapPosition">
            <carousel-block
                :table_meta="table_meta"
                :table_header="table_header"
                :table_row="table_row"
                :images="file_arr"
                :image_idx="file_idx"
                :can_click="false"
            ></carousel-block>
        </div>
        <div v-else class="image-wrapper image-wrapper--sm" :style="imgWrapPosition">
            <div class="header-btn" @mouseenter="show_vtype = true" @mouseleave="show_vtype = false">
                <i style="color: #FFF;margin-left: 3px;" class="fas pull-right" :title="viewTypeTitle" :class="viewTypeIcon"></i>
                <div v-if="show_vtype" class="view-type-wrapper">
                    <i v-if="sel_vtype != 'list'" class="fas pull-right fa-th-list" title="List" @click="sel_vtype = 'list'"></i>
                    <i v-if="sel_vtype != 'grid'" class="fas pull-right fa-th" title="Grid" @click="sel_vtype = 'grid'"></i>
                    <i v-if="sel_vtype != 'carousel'" class="fas pull-right fa-ellipsis-h" title="Carousel" @click="sel_vtype = 'carousel'"></i>
                </div>
            </div>

            <carousel-block v-if="sel_vtype === 'carousel'"
                            :images="file_arr"
                            :image_idx="file_idx"
                            :can_click="true"
                            @img-clicked="(images, idx) => { single_file = images[idx] }"
            ></carousel-block>

            <div v-if="sel_vtype === 'grid'" class="grid-wrapper">
                <div v-for="file in file_arr" class="grid-item">
                    <single-attachment-block
                        :attachment="file"
                        :is_full_size="false"
                        @img-clicked="() => { single_file = file }"
                    ></single-attachment-block>
                    <div class="grid-item--name">{{ file.filename }}</div>
                </div>
            </div>

            <div v-if="sel_vtype === 'list'" class="list-wrapper">
                <div v-for="file in file_arr" class="list-item flex">
                    <single-attachment-block
                        :attachment="file"
                        :is_full_size="false"
                        @img-clicked="() => { single_file = file }"
                    ></single-attachment-block>
                    <div class="list-item--name flex flex--center-v">{{ file.filename }}</div>
                </div>
            </div>
        </div>
        

        <full-size-img-block
            v-if="single_file"
            :table_meta="table_meta"
            :table_header="table_header"
            :table_row="table_row"
            :file_arr="[single_file]"
            :file_idx="0"
            :single_full_size_image="true"
            @close-full-img="single_file = null"
        ></full-size-img-block>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import CarouselBlock from "./CarouselBlock";
    import SingleAttachmentBlock from "./SingleAttachmentBlock";

    export default {
        components: {
            SingleAttachmentBlock,
            CarouselBlock,
        },
        name: "FullSizeImgBlock",
        data: function () {
            return {
                show_vtype: false,
                sel_vtype: 'carousel',
                single_file: null,
                block_index: null,
            };
        },
        computed: {
            viewTypeIcon() {
                switch (this.sel_vtype) {
                    case 'list': return ['fa-th-list'];
                    case 'grid': return ['fa-th'];
                    case 'carousel': return ['fa-ellipsis-h'];
                }
                return [''];
            },
            viewTypeTitle() {
                switch (this.sel_vtype) {
                    case 'list': return 'List';
                    case 'grid': return 'Grid';
                    case 'carousel': return 'Carousel';
                }
                return '';
            },
            imgWrapPosition() {
                //let vertBrowser = window.innerHeight > window.innerWidth;
                let isVideo = _.find(this.file_arr, (f) => {
                    return f.is_video || ['youtube','vimeo'].indexOf(f.special_mark) > -1;
                });

                let overflowModifier = Math.max(window.innerWidth * 0.56 / window.innerHeight * 1.05, 1); // 1 or more
                let videoHeight = window.innerWidth * 0.56 / overflowModifier;
                let videoWidth = window.innerWidth / overflowModifier;
                let videoTop = (window.innerHeight - videoHeight) / 2;
                let videoLeft = (window.innerWidth - videoWidth) / 2;

                return {
                    top: isVideo ? videoTop+'px' : '5%',
                    left: isVideo ? videoLeft+'px' : '5%',
                    width: isVideo ? videoWidth+'px' : '90%',
                    height: isVideo ? videoHeight+'px' : '90%',
                }
            },
        },
        props:{
            table_meta: Object,
            table_header: Object,
            table_row: Object,
            file_arr: Array,
            file_idx: Number,
            single_full_size_image: Boolean,
        },
        methods: {
            close() {
                window.full_size_img_block = (window.full_size_img_block || 1) - 1;
                this.$emit('close-full-img');
            },
            hideMenu(e) {
                if (e.keyCode === 27 && this.block_index == window.full_size_img_block) {//esc
                    this.close();
                }
            },
        },
        mounted() {
            $(this.$refs.full_block).appendTo(this.$root.$el);

            if (window.full_size_img_block < 0) {
                window.full_size_img_block = 0;
            }
            window.full_size_img_block = (window.full_size_img_block || 0) + 1;
            this.block_index = window.full_size_img_block;

            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
        },
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

        .header-btn {
            cursor: pointer;
            color: #FFF;
            position: absolute;
            right: 5px;
            top: 5px;
            z-index: 100;

            .view-type-wrapper {
                right: 24px;
                position: absolute;
                background-color: transparent;
                width: max-content;
                white-space: nowrap;
                top: 0;
            }

            .fas {
                color: rgb(255, 255, 255);
                margin-left: 3px;
                padding: 3px;
                background: #777;
                border-radius: 3px;
            }
        }

        .image-wrapper {
            position: absolute;
            background-color: #FFF;
            border: 3px solid #CCC;
            border-radius: 5px;
            overflow: auto;
        }
        .image-wrapper--sm {
            width: 60%;
            height: 70%;
        }

        .grid-wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 15px;
            grid-auto-rows: minmax(100px, auto);
            padding: 15px;

            .grid-item {
                height: 300px;
                position: relative;
                background: #ddd;
                padding-bottom: 22px;

                .grid-item--name {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    padding: 0 5px;
                    text-align: center;
                    font-size: 14px;
                }
            }
        }

        .list-wrapper {
            padding: 15px;

            .list-item {
                height: 200px;
                width: 100%;
                margin-bottom: 15px;
                background: #ddd;

                .list-item--name {
                    width: 70%;
                    padding: 10px;
                }
            }
        }
    }
</style>