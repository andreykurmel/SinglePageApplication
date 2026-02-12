<template>
    <iframe :src="iframe_path" width="100%" height="100%"></iframe>
</template>

<script>
    import {eventBus} from "../../app";

    export default {
        name: 'StimIframeApp',
        mixins: [
        ],
        components: {
        },
        data() {
            return {
                stimapptop: false,
            }
        },
        props: {
            iframe_path: String,
        },
        methods: {
            globalKeyHandler(e) {
                let cmdOrCtrl = e.metaKey || e.ctrlKey;
                if (cmdOrCtrl && e.keyCode === 38) {//ctrl + up arrow
                    this.stimapptop = !this.stimapptop;
                    (this.stimapptop ? $('#main_navbar').show() : $('#main_navbar').hide());
                }
            },
        },
        mounted() {
            $('#main_navbar').hide();
            eventBus.$on('global-keydown', this.globalKeyHandler);
        }
    }
</script>

<style lang="scss" scoped>
</style>