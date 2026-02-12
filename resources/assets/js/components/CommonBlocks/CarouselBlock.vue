<template>
    <div class="carousel slide full-height" data-ride="carousel">
        <!-- Wrapper for slides -->
        <div class="carousel-inner full-height" role="listbox">
            <div
                v-for="(image,idx) in images"
                class="item full-height"
                :style="{
                    display: (idx === active_idx || idx === prev_idx || idx === next_idx) ? 'block' : 'none',
                    left: getLeft(idx),
                    transition: 'all '+transition_duration+'s ease-in'
                }"
            >
                <single-attachment-block
                    :table_meta="table_meta"
                    :table_header="table_header"
                    :table_row="table_row"
                    :attachment="image"
                    :is_full_size="!can_click"
                    @img-clicked="() => { can_click ? $emit('img-clicked', images, idx) : null }"
                ></single-attachment-block>
            </div>
        </div>

        <!-- Controls -->
        <template v-if="images && images.length > 1">
            <a class="left carousel-control"
               role="button"
               data-slide="prev"
               @click.stop.prevent="prevSlide()"
               @mousedown.stop.prevent=""
               @mouseup.stop.prevent=""
            >
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="right carousel-control"
               role="button"
               data-slide="next"
               @click.stop.prevent="nextSlide()"
               @mousedown.stop.prevent=""
               @mouseup.stop.prevent=""
            >
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </template>
    </div>
</template>

<script>
    import SingleAttachmentBlock from "./SingleAttachmentBlock";
    export default {
        name: "CarouselBlock",
        mixins: [
        ],
        components: {
            SingleAttachmentBlock
        },
        data: function () {
            return {
                prev_idx: null,
                next_idx: null,
                active_idx: this.image_idx || 0,
                transition_duration: 0.6,
            };
        },
        computed: {
        },
        props:{
            table_meta: Object,
            table_header: Object,
            table_row: Object,
            images: Array,
            image_idx: Number,
            can_click: Boolean,
        },
        methods: {
            //controls
            nextSlide() {
                if (this.prev_idx === null && this.next_idx === null) {
                    if (this.active_idx === this.images.length - 1) {
                        this.next_idx = 0;
                        this.moveImages(true);
                    } else {
                        this.next_idx = this.active_idx + 1;
                        this.moveImages(true);
                    }
                }
            },
            prevSlide() {
                if (this.prev_idx === null && this.next_idx === null) {
                    if (this.active_idx === 0) {
                        this.prev_idx = this.images.length - 1;
                        this.moveImages(false);
                    } else {
                        this.prev_idx = this.active_idx - 1;
                        this.moveImages(false);
                    }
                }
            },

            //movement functions
            moveImages(forvard) {
                setTimeout(() => {
                    if (forvard) {
                        this.prev_idx = this.active_idx;
                        this.active_idx = this.next_idx;
                    } else {
                        this.next_idx = this.active_idx;
                        this.active_idx = this.prev_idx;
                    }
                    setTimeout(this.clearMoving, this.transition_duration*1000);
                }, 100);
            },
            clearMoving() {
                this.prev_idx = null;
                this.next_idx = null;
            },

            //styles
            getLeft(idx) {
                let left;
                if (idx === this.active_idx) {
                    left = 0;
                } else
                if (idx === this.prev_idx) {
                    left = '-100%';
                } else
                if (idx === this.next_idx) {
                    left = '100%';
                }
                return left;
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .full-height {
        height: 100%;
    }

    .carousel {
        .carousel-inner {
            .item {
                /*transition: all 1s ease-in;*/
                position: absolute;
                width: 100%;
                top: 0;
            }
        }
    }
</style>