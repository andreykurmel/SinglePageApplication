<template>
    <div class="info_wrapper" ref="info_wrapper" @click="show()">
        <div v-if="edit" class="full-height">
            <div class="btns-wrapper">
                <select class="form-control" v-model="fontSize" @change="addTag('font')">
                    <option v-for="val in fontsArr" :value="val">Font: {{ val }}px</option>
                </select>
                <div class="mini-colorpicker">
                    <tablda-colopicker
                            :init_color="fontColor"
                            :saved_colors="$root.color_palette"
                            :show_text="'Color'"
                            :avail_null="true"
                            @set-color="setColor"
                    ></tablda-colopicker>
                </div>
                <button class="btn btn-default btn-sm" @click="addTag('strike')">Strikeout</button>
                <button class="btn btn-default btn-sm" @click="addTag('bold')">Bold</button>
                <button class="btn btn-default btn-sm" @click="addTag('italic')">Italic</button>
                <button class="btn btn-default btn-sm" @click="addTag('paragraph')">Paragraph</button>
                <button class="btn btn-default btn-sm" @click="addTag('ordered')">Ord. List</button>
                <button class="btn btn-default btn-sm" @click="addTag('unordered')">Unord. List</button>
                <button class="btn btn-default btn-sm" @click="addTag('item')">List Item</button>

                <select v-if="tableMeta" class="right_el form-control" v-model="selFld" @change="addTag('field_link')">
                    <option v-for="flld in tableMeta._fields" :value="flld.name">Field: {{ flld.name }}</option>
                </select>
            </div>
            <textarea ref="info_panel"
                      class="edit-textarea"
                      v-model="infoTXT"
                      @blur="getCursorPos()"
            ></textarea>
        </div>
        <span class="full-height" v-else="" v-html="$root.strip_tags(infoTXT)"></span>
    </div>
</template>

<script>
    import TabldaColopicker from '../../CustomCell/InCell/TabldaColopicker';

    import {eventBus} from '../../../app';

    export default {
        name: "HtmlEditPanel",
        components: {
            TabldaColopicker
        },
        data: function () {
            return {
                edit: false,
                selectionStart: null,
                fontColor: null,
                selFld: this.tableMeta ? _.first(this.tableMeta._fields).name : '',
                fontSize: 10,
                fontsArr: [6, 8, 10, 12, 14, 16, 18, 20, 24, 28],
                infoTXT: this.initText || '',
            }
        },
        props:{
            tableMeta: Object,
            initText: String,
        },
        methods: {
            show() {
                this.edit = true;
                this.$nextTick(function () {
                    if (this.$refs.info_panel) {
                        this.$refs.info_panel.focus();
                    }
                });
            },

            setColor(clr, save) {
                if (save) {
                    this.$root.color_palette.unshift(clr);
                    localStorage.setItem('color_palette', this.$root.color_palette.join(','));
                }
                this.fontColor = clr;
                this.addTag('color');
            },

            //helper functions
            getCursorPos() {
                this.selectionStart = this.$refs.info_panel.selectionStart;
            },
            addTag(type) {
                let elem = '';
                switch (type) {
                    case 'field_link': elem = '{' + this.selFld + '}'; break;
                    case 'font': elem = '<span style="font-size: ' + this.fontSize + 'px;"></span>'; break;
                    case 'color': elem = '<span style="color: ' + this.fontColor + ';"></span>'; break;
                    case 'strike': elem = '<span style="text-decoration: line-through;"></span>'; break;
                    case 'bold': elem = '<b></b>'; break;
                    case 'italic': elem = '<i></i>'; break;
                    case 'paragraph': elem = '<p style=""></p>'; break;
                    case 'ordered': elem = '<ol></ol>'; break;
                    case 'unordered': elem = '<ul></ul>'; break;
                    case 'item': elem = '<li></li>'; break;
                }

                let l_part = String(this.infoTXT).substr(0,this.selectionStart);
                let r_part = String(this.infoTXT).substr(this.selectionStart);

                this.infoTXT = String(l_part) + elem + String(r_part);
                this.$refs.info_panel.focus();
            },

            //save changes
            tableNoteChanged() {
                if (this.infoTXT !== this.initText) {
                    this.infoTXT = this.$root.strip_tags(this.infoTXT);
                    this.$emit('txt-update', this.infoTXT);
                }
            },

            hideMenu(e) {
                let container = $(this.$refs.info_wrapper);
                if (this.edit === true && container.has(e.target).length === 0){
                    this.edit = false;
                    this.tableNoteChanged();
                }
            }
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
    .info_wrapper {
        height: 100%;
        width: 100%;
        border: 1px solid #CCC;
        overflow-y: auto;
        overflow-x: hidden;

        span {
            display: block;
        }

        textarea {
            height: 100%;
            width: 100%;
            resize: none;
        }

        button {
            height: 28px;
            padding: 3px 5px;
        }

        select {
            font-size: 12px;
            height: 28px;
            padding: 3px;
            width: 85px;
            display: inline-block;
        }

        .mini-colorpicker {
            display: inline-block;
            height: 28px;
            width: 60px;
            position: relative;
            border: 1px solid #aaa;
        }

        .btns-wrapper {
            display: flex;
            padding: 1px;
            height: 30px;
        }

        .edit-textarea {
            height: calc(100% - 30px);
        }

        .right_el {
            position: absolute;
            right: 0;
            width: 150px;
        }
    }
</style>