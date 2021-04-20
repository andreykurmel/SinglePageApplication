<template>
    <div class="popup-wrapper" @click.self.stop="$emit('close-app')">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">{{ tb_app.name }}</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click.stop="$emit('close-app')"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="popup-main full-height">
                        <iframe :src="app_path" width="100%" height="100%"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    export default {
        name: "CustomApplicationPopUp",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
        },
        data: function () {
            return {
                //PopupAnimationMixin
                getPopupWidth: 768,
                idx: 0,
            };
        },
        props:{
            tb_app: Object,
            app_path: String
        },
        methods: {
            hideMenu(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.$emit('close-app');
                    this.$root.set_e__used(this);
                }
                if (e.target.nodeName === 'BODY' && e.shiftKey && e.keyCode === 191) {//shift + '?'
                    this.$emit('close-app');
                }
            },
            globalCloseApplication(app_name) {
                this.$emit('close-app', app_name);
            }
        },
        mounted() {
            this.runAnimation();
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('global-close-application', this.globalCloseApplication);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('global-close-application', this.globalCloseApplication);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";
</style>