<template>
    <div v-if="is_vis">
        <div class="popup-wrapper" @click.self="close()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            <span v-html="getHeaders()"></span>
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="close()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">

                    <div class="flex__elem__inner popup-main" @click="editPopup()">
                        <div v-if="!edit" v-html="html" class="content_popup__body"></div>
                        <div v-else class="content_popup__body">
                            <Editor
                                v-model="html"
                                @click.stop=""
                                class="textarea-autosize"
                            ></Editor>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import {eventBus} from '../../app';

    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import Editor from '../CommonBlocks/Editor.vue';

    export default {
        name: "TableDataStringPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            Editor,
        },
        data: function () {
            return {
                obj: null,

                is_vis: false,
                edit: false,
                can_edit: false,

                html: '',
                newHtml: '',

                uniq_id: null,

                idx: 0,
                getPopupWidth: 720,
                getPopupHeight: '350px',
            }
        },
        props: {
            maxCellRows: {
                type: Number,
                default: 1
            },
        },
        methods: {
            editPopup() {
                if (this.can_edit) {
                    this.edit = true;
                }
            },
            hidePop(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.close();
                    this.$root.set_e__used(this);
                }
            },
            close() {
                if (this.edit) {
                    eventBus.$emit('table-data-string-popup__update', this.uniq_id, this.$root.strip_danger_tags(this.html));
                }
                this.edit = false;
                this.is_vis = false;
            },
            getHeaders() {
                let headers = this.obj && this.obj.meta ? this.obj.meta._fields : [];
                let row = this.obj ? this.obj.row : [];
                let globalShows = [];
                _.each(headers, (hdr) => {
                    if (hdr.popup_header || hdr.popup_header_val) {
                        let row_value = row
                            ? SpecialFuncs.showhtml(hdr, row, row[hdr.field], this.obj.meta)
                            : '';
                        let ar = hdr.popup_header ? [this.$root.uniqName(hdr.name)] : [];
                        if (hdr.popup_header_val) { ar.push(row_value) }
                        globalShows.push( ar.join(': ') );
                    }
                });
                globalShows = globalShows.length ? globalShows.join('<br>') : '';

                let localShows = this.$root.uniqName(this.obj ? '{'+this.obj.header.name+'}:' : '');

                return (globalShows ? globalShows+'<br>' : '') + localShows;
            },
            tableDataStringShowHandler(obj) {
                this.obj = obj;
                this.can_edit = obj.can_edit;
                this.html = obj.html;
                this.newHtml = obj.html;
                this.uniq_id = obj.uniq_id;
                this.is_vis = true;
                this.$root.tablesZidxIncrease();
                this.zIdx = this.$root.tablesZidx + 700;
                this.$nextTick(() => {
                    this.runAnimation();
                });
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('table-data-string-popup__show', this.tableDataStringShowHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('table-data-string-popup__show', this.tableDataStringShowHandler);
        }
    }
</script>

<style scoped lang="scss">
    @import "./CustomEditPopUp";

    .popup-wrapper {
        z-index: 2000;
    }

    .popup {
        z-index: 2500;

        .popup-content {

            .popup-main {
                padding: 5px;
            }
            .content_popup__hdr {
                background-color: #CCC;
                padding: 0 5px;
                height: 20px;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .content_popup__body {
                height: 100%;
                overflow: auto;
            }
            .textarea-autosize {
                font-size: 13px;
                height: calc(100% - 45px);
            }
        }
    }
</style>