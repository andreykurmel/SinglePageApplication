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
                                        <th :width="getWi('attr')">
                                            <span>Attribute</span>
                                            <header-resizer :table-header="tb_headers.attr" :user="{id:0}"></header-resizer>
                                        </th>
                                        <th :width="getWi('val')">
                                            <span>Value</span>
                                            <header-resizer :table-header="tb_headers.val" :user="{id:0}"></header-resizer>
                                        </th>
                                        <th :width="getWi('action')">
                                            <span>Action</span>
                                            <header-resizer :table-header="tb_headers.action" :user="{id:0}"></header-resizer>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tr v-for="(elem,i) in variableAttributes">
                                        <td>
                                            <select-block
                                                :options="availAttributes()"
                                                :sel_value="elem.attr"
                                                style="height: 32px;"
                                                @option-select="(opt) => { elem.attr = opt.val }"
                                            ></select-block>
                                        </td>
                                        <td>
                                            <input v-if="elem.attr === 'Email'" class="form-control" disabled style="height: 32px;"/>
                                            <input v-else class="form-control" v-model="elem.val" style="height: 32px;"/>
                                        </td>
                                        <td>
                                            <button class="blue-gradient" :style="$root.themeButtonStyle" style="height: 32px;" @click="removeAttr(i)">
                                                <i class="glyphicon glyphicon-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select-block
                                                :options="availAttributes()"
                                                :sel_value="newElem.attr"
                                                style="height: 32px;"
                                                @option-select="(opt) => { newElem.attr = opt.val }"
                                            ></select-block>
                                        </td>
                                        <td>
                                            <input v-if="newElem.attr === 'Email'" class="form-control" disabled style="height: 32px;"/>
                                            <input v-else class="form-control" v-model="newElem.val" style="height: 32px;"/>
                                        </td>
                                        <td>
                                            <button class="blue-gradient" :style="$root.themeButtonStyle" style="height: 32px;" @click="addAttr()">Add</button>
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
    import {ReportVariable} from "../../classes/ReportVariable";

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import HeaderResizer from "../CustomTable/Header/HeaderResizer";
    import SelectBlock from "../CommonBlocks/SelectBlock.vue";

    export default {
        name: "ReportVariableAttributesPopUp",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            SelectBlock,
            HeaderResizer,
        },
        data: function () {
            return {
                newElem: ReportVariable.attrObject(),
                tb_headers: {
                    attr: {min_width:10, width: 150, max_width: 500},
                    val: {min_width:10, width: 150, max_width: 500},
                    action: {min_width:10, width: 50, max_width: 500},
                },
                variableAttributes: [],
                //PopupAnimationMixin
                getPopupHeight: '400px',
                getPopupWidth: 500,
                idx: 0,
            };
        },
        computed: {
        },
        props: {
            reportVariable: Object,
        },
        methods: {
            getWi(key) {
                let all_sum = _.sum( _.map(this.tb_headers,'width') );
                return ((this.tb_headers[key].width / all_sum) * 100) + '%';
            },
            parseAttributes() {
                this.variableAttributes = ReportVariable.getAttributes(this.reportVariable);
            },
            availAttributes() {
                if (this.reportVariable.variable_type === 'field') {
                    return [];
                }

                let attrs = [ { val:'Width', show:'Width' } ];
                if (this.reportVariable.variable_type === 'field') {
                    attrs.push({ val:'Height', show:'Height' });
                }
                return attrs;
            },
            removeAttr(i) {
                this.variableAttributes.splice(i, 1);
            },
            addAttr() {
                this.variableAttributes.push(_.clone(this.newElem));
                this.newElem = ReportVariable.attrObject();
            },
            hide() {
                this.$emit('popup-close', ReportVariable.attrsSet(this.reportVariable, this.variableAttributes));
            },
        },
        mounted() {
            this.runAnimation();
            this.parseAttributes();
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
</style>