<template>
    <div class="select-wrapper border-radius--top" ref="select_wrapper" @click.stop="">
        <div class="select-element" @click="opened = !opened">
            <div class="element-value">{{ curVal() }}</div>
            <div class="element-triangle">
                <b :class="[opened ? 'b-opened' : '']"></b>
            </div>
        </div>

        <div v-if="opened" class="select-results border-radius--bottom" :style="ItemsListStyle()">
            <div class="filter-wrapper">
                <input class="form-control" v-model="search_text" @keyup="searchUsersInGroupsDelay()" placeholder="Search"/>
                <!--<span class="input-helper" v-if="search_text.length < 3">Min 3 characters for searching...</span>-->
                <span class="input-helper" v-if="searching_process">Searching...</span>
                <span class="input-helper" v-else-if="users_in_groups.length === 0">Results not found</span>
            </div>

            <div class="result-wrapper">
                <div v-if="can_empty" class="result-item" @click="selectedItem( '' )">&nbsp;</div>
                <div v-if="extra_vals.indexOf('visitor') > -1" class="result-item" @click="selectedItem( '{$visitor}' )">{$visitor}</div>
                <div v-if="extra_vals.indexOf('user') > -1" class="result-item" @click="selectedItem( '{$user}' )">{$user}</div>
                <div v-if="extra_vals.indexOf('group') > -1" class="result-item" @click="selectedItem( '{$group}' )">{$group}</div>

                <div v-for="group in users_in_groups">
                    <div>
                        <span class="group-opener" @click="group.opened = !group.opened">{{ group.opened ? '-' : '+' }}</span>
                        <div class="result-item result-item--inline"
                             :class="[is_selected(group.id) ? 'result-item--selected' : '']"
                             :style="{fontWeight: group.found ? 'bold' : 'normal'}"
                             @click="selectedItem(group.id)"
                        >{{ group.name || '&nbsp;' }}</div>
                    </div>
                    <div v-if="group.opened">
                        <div v-for="user in group._users"
                             class="result-item result-item--space"
                             :class="[is_selected(user.id) ? 'result-item--selected' : '']"
                             :style="{fontWeight: user.found ? 'bold' : 'normal'}"
                             @click="selectedItem(user.id)"
                        >{{ user.name || '&nbsp;' }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import MixinSmartPosition from '../../_Mixins/MixinSmartPosition.vue';

    export default {
        name: "TabldaUserSelect",
        components: {
        },
        mixins: [
            MixinSmartPosition
        ],
        data: function () {
            return {
                search_text: '',
                users_in_groups: [],
                u_search_timeout: null,
                searching_process: false,
            }
        },
        props:{
            edit_value: Array|String|Number,
            show_selected: Boolean,
            table_meta: Object,
            can_empty: Boolean,
            fixed_pos: Boolean,
            multiselect: Boolean,
            extra_vals: {
                type: Array,
                default() { return []; },
            },
        },
        computed: {
        },
        methods: {
            //check
            is_selected(val) {
                if (Array.isArray(this.edit_value)) {
                    return in_array(String(val), this.edit_value);
                } else {
                    return this.edit_value == val;
                }
            },
            curVal() {
                if (!this.show_selected) {
                    return '';
                }
                if (Array.isArray(this.edit_value)) {
                    return _.map(this.edit_value, (el) => {
                        return this.findElem(el);
                    }).join(', ');
                } else {
                    return this.findElem(this.edit_value);
                }
            },
            findElem(el) {
                let result = '';
                _.each(this.users_in_groups, (group) => {
                    if (group.id == el) {
                        result = group.name || '&nbsp;';
                    }
                    _.each(group._users, (user) => {
                        if (user.id == el) {
                            result = user.name || '&nbsp;';
                        }
                    });
                });
                return result;
            },

            //Standard DDL
            selectedItem(key) {
                this.$emit('selected-item', String(key));
                if (!this.multiselect) {
                    this.hideSelect();
                }
            },

            //search Users in groups DDL
            searchUsersInGroupsDelay() {
                window.clearTimeout(this.u_search_timeout);
                this.u_search_timeout = window.setTimeout(this.searchUsersInGroups, 500);
                this.searching_process = true;
                /*if (this.search_text.length >= 3) {
                    this.u_search_timeout = window.setTimeout(this.searchUsersInGroups, 500);
                    this.searching_process = true;
                } else {
                    this.users_in_groups = [];
                    this.searching_process = false;
                }*/
            },
            searchUsersInGroups() {
                axios.get('/ajax/user/search-in-groups', {
                    params: {
                        table_id: this.table_meta.id,
                        q: this.search_text,
                    }
                }).then(({ data }) => {
                    _.each(data, (gr) => {
                        if (Array.isArray(this.edit_value)) {
                            gr.opened = _.find(gr._users, (usr) => { return in_array(String(usr.id), this.edit_value); });
                        } else {
                            gr.opened = _.find(gr._users, {id: Number(this.edit_value)});
                        }
                    });
                    this.users_in_groups = data;
                    this.searching_process = false;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
        },
        mounted() {
            this.searchUsersInGroups();
            this.showItemsList();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabldaSelect";

    .select-wrapper {
        min-width: 250px;
    }
</style>