<template>
    <div class="rort-modal" ref="rort_link" v-show="rortShow" :style="{left: rortModal.left+'px', top: rortModal.top+'px'}">
        <div>
            <a @click.stop="showSrcRecord()" :title="link ? link.tooltip : ''">
                Record
            </a>
        </div>
        <div>
            <a target="_blank" :title="link ? link.tooltip : ''" :href="tb_link">
                Table
            </a>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../app';

    export default {
        name: "LinkRortModal",
        data: function () {
            return {
                rortShow: false,
                rortModal: {
                    top: 0,
                    left: 0
                },
                uuid: null,
                link: null,
                tb_link: null,
            }
        },
        methods: {
            showSrcRecord() {
                eventBus.$emit('clicked-rort-src', this.uuid);
            },
            showRortModal() {
                this.rortShow = true;
                if (this.rortShow) {
                    this.rortModal.left = window.event.clientX;
                    this.rortModal.top = window.event.clientY;
                }
            },
            hideMenu(e) {
                this.rortShow = false;
            },
            clickedRortSrcHandler(uuid, link, tb_link) {
                this.uuid = uuid;
                this.link = link;
                this.tb_link = tb_link;
                this.showRortModal();
            }
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('show-rort-modal', this.clickedRortSrcHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('clicked-rort-src', this.clickedRortSrcHandler);
        }
    }
</script>

<style lang="scss" scoped>
    .rort-modal {
        position: fixed;
        z-index: 2500;
        background: #FFF;
        border: 1px solid #CCC;
        border-radius: 5px;
        padding: 5px 10px;

        a {
            cursor: pointer;
        }
    }
</style>