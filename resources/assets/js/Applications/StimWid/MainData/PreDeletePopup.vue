<template>
    <div>
        <div class="popup-wrapper" @click.self="$emit('popup-close')"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Delete: {{ master_str || 'Master Row' }}</div>
                        <div class="" style="padding-bottom: 4px;">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close')"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main flex flex--col">
                        <div v-if="add_tables && add_tables.length" class="flex__elem-remain">
                            <div class="flex__elem__inner">
                                <div class="flex flex--col full-height">
                                    <h2 class="hh2">Tables inheriting fields from this table. Check those to be deleted:</h2>
                                    <div class="flex__elem-remain tbs_wrp">
                                        <div>
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="toggleAll()">
                                                    <i v-if="allChecked == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                                    <i v-if="allChecked == 1" class="glyphicon glyphicon-minus group__icon"></i>
                                                </span>
                                            </span>
                                            <label>{{ master_str }} (master)</label>
                                        </div>
                                        <div v-for="obj in add_tables" class="chck_item">
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="obj.to_del = !obj.to_del">
                                                    <i v-if="obj.to_del" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <label>{{ getTname(obj) }}</label>
                                        </div>
                                    </div>
                                    <h2 class="hh2">Note: data(records) in tables referred by but not inheriting any field values from the master table would not be deleted.</h2>
                                </div>
                            </div>
                        </div>
                        <h1 class="hh1">Data deleted cannot be recovered. Confirm to proceed.</h1>
                        <div class="popup-buttons">
                            <button class="btn btn-default pull-right" @click="$emit('popup-close')">Cancel</button>
                            <button class="btn btn-danger pull-right" @click="$emit('popup-delete')">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from './../../../components/_Mixins/PopupAnimationMixin';

    export default {
        name: 'PreDeletePopup',
        mixins: [
            PopupAnimationMixin,
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
            allChecked() {
                let check = _.find(this.add_tables, {to_del: true});
                let uncheck = _.find(this.add_tables, {to_del: false});
                return check && uncheck ? 1 : (check ? 2 : 0);
            },
            getPopupHeight() {
                return this.add_tables && this.add_tables.length ? '400px' : '150px';
            },
        },
        props: {
            master_str: String,
            add_tables: Array,
        },
        methods: {
            getTname(obj) {
                if (obj.stim) {
                    return obj.stim.horizontal + (obj.stim.vertical ? '/'+obj.stim.vertical : '');
                } else {
                    return obj.table;
                }
            },
            toggleAll() {
                let stat = !this.allChecked;
                _.each(this.add_tables, (el) => {
                    el.to_del = stat;
                });
            },
        },
        mounted() {
            this.runAnimation({anim_transform:'none'});
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
        margin: 0;
        font-size: 2.00rem;
        font-weight: bold;
    }
    .hh2 {
        font-size: 0.9em;
        margin: 10px 0;
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