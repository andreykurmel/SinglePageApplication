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
                        <div v-else="" class="content_popup__body">
                            <textarea
                                    v-model="html"
                                    @blur="updateVal()"
                                    @click.stop=""
                                    ref="inline_input"
                                    class="full-height form-control textarea-autosize"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import TextareaAutosize from '../CustomCell/InCell/TextareaAutosize';

    export default {
        name: "TableDataStringPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            TextareaAutosize,
        },
        data: function () {
            return {
                obj: null,

                is_vis: false,
                edit: false,
                can_edit: false,

                html: '',

                uniq_id: null,

                idx: 0,
                getPopupWidth: 500,
                getPopupHeight: '300px',
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
                    this.$nextTick(() => {
                        if (this.$refs.inline_input) {
                            this.$refs.inline_input.focus();
                        }
                    });
                }
            },
            updateVal() {
                eventBus.$emit('table-data-string-popup__update', this.uniq_id, this.html);
                this.close();
            },
            hidePop(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.close();
                    this.$root.set_e__used(this);
                }
            },
            close() {
                this.edit = false;
                this.is_vis = false;
            },
            getHeaders() {
                let headers = this.obj && this.obj.meta ? this.obj.meta._fields : [];
                let row = this.obj ? this.obj.row : [];
                let globalShows = [];
                _.each(headers, (hdr) => {
                    if (hdr.popup_header) {
                        globalShows.push('{' + this.$root.uniqName(hdr.name) + '}: ' + (row ? (row[hdr.field] || '') : ''));
                    }
                });
                globalShows = globalShows.length ? globalShows.join('<br>') : '';

                let localShows = this.$root.uniqName(this.obj ? '{'+this.obj.header.name+'}:' : '');

                return (globalShows ? globalShows+'<br>' : '') + localShows;
            },
            tableDataStringShowHandler(obj) {
                if (obj.behavior === this.behavior) {
                    this.obj = obj;
                    this.can_edit = obj.can_edit;
                    this.html = obj.html;
                    this.uniq_id = obj.uniq_id;
                    this.is_vis = true;
                    this.$root.tablesZidx += 10;
                    this.zIdx = this.$root.tablesZidx + 700;
                    this.$nextTick(() => {
                        this.runAnimation();
                    });
                }
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
                padding: 0 5px;
                height: 100%;
                overflow: auto;
            }
            .textarea-autosize {
                padding: 0 5px;
                font-size: 13px;
            }
        }
    }
</style>