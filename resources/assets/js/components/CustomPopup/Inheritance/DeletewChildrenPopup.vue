<template>
    <div>
        <div class="popup-wrapper" @click.self="$emit('popup-close')"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Delete a master w/ child tables</div>
                        <div class="" style="padding-bottom: 4px;">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close')"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main flex flex--col">
                        <div class="flex__elem-remain">
                            <div class="flex__elem__inner">
                                <div class="flex flex--col full-height">
                                    <h2 class="hh2">Child tables inheriting fields from this Master table. Check those to be deleted:</h2>
                                    <div class="flex__elem-remain tbs_wrp">
                                        <div v-for="(obj1) in inherited_tree" class="chck_item">
                                        <span class="indeterm_check__wrap">
                                            <span class="indeterm_check" @click="objChecked(obj1)">
                                                <i v-if="obj1.checked" class="glyphicon glyphicon-ok group__icon"></i>
                                            </span>
                                        </span>
                                            <label>{{ obj1.name }}</label>

                                            <div v-for="(obj2) in obj1.children" class="chck_item">
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="objChecked(obj2)">
                                                    <i v-if="obj2.checked" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                                <label>{{ obj2.name }}</label>


                                                <div v-for="(obj3) in obj2.children" class="chck_item">
                                                <span class="indeterm_check__wrap">
                                                    <span class="indeterm_check" @click="objChecked(obj3)">
                                                        <i v-if="obj3.checked" class="glyphicon glyphicon-ok group__icon"></i>
                                                    </span>
                                                </span>
                                                    <label>{{ obj3.name }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h2 class="hh2">Note: data(records) in tables referred by but not inheriting any field values from the master table would not be deleted.</h2>
                                    <h2 class="hh2 red">Maximum 3 level of hierarchy is checked. Further levels are ignored</h2>
                                </div>
                            </div>
                        </div>
                        <h1 class="hh1 red">Data deleted cannot be recovered. Confirm to proceed.</h1>
                        <div class="popup-buttons">
                            <button class="btn btn-default pull-right" @click="$emit('popup-close')">Cancel</button>
                            <button class="btn btn-danger pull-right" @click="goClick">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from './../../../components/_Mixins/PopupAnimationMixin';
    import InheritanceMixin from './InheritanceMixin';

    import {Endpoints} from "../../../classes/Endpoints";

    export default {
        name: 'DeletewChildrenPopup',
        mixins: [
            PopupAnimationMixin,
            InheritanceMixin,
        ],
        components: {
        },
        data() {
            return {
                //PopupAnimationMixin
                getPopupWidth: 600,
                idx: 0,
            }
        },
        computed: {
            getPopupHeight() {
                return '500px';
            },
        },
        props: {
            master_table: Object,
            request_params: Object,
            all_rows: Array,
            direct_row: Object,
        },
        methods: {
            goClick() {
                let check_obj = null;
                if (this.direct_row) {
                    check_obj = {
                        rows_ids: [this.direct_row.id],
                        all_checked: false,
                    };
                } else {
                    check_obj = this.$root.checkedRowObject(this.all_rows);
                    //avail if all_rows are more than 1 page.
                    check_obj.all_checked = this.all_rows.length >= this.master_table.rows_per_page ? check_obj.all_checked : false;
                }

                let request_params = _.cloneDeep(this.request_params);
                request_params.page = 1;
                request_params.rows_per_page = 0;

                if (check_obj.rows_ids || check_obj.all_checked) {
                    $.LoadingOverlay('show');
                    Endpoints.massDeleteRows(
                        this.master_table.id,
                        (check_obj.all_checked ? null : check_obj.rows_ids),
                        (check_obj.all_checked ? request_params : null),
                        this.noInheritanceIds(),
                    ).then((data) => {
                        this.$emit('after-delete', check_obj.all_checked);
                    }).finally(() => $.LoadingOverlay('hide'));
                } else {
                    Swal('Info','No record selected!');
                }
            },
        },
        mounted() {
            this.runAnimation({anim_transform:'none'});
            this.loadInheritTree();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../../../components/CustomPopup/CustomEditPopUp";

    .popup-main {
        padding: 10px 20px;
    }
    .hh1 {
        margin: 10px 0;
        font-size: 16px;
        font-weight: bold;
    }
    .hh2 {
        font-size: 14px;
        margin: 10px 0;
        font-weight: bold;
    }
    .tbs_wrp {
        border: 1px solid #DDD;
        padding: 3px;
        border-radius: 5px;
    }
    .chck_item {
        padding-left: 15px;
    }
</style>