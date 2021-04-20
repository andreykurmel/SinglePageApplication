<template>
    <div ref="color_button" class="color_wrapper full-height" title="Select Color">
        <div
            class="color_button full-height"
            ref="color_smart"
            :style="{backgroundColor: showed_color, height: '100%'}"
            @click="menu_opened = (can_edit ? !menu_opened : false)"
        >{{ show_text }}</div>

        <button v-if="showed_color && avail_null && can_edit" class="btn btn-danger btn-sm btn-deletable flex flex--center" @click.stop.prevent="delColor()">
            <span>×</span>
        </button>

        <div v-show="menu_opened" class="color_menu" :style="wrapColorEdit">
            <div class="palette">
                <ul v-for="colors in palette">
                    <li v-for="clr in colors" :style="{backgroundColor: clr}" @click="setColor(clr)">
                        <div v-if="showed_color === clr" class="selected_dot"></div>
                    </li>
                </ul>
            </div>
            <div class="palette palette--saved">
                <ul>
                    <li v-for="idx in 8" :style="{backgroundColor: saved_colors[idx-1] || null}" @click="setColor(saved_colors[idx-1] || null)">
                        <div v-if="showed_color === saved_colors[idx-1]" class="selected_dot"></div>
                    </li>
                </ul>
            </div>
            <div class="palette--saved">
                <select class="form-control color-input color-input--select" v-model="color_type">
                    <option>HEX</option>
                    <option>RGB</option>
                    <option>HSV</option>
                </select>
                <input class="form-control color-input color-input--input" v-model="color_input" :placeholder="getPlaceholder()" @change="inputChanged()"/>
            </div>
            <div class="palette--saved">
                <button class="btn btn-primary eyedropper-input" @click.stop="startEyedropper()">Eyedropper</button>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../app';

    import MixinSmartPosition from './../Selects/MixinSmartPosition';

    export default {
        name: "TabldaColopicker",
        mixins: [
            MixinSmartPosition,
        ],
        data: function () {
            return {
                prepared: false,
                one_prevent: !!this.init_menu,
                showed_color: this.init_color,
                color_type: 'RGB',
                color_input: null,
                menu_opened: !!this.init_menu,
                eye_drop: false,
                palette: [
                    ["#000000","#444444","#666666","#999999","#cccccc","#eeeeee","#f3f3f3","#ffffff"],
                    ["#ff0000","#ff9900","#ffff00","#00ff00","#00ffff","#0000ff","#9900ff","#ff00ff"],
                    ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
                    ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
                    ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
                    ["#cc0000","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
                    ["#990000","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
                    ["#660000","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
                ],
            }
        },
        props:{
            init_color: String|null,
            can_edit: {
                type: Boolean,
                default: () => { return true; }
            },
            saved_colors: {
                type: Array,
                default: () => { return []; }
            },
            fixed_pos: Boolean,
            menu_shift: Boolean,
            show_text: String,
            avail_null: Boolean,
            init_menu: Boolean,
        },
        computed: {
            wrapColorEdit() {
                let style = this.fixed_pos && this.prepared
                    ? this.ItemsListStyle()
                    : {};
                style.position = (this.fixed_pos ? 'fixed' : 'absolute');
                style.right = (!this.fixed_pos && this.menu_shift ? '0' : null);
                style.zIndex = 2000;
                style.width = '168px';
                return style;
            },
        },
        watch: {
            init_color(val) {
                this.showed_color = val;
            },
            menu_opened(val) {
                this.prepared = false;
                this.$nextTick(() => {
                    this.showItemsList();
                    this.prepared = true;
                });
            },
        },
        methods: {
            getPlaceholder() {
                let str = '';
                switch (this.color_type) {
                    case 'HEX': str = '0099FF';
                    break;
                    case 'RGB': str = '0-255, 0-255, 0-255';
                    break;
                    case 'HSV': str = '0-360, 0-100%, 0-100%';
                    break;
                }
                return str;
            },
            inputChanged() {
                let str = '';
                switch (this.color_type) {
                    case 'HEX': str = (this.color_input && this.color_input.charAt(0) !== '#' ? '#' : '') + this.color_input;
                        break;
                    default: str = this.color_type + '(' + this.color_input + ')';
                        break;
                }
                this.parseColor(str);
            },
            parseColor(str) {
                str = str.toLowerCase();
                let res_color = window.Color(str).toString();
                this.setColor(res_color);
            },
            setColor(clr) {
                this.color_input = null;
                this.menu_opened = false;

                clr = clr && clr.charAt(0) === '#' ? clr : null;
                clr = clr ? clr.toLowerCase() : null;

                let save = true;
                _.each(this.palette, (arr) => {
                    if (arr.indexOf(clr) > -1) {
                        save = false;
                    }
                });
                if (this.saved_colors.indexOf(clr) > -1) {
                    save = false;
                }

                this.showed_color = clr;
                this.$emit('set-color', clr, save);
            },
            delColor() {
                this.showed_color = null;
                this.$emit('set-color', null);
            },
            startEyedropper() {
                this.eye_drop = true;
                $('body').css('cursor', 'cell');
            },
            finishEyedropper(e) {
                let clr = '';
                if (e.target.nodeName.toLowerCase() !== 'img') {
                    for (let i = 0; i < e.path.length; i++) {
                        let computedStyle = window.getComputedStyle(e.path[i]);
                        clr = computedStyle.backgroundColor !== "rgba(0, 0, 0, 0)" ? computedStyle.backgroundColor : clr;
                        if (clr) { break; }
                    }
                } else {
                    let canvas = document.createElement("canvas");
                    canvas.width = e.target.width;
                    canvas.height = e.target.height;
                    canvas.getContext('2d').drawImage(e.target, 0, 0);

                    clr = canvas.getContext('2d').getImageData(e.offsetX, e.offsetY, 1, 1).data;
                    clr = 'rgb('+clr[0]+','+clr[1]+','+clr[2]+')';

                    canvas.remove();
                }

                this.eye_drop = false;
                $('body').css('cursor', '');

                this.parseColor(clr);
            },
            hideMenu(e) {
                if (this.one_prevent) {
                    this.one_prevent = false;
                    return;
                }
                let container = $(this.$refs.color_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
            globalClickHangler(e) {
                if (this.eye_drop) {
                    this.finishEyedropper(e);
                } else {
                    this.hideMenu(e);
                }
            }
        },
        created() {
            eventBus.$on('global-click', this.globalClickHangler);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        mounted() {
            this.smart_wrapper = 'color_smart';
            this.smart_limit = 280;
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.globalClickHangler);
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .color_wrapper {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        outline: none;

        .color_button {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-deletable {
            position: absolute;
            top: 10%;
            right: 3px;
            bottom: 10%;
            padding: 0 3px;

            span {
                font-size: 1.4em;
                line-height: 0.7em;
                display: inline-block;
            }
        }

        .color_menu {
            position: absolute;
            width: 168px;
            z-index: 500;
            background-color: #FFF;
            border: 1px solid #CCC;
            padding: 5px 0 5px 5px;

            .palette {
                ul {
                    overflow: hidden;
                    padding: 0;
                    margin: 0;
                }
                li {
                    list-style: none;
                    width: 15px;
                    height: 15px;
                    float: left;
                    margin-right: 5px;
                    margin-bottom: 5px;
                    position: relative;
                    cursor: pointer;
                    border: 1px solid #CCC;

                    .selected_dot {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        height: 7px;
                        width: 7px;
                        border-radius: 50%;
                        border: 1px solid #CCC;
                        background: #fff;
                    }
                }
            }

            .palette--saved {
                margin-top: 5px;
            }

            .color-input {
                display: inline-block;
            }
            .color-input--select {
                width: 60px;
                padding: 6px 3px;
            }
            .color-input--input {
                width: calc(100% - 70px);
            }

            .eyedropper-input {
                width: calc(100% - 5px);
            }
        }
    }
</style>