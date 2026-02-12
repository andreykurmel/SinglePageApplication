<template>
    <div v-if="is_vis">
        <div class="popup-wrapper" @click.self="close()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain" v-html="getPopUpHeader()"></div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="close()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div class="content_popup__body">
                            <div v-if="obj_type==='Email'" class="adder">
                                <input class="form-control" v-model="new_email">
                                <button class="btn btn-success" @click="addEmail()">Add</button>
                            </div>
                            <div v-if="obj_type==='Phone Number'" class="adder">
                                <phone-block v-model="tel_phone" :full_width="true"></phone-block>
                                <button class="btn btn-success" @click="addPhone()">Add</button>
                            </div>
                            <div class="presents">
                                <div v-for="(item, idx) in items">
                                    <span v-html="obj_type==='Phone Number' ? $root.telFormat(item) : item"></span>
                                    <i class="glyphicon glyphicon-remove hover-red" @click="remItem(idx)"></i>
                                </div>
                            </div>
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
    import PhoneBlock from "../CommonBlocks/PhoneBlock";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    export default {
        name: "CellEmailPhonePopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            PhoneBlock
        },
        data: function () {
            return {
                is_vis: false,

                new_email: '',
                tel_phone: '',
                obj_type: '',
                obj_header: null,
                obj_row: null,
                items: null,
                uniq_id: null,

                idx: 0,
                getPopupWidth: 500,
                getPopupHeight: '300px',
            }
        },
        props: {
            tableMeta: Object,
        },
        methods: {
            getPopUpHeader() {
                return this.obj_type === 'Email' ? 'Add Email Address(es)'
                    : (this.obj_type === 'Phone Number' ? 'Add Phone Number(s)' : '');
            },
            addEmail() {
                if (String(this.new_email).match(/\S+@\S+\.\S+/gi)) {
                    this.items.push(this.new_email);
                    this.new_email = '';
                } else {
                    Swal('Info','Invalid email address!');
                }
            },
            addPhone() {
                if (String(this.tel_phone).match(/\+[0-9]{8,12}/gi)) {
                    this.items.push(this.tel_phone);
                    this.tel_phone = '';
                } else {
                    Swal('Info','Invalid phone number!');
                }
            },
            remItem(idx) {
                this.items.splice(idx, 1);
            },
            hidePop(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.close();
                    this.$root.set_e__used(this);
                }
            },
            close() {
                eventBus.$emit('cell-email-phone-popup__update', this.uniq_id, this.items);
                this.is_vis = false;
            },
            cellEmailPhonePopupShow(obj) {
                this.obj_type = obj.type;
                this.obj_header = obj.header;
                this.obj_row = obj.row;
                this.items = obj.items;
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
            eventBus.$on('cell-email-phone-popup__show', this.cellEmailPhonePopupShow);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
            eventBus.$off('cell-email-phone-popup__show', this.cellEmailPhonePopupShow);
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

        .popup-header {
            height: 22px;

            .header-btn {
                top: 0px;
            }
        }

        .popup-content {

            .popup-main {
                padding: 5px;
            }
            .content_popup__body {
                padding: 0 5px;
                height: 100%;
                overflow: auto;
                .adder {
                    display: flex;
                }
                .presents {
                    border: 1px solid #777;
                    border-radius: 5px;
                    padding: 5px;
                    height: calc(100% - 40px);
                }
            }
        }
    }
</style>