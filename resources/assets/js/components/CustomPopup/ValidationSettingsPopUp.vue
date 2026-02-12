<template>
    <div>
        <div class="popup-wrapper" @click.self="hide()"></div>

        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Validations</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">

                        <div class="flex flex--col">
                            <div class="full-frame tb_wrap">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th :width="getWi('rule')">
                                            <span>Rule</span>
                                            <header-resizer :table-header="tb_headers.rule" :user="{id:0}"></header-resizer>
                                        </th>
                                        <th :width="getWi('val')">
                                            <span>Value</span>
                                            <header-resizer :table-header="tb_headers.val" :user="{id:0}"></header-resizer>
                                        </th>
                                        <th :width="getWi('err')">
                                            <span>Error Message</span>
                                            <header-resizer :table-header="tb_headers.err" :user="{id:0}"></header-resizer>
                                        </th>
                                        <th :width="getWi('action')">
                                            <span>Action</span>
                                            <header-resizer :table-header="tb_headers.action" :user="{id:0}"></header-resizer>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tr v-for="(elem,i) in validationRules">
                                        <td>
                                            <select-block
                                                :options="availRules()"
                                                :sel_value="elem.rule"
                                                style="height: 32px;"
                                                @option-select="(opt) => { elem.rule = opt.val }"
                                            ></select-block>
                                        </td>
                                        <td>
                                            <input v-if="elem.rule === 'Email'" class="form-control" disabled style="height: 32px;"/>
                                            <input v-else class="form-control" v-model="elem.val" style="height: 32px;"/>
                                        </td>
                                        <td>
                                            <input class="form-control" v-model="elem.err" style="height: 32px;"/>
                                        </td>
                                        <td>
                                            <button class="blue-gradient" :style="$root.themeButtonStyle" style="height: 32px;" @click="removeRule(i)">
                                                <i class="glyphicon glyphicon-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select-block
                                                :options="availRules()"
                                                :sel_value="newElem.rule"
                                                style="height: 32px;"
                                                @option-select="(opt) => { newElem.rule = opt.val }"
                                            ></select-block>
                                        </td>
                                        <td>
                                            <input v-if="newElem.rule === 'Email'" class="form-control" disabled style="height: 32px;"/>
                                            <input v-else class="form-control" v-model="newElem.val" style="height: 32px;"/>
                                        </td>
                                        <td>
                                            <input class="form-control" v-model="newElem.err" style="height: 32px;"/>
                                        </td>
                                        <td>
                                            <button class="blue-gradient" :style="$root.themeButtonStyle" style="height: 32px;" @click="addRule()">Add</button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import {Validator} from "../../classes/Validator";

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import HeaderResizer from "../CustomTable/Header/HeaderResizer";
    import SelectBlock from "../CommonBlocks/SelectBlock.vue";

    export default {
        name: "ValidationSettingsPopUp",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            SelectBlock,
            HeaderResizer,
        },
        data: function () {
            return {
                newElem: Validator.ruleObject(),
                tb_headers: {
                    rule: {min_width:10, width: 150, max_width: 500},
                    val: {min_width:10, width: 300, max_width: 500},
                    err: {min_width:10, width: 300, max_width: 500},
                    action: {min_width:10, width: 70, max_width: 500},
                },
                validationRules: [],
                //PopupAnimationMixin
                getPopupHeight: '400px',
                getPopupWidth: 900,
                idx: 0,
            };
        },
        computed: {
        },
        props: {
            tableHeader: Object,
        },
        methods: {
            getWi(key) {
                let all_sum = _.sum( _.map(this.tb_headers,'width') );
                return ((this.tb_headers[key].width / all_sum) * 100) + '%';
            },
            parseRules() {
                this.validationRules = Validator.getRules(this.tableHeader);
            },
            availRules() {
                return [
                    { val:'Min', show:'Min' },
                    { val:'Max', show:'Max' },
                    { val:'Email', show:'Email' },
                    { val:'Regex', show:'Regex' },
                ];
            },
            removeRule(i) {
                this.validationRules.splice(i, 1);
            },
            addRule() {
                this.validationRules.push(_.clone(this.newElem));
                this.newElem = Validator.ruleObject();
            },
            hide() {
                this.$emit('popup-close', Validator.rulesSet(this.tableHeader, this.validationRules));
            },
        },
        mounted() {
            this.runAnimation();
            this.parseRules();
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";
    @import "./../CustomTable/Table";

    .popup {
        font-size: initial;
        cursor: auto;

        .popup-content {
            .popup-main {
                padding: 5px;

                label {
                    margin: 0;
                }

                .tb_wrap {
                    border: 1px solid #CCC;
                }

                .popup-buttons {
                    height: 90px;

                    button {
                        margin-top: 0;
                    }
                }
            }
        }
    }

    .ml5 {
        margin-left: 5px;
    }

    .table {
        width: 100%;
        table-layout: fixed;

        th {
            position: sticky;
            top: 0;
        }
        .name_td {
            display: block;
            max-width: 100%;
            overflow: hidden;
        }
    }

    .copy-table {
        position: absolute;
        z-index: -1;
        top: 0;
    }
</style>