<template>
    <div>
        <div class="popup-wrapper" @click.self="$emit('popup-close')"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Copy a master w/ child tables</div>
                        <div class="" style="padding-bottom: 4px;">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close')"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content" :class="{'flex__elem-remain': !copy_success_message}">
                    <div v-if="copy_success_message" class="popup-main">
                        <div style="font-size: 16px;">Copying present (Master) table with selected child tables (if any) is completed</div>
                        <div style="font-size: 16px;">The copied tables have a "{{ copy_success_message }}"
                            {{ copy_success_message === 'copy_' ? 'prefix' : 'suffix' }}
                            added to the values for their identification fields.</div>
                    </div>
                    <div v-else class="flex__elem__inner popup-main flex flex--col">
                        <div class="flex__elem-remain">
                            <div class="flex flex--col">
                                <div class="flex flex--center-v form-group no-wrap">
                                    <label class="no-margin">Copy to:&nbsp;</label>
                                    <select class="form-control cpt_sel" v-model="type" style="width: 40%">
                                        <option value="self">Self</option>
                                        <option value="user">Another User</option>
                                    </select>
                                    <select :disabled="type === 'self'" ref="search_user" class="form-control"/>
                                </div>
                                <div class="">If “no results found” for an entered email address for searching,
                                    it may due to that it is not registered with TablDA or does not have access to the STIM module/tables.</div>
                                <h2 class="hh2">Child tables inheriting field(s) from this Master table. Check those to copy the related records:</h2>
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
                                    <div v-for="obj in cp_tables" class="chck_item">
                                        <span class="indeterm_check__wrap">
                                            <span class="indeterm_check" @click="obj.to_copy = !obj.to_copy">
                                                <i v-if="obj.to_copy" class="glyphicon glyphicon-ok group__icon"></i>
                                            </span>
                                        </span>
                                        <label>{{ getTname(obj) }}</label>
                                    </div>
                                </div>
                                <h2 class="hh2">Note: data(records) in tables referred by but not inheriting any field
                                    values from the master table would not be copied.</h2>
                            </div>
                        </div>
                        <div class="popup-buttons">
                            <button class="btn btn-default pull-right" @click="$emit('popup-close')">Cancel</button>
                            <button class="btn btn-success pull-right" @click="goClick">Copy</button>
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
        name: 'CopyMasterPopup',
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
        },
        data() {
            return {
                type: 'self',
                user_id: null,
                //PopupAnimationMixin
                getPopupWidth: 600,
                idx: 0,
            }
        },
        computed: {
            allChecked() {
                let check = _.find(this.cp_tables, {to_copy: true});
                let uncheck = _.find(this.cp_tables, {to_copy: false});
                return check && uncheck ? 1 : (check ? 2 : 0);
            },
            getPopupHeight() {
                return this.copy_success_message ? 'auto' : '500px';
            }
        },
        props: {
            master_str: String,
            cp_tables: Array,
            table_id: Number,
            copy_success_message: String,
        },
        methods: {
            createSearchUser() {
                if (this.$refs.search_user && $(this.$refs.search_user).hasClass('select2-hidden-accessible')) {
                    $(this.$refs.search_user).select2('destroy');
                }
                this.$nextTick(() => {
                    $(this.$refs.search_user).select2({
                        ajax: {
                            url: '/ajax/user/search',
                            dataType: 'json',
                            delay: 250,
                            data: (params) => {
                                return {
                                    q: params.term,
                                    extras: { show_field: 'email' },
                                    table_id: this.table_id
                                }
                            },
                        },
                        width: '100%',
                        dropdownAutoWidth: true,
                        minimumInputLength: {val:3},
                    });
                    $(this.$refs.search_user).next().css('height', '30px');
                });
            },
            goClick() {
                let uid = null;
                if (this.type !== 'self') {
                    uid = $(this.$refs.search_user).val();
                }
                this.$emit('popup-copy', uid);
            },
            getTname(obj) {
                if (obj.stim) {
                    return obj.stim.horizontal_lvl1 + (obj.stim.vertical_lvl1 ? '/'+obj.stim.vertical_lvl1 : '')
                        + (obj.stim.horizontal_lvl2 ? '/'+obj.stim.horizontal_lvl2 : '')
                        + (obj.stim.vertical_lvl2 ? '/'+obj.stim.vertical_lvl2 : '');
                } else {
                    return obj.table;
                }
            },
            toggleAll() {
                let stat = !this.allChecked;
                _.each(this.cp_tables, (el) => {
                    el.to_copy = stat;
                });
            },
        },
        mounted() {
            this.runAnimation({anim_transform:'none'});
            this.createSearchUser();
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
    .hh2 {
        font-size: 1em;
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
    .cpt_sel {
        height: 30px;
        padding: 3px 6px;
        margin-right: 5px;
    }
</style>