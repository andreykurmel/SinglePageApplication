<template>
    <div ref="align_button" class="align-wrapper full-frame" title="Select an Align">
        <div class="content align-button full-frame" @click="menuToggle()" :style="{justifyContent: flexAlign}">
            <i class="fas" :class="[iconAlign]" :style="textStyleNoFont"></i>
        </div>
        <div v-show="menu_opened" class="align-helper" :style="helperStyle">
            <i class="fas fa-align-left" @click="setAlign('left')" :style="textStyleNoFont"></i>
            <i class="fas fa-align-center" @click="setAlign('center')" :style="textStyleNoFont"></i>
            <i class="fas fa-align-right" @click="setAlign('right')" :style="textStyleNoFont"></i>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from '../../_Mixins/CellStyleMixin.vue';
    
    import {eventBus} from '../../../app';

    export default {
        name: "AlignOfColumn",
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                menu_opened: false,
            }
        },
        props:{
            tableMeta: Object,//style mixin
            tableRow: Object,
        },
        computed: {
            helperStyle() {
                switch (this.tableRow.col_align) {
                    case 'left' : return { left: 0, right: 'initial' };
                    case 'right' : return { left: 'initial', right: 0 };
                    default : return { left: '50%', transform: 'translateX(-50%)', right: 'initial' };
                }
            },
            iconAlign() {
                switch (this.tableRow.col_align) {
                    case 'left' : return 'fa-align-left';
                    case 'right' : return 'fa-align-right';
                    default : return 'fa-align-center';
                }
            },
            flexAlign() {
                switch (this.tableRow.col_align) {
                    case 'left' : return 'flex-start';
                    case 'right' : return 'flex-end';
                    default : return 'center';
                }
            },
            textStyleNoFont() {
                let obj = this.textStyle;
                obj.fontFamily = null;
                return obj;
            },
        },
        methods: {
            menuToggle() {
                this.menu_opened = !this.menu_opened;
            },
            setAlign(val) {
                this.$emit('set-align', val);
                this.menu_opened = false;
            },
            hideAlignHelper(e) {
                if (this.menu_opened) {
                    let container = $(this.$refs.align_button);
                    if (container.has(e.target).length === 0) {
                        this.menu_opened = false;
                    }
                }
            },
        },
        created() {
            eventBus.$on('global-click', this.hideAlignHelper);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideAlignHelper);
        }
    }
</script>

<style lang="scss" scoped>
    .align-wrapper {
        position: relative;
        overflow: initial;
        background-color: #FFF;

        .align-button {
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 0 5px;
        }

        .align-helper {
            position: absolute;
            bottom: 100%;
            right: 0;
            border: 1px solid #CCC;
            padding: 5px;
            background-color: white;
            border-radius: 5px;
            z-index: 1500;
            white-space: nowrap;

            .fas {
                cursor: pointer;
                margin: 3px 5px;
                font-size: 20px;
            }
        }
    }
</style>