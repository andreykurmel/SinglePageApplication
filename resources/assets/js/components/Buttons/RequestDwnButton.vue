<template>
    <div ref="dwn_button" class="download_main_button" title="Download Data" v-if="dcrObject">
        <button class="btn btn-default blue-gradient"
                @click="menu_opened = !menu_opened"
                :style="$root.themeButtonStyle"
        >
            <i class="glyphicon glyphicon-download-alt"></i>
        </button>
        <div v-show="menu_opened" class="download_menu">
            <button v-if="dcrObject.download_pdf"
                    type="button"
                    class="btn btn-default download_btn active"
                    @click="storePDF(dwnWindowElem)"
                    style="background-color: #aa0;">PDF (W)</button>

            <button v-if="dcrObject.download_png"
                    type="button"
                    class="btn btn-default download_btn active"
                    @click="storePNG(dwnWindowElem)"
                    style="background-color: #a0a;">PNG (W)</button>

            <button v-if="dcrObject.download_pdf"
                    type="button"
                    class="btn btn-default download_btn active"
                    @click="storePDF(dwnFormElem)"
                    style="background-color: #aa0;">PDF (F)</button>

            <button v-if="dcrObject.download_png"
                    type="button"
                    class="btn btn-default download_btn active"
                    @click="storePNG(dwnFormElem)"
                    style="background-color: #a0a;">PNG (F)</button>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    export default {
        name: "RequestDwnButton",
        mixins: [
        ],
        data: function () {
            return {
                menu_opened: false
            }
        },
        props:{
            dwnWindowElem: String,
            dwnFormElem: String,
            dcrObject: Object,
        },
        methods: {
            storePNG(elemId) {
                this.$emit('force-flow', true);
                this.$nextTick(() => {
                    let elem = document.getElementById(elemId);
                    export_to_png(elem, this.dcrObject.name, {noscale:true});
                    this.$emit('force-flow', false);
                });
            },
            storePDF(elemId) {
                this.$emit('force-flow', true);
                this.$nextTick(() => {
                    let elem = document.getElementById(elemId);
                    export_to_pdf(elem, this.dcrObject.name);
                    this.$emit('force-flow', false);
                });
            },
            hideMenu(e) {
                let container = $(this.$refs.dwn_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .download_main_button {
        cursor: pointer;
        width: 35px;
        height: 30px;
        padding: 0;
        background-color: transparent;
        position: relative;
        outline: none;

        button {
            width: 100%;
            height: 100%;
            padding: 3px 0 0 0;
        }

        .download_menu {
            position: absolute;
            top: 100%;
            right: 100%;
            display: flex;
            z-index: 500;
            padding: 5px;
            background-color: #FFF;
            border: 1px solid #CCC;

            .download_btn {
                border-radius: 50%;
                width: 50px;
                height: 40px;
                opacity: 0.1;
                color: #FFF;
                font-weight: bold;
                font-size: 0.8em;
                padding: 1px 0 0 0;
            }
            .active {
                opacity: 0.4;

                &:hover {
                    opacity: 0.9;
                }
            }
        }
    }
</style>