<template>
    <div ref="color_button"
         class="color_wrapper full-height"
         title="Select Color"
         @mouseenter="mousein=true"
         @mouseleave="mousein=false"
         @mousedown="propStop"
         @mouseup="propStop"
    >
        <div
            class="color_button full-height"
            ref="color_smart"
            :style="{backgroundColor: showed_color, height: '100%'}"
            @click="opnMenu()"
        >{{ show_text }}</div>

        <button v-if="showed_color && avail_null && can_edit && mousein"
                class="btn btn-danger btn-sm btn-deletable flex flex--center"
                @click.stop.prevent="delColor()"
        >
            <span>×</span>
        </button>

        <div v-show="menu_opened" class="color_menu" ref="clr_menu_ref" :style="wrapColorEdit">
            <div class="palette">
                <ul v-for="colors in palette">
                    <li v-for="clr in colors" :style="{backgroundColor: clr}" @click="setColor(clr)">
                        <div v-if="sameClr(clr)" class="selected_dot"></div>
                    </li>
                </ul>
            </div>
            <div class="palette palette--saved">
                <ul>
                    <li v-for="idx in 8" :style="{backgroundColor: $root.color_palette[idx] || null}" @click="customColor(idx)">
                        <div v-if="sameClr($root.color_palette[idx])" class="selected_dot"></div>
                    </li>
                </ul>
            </div>
            <div class="palette--saved">
                <select class="form-control color-input color-input--select" v-model="color_type" @change="syncColorInput()">
                    <option>HEX</option>
                    <option>RGB</option>
                    <option>HSV</option>
                </select>
                <input class="form-control color-input color-input--input" v-model="color_input" :placeholder="getPlaceholder()" @change="inputChanged()"/>
            </div>
            <div class="palette--saved">
                <button class="btn btn-primary eyedropper-input" @click.stop="startEyedropper()">Eyedropper</button>
            </div>

            <div v-show="custom_color" class="spec_picker" @mousedown="mDo" @mouseup="mUp" :style="specStyle">
                <div ref="spec_wrap_pick"></div>
                <div class="picker-buttons">
                    <button class="btn btn-sm btn-success" @click="setCustomColor()">Select</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ColorPicker from 'simple-color-picker';

    import {eventBus} from '../../../app';

    import MixinSmartPosition from '../../_Mixins/MixinSmartPosition';

    export default {
        name: "TabldaColopicker",
        mixins: [
            MixinSmartPosition,
        ],
        data: function () {
            return {
                uuid: uuidv4(),
                mousein: false,
                prepared: false,
                one_prevent: !!this.init_menu,
                showed_color: null,
                color_type: 'RGB',
                color_input: '',
                menu_opened: !!this.init_menu,
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
                spec_picker: null,
                spec_pos: 'right',
                no_stop: false,
                custom_color: 0,
            }
        },
        props:{
            init_color: String|null,
            can_edit: {
                type: Boolean,
                default: () => { return true; }
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
            specStyle() {
                return {
                    left: this.spec_pos === 'right' ? '100%' : null,
                    right: this.spec_pos !== 'right' ? '100%' : null,
                };
            },
        },
        watch: {
            init_color: {
                handler(val) {
                    this.showed_color = val;
                    this.syncColorInput();
                },
                immediate: true,
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
            sameClr(clr) {
                return String(this.showed_color).toLowerCase() === String(clr).toLowerCase();
            },
            mDo(e) {
                this.no_stop = true;
            },
            mUp(e) {
                setTimeout(() => {
                    this.no_stop = false;
                }, 1);
            },
            propStop(e) {
                if (!this.$root.eye_drop && !this.no_stop) {
                    e.stopPropagation();
                }
            },
            opnMenu() {
                this.menu_opened = (this.can_edit && !this.$root.eye_drop ? !this.menu_opened : false);
            },
            syncColorInput() {
                if (this.showed_color) {
                    let clr = window.Color(this.showed_color);
                    switch (this.color_type) {
                        case 'HEX':
                            this.color_input = clr.toString().substr(1);
                            break;
                        case 'RGB':
                            let r = Math.round( clr.toRGB().red*255 );
                            let g = Math.round( clr.toRGB().green*255 );
                            let b = Math.round( clr.toRGB().blue*255 );
                            this.color_input = r+', '+g+', '+b;
                            break;
                        case 'HSV':
                            let h = Math.round( clr.toHSL().hue );
                            let s = Math.round( clr.toHSL().saturation*100 );
                            let l = Math.round( clr.toHSL().lightness*100 );
                            this.color_input = h+', '+s+'%, '+l+'%';
                            break;
                    }
                } else {
                    this.color_input = '';
                }
            },
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
                    case 'RGB': str = 'rgb(' + this.color_input + ')';
                        break;
                    case 'HSV': str = 'hsl(' + this.color_input + ')';
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

                this.showed_color = clr;
                this.syncColorInput();
                this.$emit('set-color', clr);
            },
            delColor() {
                this.showed_color = null;
                this.syncColorInput();
                this.$emit('set-color', null);
            },
            startEyedropper() {
                this.$root.eye_drop = this.uuid;
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

                window.setTimeout(() => {//prevent opening another color pisker.
                    this.$root.eye_drop = false;
                }, 100);
                $('body').css('cursor', '');

                this.parseColor(clr);
            },
            hideMenu(e) {
                if (!this.menu_opened) {
                    return;
                }
                if (this.one_prevent) {
                    this.one_prevent = false;
                    return;
                }
                let container = $(this.$refs.color_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                    this.custom_color = 0;
                }
            },
            globalClickHangler(e) {
                if (this.uuid === this.$root.eye_drop) {
                    this.finishEyedropper(e);
                } else {
                    this.hideMenu(e);
                }
            },
            //Custom Colors
            specColorChange(hex_color) {
                this.$root.saveColorToPalette(hex_color, this.custom_color);
            },
            customColor(idx) {
                let wi = this.$root.isRightMenu ? 450 : 250;
                let rect = this.$refs.clr_menu_ref.getBoundingClientRect();
                this.spec_pos = window.innerWidth - rect.right > wi ? 'right' : 'left';

                this.custom_color = 0;
                if (this.custom_color !== idx) {
                    this.$nextTick(() => {
                        this.spec_picker.setColor( this.$root.color_palette[idx] );
                        this.custom_color = idx;
                    });
                }
            },
            setCustomColor() {
                this.setColor( this.$root.color_palette[this.custom_color] );
                this.custom_color = 0;
            },
        },
        created() {
            eventBus.$on('global-click', this.globalClickHangler);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        mounted() {
            this.smart_wrapper = 'color_smart';
            this.smart_limit = 280;

            this.spec_picker = new ColorPicker({
                color: '#FFFFFF',
                background: '#454545',
                el: this.$refs.spec_wrap_pick,
                width: 180,
                height: 220,
            }).onChange(this.specColorChange);
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

            .spec_picker {
                position: absolute;
                height: 100%;
                width: 200px;
                top: 0;
                background-color: #FFF;
                border-left: 1px solid #CCC;
                padding: 5px;

                .picker-buttons {
                    position: absolute;
                    bottom: 0;
                    right: 0;
                    padding: 5px;
                }
            }
        }
    }
</style>

<style>
    .Scp {
        box-sizing: content-box;
    }
</style>