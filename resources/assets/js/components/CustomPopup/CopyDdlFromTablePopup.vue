<template>
    <div>
        <div class="popup-wrapper" @click.self="closeP()"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Copy RC/DDL</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="closeP()"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content">
                    <div class="popup-main">
                        <div class="flex flex--col">
                            <div class="">
                                <div class="form-group">
                                    <label>Enter min. 3 characters to search a DDL</label>
                                    <div style="height: 30px; position: relative; z-index: 150000;">
                                        <tablda-select-simple
                                                :options="all_ddls"
                                                :table-row="searchWord"
                                                :hdr_field="'word'"
                                                :allowed_search="true"
                                                :init_no_open="true"
                                                @selected-item="searchDone"
                                        ></tablda-select-simple>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>OR:</label>
                                </div>
                                <div class="form-group">
                                    <label>Select a Table</label>
                                    <div style="height: 30px;">
                                        <select-with-folder-structure
                                                v-if="menu_ready"
                                                :cur_val="ref_table_id"
                                                :available_tables="$root.settingsMeta.available_tables"
                                                :user="$root.user"
                                                @sel-changed="selTB"
                                                class="form-control">
                                        </select-with-folder-structure>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Select a DDL</label>
                                    <select v-model="ref_ddl_id" v-if="refDDL" class="form-control" style="height: 30px;padding: 4px 8px;">
                                        <option></option>
                                        <option v-for="ddl in refDDL._ddls" :value="ddl.id">{{ $root.uniqName(ddl.name) }}</option>
                                    </select>
                                    <select v-else="" class="form-control" disabled="disabled"></select>
                                </div>
                            </div>
                            <div class="popup-buttons">
                                <button class="btn btn-success btn-sm"
                                        :disabled="!ref_table_id || !ref_ddl_id"
                                        @click="copyDDL()"
                                >Copy</button>
                                <button class="btn btn-info btn-sm ml5" @click="closeP()">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    import SelectWithFolderStructure from "../CustomCell/InCell/SelectWithFolderStructure";
    import TabldaSelectSimple from "../CustomCell/Selects/TabldaSelectSimple";

    export default {
        components: {
            TabldaSelectSimple,
            SelectWithFolderStructure,
        },
        name: "CopyDdlFromTablePopup",
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                menu_ready: true,
                searchWord: { word: '' },
                ref_table_id: null,
                ref_ddl_id: null,
                //PopupAnimationMixin
                getPopupWidth: 400,
                getPopupHeight: '377px',
                idx: 0,
            };
        },
        computed: {
            all_ddls() {
                let allddls = [];
                _.each(this.$root.settingsMeta.available_tables, (tb) => {
                    _.each(tb._ddls, (ddl) => {
                        allddls.push({
                            val: tb.id+'_'+ddl.id,
                            show:tb.name+'/'+ddl.name,
                        });
                    });
                });
                return allddls;
            },
            refDDL() {
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(this.ref_table_id)});
            },
        },
        props:{
            tableMeta: Object,
        },
        methods: {
            selTB(val) {
                this.ref_table_id = val;
            },
            copyDDL() {
                if (this.ref_table_id || this.ref_ddl_id) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/ddl/copy-from-table', {
                        target_table_id: this.tableMeta.id,
                        ref_table_id: this.ref_table_id,
                        ref_ddl_id: this.ref_ddl_id,
                    }).then(({data}) => {
                        this.tableMeta._ddls = data;
                        eventBus.$emit('reload-meta-table');
                        this.closeP();
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => $.LoadingOverlay('hide'));
                } else {
                    Swal('No DDL selected!');
                }
            },
            closeP() {
                this.$emit('popup-close');
            },
            searchDone(key, show) {
                let arr = key ? String(key).split('_') : null;
                if (arr && arr.length) {
                    this.menu_ready = false;
                    this.ref_table_id = Number(arr[0]);
                    this.ref_ddl_id = Number(arr[1]);
                    this.searchWord.word = '';
                    this.$nextTick(() => {
                        this.menu_ready = true;
                    });
                }
            },
        },
        mounted() {
            this.runAnimation();
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup {
        font-size: initial;
        cursor: auto;

        .popup-content {
            label {
                margin: 0;
            }

            .popup-buttons {
                text-align: right;
            }
        }
    }

    .ml5 {
        margin-left: 5px;
    }
</style>