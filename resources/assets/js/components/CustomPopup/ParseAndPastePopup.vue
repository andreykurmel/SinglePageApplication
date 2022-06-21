<template>
    <div>
        <div class="popup-wrapper" @click.self="$emit('popup-close', false)"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Paste To Import</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close')"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">

                        <div v-if="!step2" class="flex flex--col">
                            <div>
                                <label class="font-15">Paste Data</label>
                                <label class="pull-right">
                                    <span class="indeterm_check__wrap">
                                        <span class="indeterm_check" @click="paste_settings.f_header = !paste_settings.f_header">
                                            <i v-if="paste_settings.f_header" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <span>First row as header</span>
                                </label>
                            </div>
                            <div class="flex__elem-remain">
                                <textarea class="form-control full-height" v-model="paste_data" @paste="onPaste()"></textarea>
                                <textarea class="form-control full-height" v-model="paste_data"></textarea>
                            </div>
                            <div class="popup-buttons">
                                <button class="btn btn-info btn-sm pull-right" @click="$emit('popup-close')">Cancel</button>
                                <button class="btn btn-success btn-sm pull-right" @click="getFieldsFromPaste()">Next</button>
                            </div>
                        </div>

                        <div v-else="" class="flex flex--col">
                            <div>
                                <label class="font-15">Set the Column Correspondences</label>
                            </div>
                            <div class="full-frame">
                                <table class="table">
                                    <tr>
                                        <th>#</th>
                                        <th>Table Column</th>
                                        <th>Source Column</th>
                                    </tr>
                                    <tr v-for="(hdr,i) in tableHeaders">
                                        <td>{{ i++ }}</td>
                                        <td>{{ $root.uniqName(hdr.name) }}</td>
                                        <td>
                                            <select class="form-control" v-model="hdr.col">
                                                <option value=""></option>
                                                <option v-for="(fld,key) in fieldsColumns" :value="key">{{ fld }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="popup-buttons">
                                <button class="btn btn-info btn-sm pull-right" @click="$emit('popup-close')">Cancel</button>
                                <button class="btn btn-success btn-sm pull-right" @click="sendDirectImport()">Complete</button>
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

    import PasteAutowrapperMixin from './../_Mixins/PasteAutowrapperMixin.vue';
    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin.vue';

    export default {
        name: "ParseAndPastePopup",
        mixins: [
            PopupAnimationMixin,
            PasteAutowrapperMixin,
        ],
        components: {
        },
        data: function () {
            return {
                step2: false,
                //columns
                tableHeaders: [],
                fieldsColumns: [],
                //PopupAnimationMixin
                getPopupWidth: window.innerWidth*0.6,
                idx: 0,
            };
        },
        computed: {
        },
        props:{
            tableMeta: Object,
            availFields: Array,
            replaceValues: Object,
        },
        methods: {
            getFieldsFromPaste() {
                if (this.paste_data) {
                    this.pasteFieldsFromBackend().then((data) => {
                        _.each(data.fields, (elem, i) => {
                            if (this.tableHeaders[i]) {
                                this.tableHeaders[i].col = i;
                            }
                        });
                        this.fieldsColumns = data.fields;
                        this.step2 = true;
                    });
                } else {
                    Swal('No pasted data', '', 'info');
                }
            },
            sendDirectImport() {
                this.$root.sm_msg_type = 2;
                axios.post('/ajax/import/direct-call', {
                    table_id: this.tableMeta.id,
                    columns: this.tableHeaders,
                    import_type: 'paste',
                    paste_settings: this.paste_settings,
                    paste_file: this.paste_file,
                }).then(({ data }) => {
                    this.$emit('paste-completed');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        mounted() {
            this.paste_settings.replace_values = this.replaceValues;

            this.runAnimation();

            let fields = _.filter(this.tableMeta._fields, (el) => {
                return !this.availFields
                    || this.availFields.indexOf(el.field) > -1;
            });
            this.tableHeaders = _.map(fields, (el) => {
                return {
                    field: el.field,
                    name: el.name,
                    col: '',
                    f_type: el.f_type,
                    f_size: el.f_size,
                    f_default: el.f_default,
                }
            });
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";
    @import "./../CustomTable/Table";

    .popup {
        /*max-height: 450px;*/

        .font-15 {
            font-size: 1.5em;
        }

        label {
            margin: 0;
        }

        select {
            padding: 3px 6px;
            height: 26px;
        }
    }
</style>