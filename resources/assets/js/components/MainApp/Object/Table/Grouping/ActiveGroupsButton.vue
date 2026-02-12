<template>
    <div ref="group_btn_wrapper" class="active_groups_wrap flex flex--center-v flex--space">

        <button class="btn btn-default blue-gradient flex flex--center"
                @click="menu_opened = !menu_opened"
                :style="$root.themeButtonStyle"
        >
            <i class="glyphicon glyphicon-cog"></i>
        </button>

        <div v-show="menu_opened"
             class="active_groups_menu"
             title="Operations on grouping levels"
        >
            <table style="background: transparent; font-size: 1.2em;">
                <tr v-for="(lvl, idx) in selectedGrouping._settings">
                    <td class="txt--center" v-if="tableMeta._is_owner" style="width: 20px;">
                        <i v-if="idx > 0" class="fa fa-arrow-up pointer" @click="updateOrder(idx)"></i>
                    </td>
                    <td style="font-weight: bold;">{{ getLvlName(lvl) }}</td>
                    <td>
                        <label class="switch_t no-margin" style="height: 17px; margin-left: 5px !important;">
                            <input type="checkbox"
                                   v-model="lvl.rg_active"
                                   @click="updateStatus(lvl)">
                            <span class="toggler round"></span>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</template>

<script>

import {eventBus} from "../../../../../app";

export default {
    components: {
    },
    name: "ActiveGroupsButton",
    mixins: [
    ],
    data: function () {
        return {
            menu_opened: false,
        }
    },
    props: {
        tableMeta: Object,
        selectedGrouping: Object,
    },
    computed: {
    },
    methods: {
        toggleMenu() {
            this.menu_opened = false;
        },
        hideMenu(e) {
            let container = $(this.$refs.group_btn_wrapper);
            if (container.has(e.target).length === 0 && this.menu_opened) {
                this.toggleMenu();
            }
        },
        getLvlName(lvl) {
            let fld = _.find(this.tableMeta._fields, {id: Number(lvl.field_id)});
            return fld ? fld.name : lvl.field_id;
        },
        updateStatus(lvl) {
            lvl.rg_active = lvl.rg_active ? 0 : 1;
            if (this.tableMeta._is_owner) {
                eventBus.$emit('grouping-settings__update-grouping-level', lvl);//handle-redraw will be triggered here
            } else {
                this.$emit('handle-redraw');
            }
        },
        updateOrder(idx) {
            let lvlFrom = this.selectedGrouping._settings[idx];
            let lvlTo = this.selectedGrouping._settings[idx-1];

            $.LoadingOverlay('show');
            axios.put('/ajax/settings/change-row-order', {
                table_id: this.$root.settingsMeta['table_grouping_fields'].id,
                from_order: lvlFrom.row_order,
                to_order: lvlTo.row_order,
                from_id: lvlFrom.id,
                to_id: lvlTo.id,
            }).then(({ data }) => {
                eventBus.$emit('grouping-settings__reorder-grouping-level');
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
    },
    created() {
        eventBus.$on('global-click', this.hideMenu);
        eventBus.$on('global-keydown', this.hideMenu);
    },
    beforeDestroy() {
        eventBus.$off('global-click', this.hideMenu);
        eventBus.$off('global-keydown', this.hideMenu);
    }
}
</script>

<style lang="scss" scoped>
.active_groups_wrap {
    margin-right: 5px;
    background-color: transparent;
    position: relative;

    button {
        cursor: pointer;
        height: 30px;
        width: 35px;
        font-size: 22px;
        padding: 0;
        outline: none;

        i {
            padding: 0 !important;
        }
    }
}

.active_groups_menu {
    position: absolute;
    right: 100%;
    top: 100%;
    width: max-content;
    z-index: 1500;
    background: linear-gradient(to bottom, #efeff4, #d6dadf);
    padding: 5px;
    border: 1px solid #CCC;
    border-radius: 5px;
}
</style>