<template>
    <table class="table-others">
        <tr>
            <td :style="{width: addon_widths.title, cursor: (in_array(selAddon.code, ['bi','alert']) ? 'pointer' : null)}"
                @click="tglAddonRights(selAddon)"
            >
                <span>{{ selAddon.name }}</span>
                <span v-if="in_array(selAddon.code, ['bi','alert'])">({{ (!showAddonRight[selAddon.code] ? '+' : '-') }})</span>
            </td>
            <td :style="{width: addon_widths.view}">
                <label>
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="toggleAddonRight('view')">
                            <i v-if="findAddonRight('view')" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <span>&nbsp;{{ (in_array(selAddon.code, ['bi']) ? 'Available' : 'View') }}</span>
                </label>
            </td>
            <td :style="{width: addon_widths.edit}">
                <label v-if="selAddon.code !== 'request'"><!-- collaborator cannot edit DCR -->
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="toggleAddonRight('edit')">
                            <i v-if="findAddonRight('edit')" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <span>&nbsp;Edit</span>
                </label>
            </td>
            <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
        </tr>
        <template v-if="in_array(selAddon.code, ['bi']) && showAddonRight[selAddon.code]">
            <tr v-if="findAddonRight('edit')">
                <td :colspan="addon_widths.activate ? 4 : 3">
                    <div class="flex">
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(selAddon, !findAddonRight(1, 'lockout_layout'), 'lockout_layout')">
                                    <i v-if="findAddonRight(1, 'lockout_layout')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Lockout Layout&nbsp;&nbsp;&nbsp;</span>
                        </label>
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(selAddon, !findAddonRight(1, 'add_new'), 'add_new')">
                                    <i v-if="findAddonRight(1, 'add_new')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Add New&nbsp;&nbsp;&nbsp;</span>
                        </label>
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(selAddon, !findAddonRight(1, 'hide_settings'), 'hide_settings')">
                                    <i v-if="findAddonRight(1, 'hide_settings')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Hide Settings&nbsp;&nbsp;&nbsp;</span>
                        </label>
                    </div>
                    <div class="flex">
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(selAddon, !findAddonRight(1, 'block_spacing'), 'block_spacing')">
                                    <i v-if="findAddonRight(1, 'block_spacing')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Block Spacing&nbsp;&nbsp;&nbsp;</span>
                        </label>
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(selAddon, !findAddonRight(1, 'vert_grid_step'), 'vert_grid_step')">
                                    <i v-if="findAddonRight(1, 'vert_grid_step')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Block Vertical Grid Step</span>
                        </label>
                    </div>
                </td>
            </tr>
            <tr v-for="chart in sortedCharts">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ getChartTitle(chart) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleChartRight(chart, 'view')">
                                <i v-if="findChartRight(chart, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleChartRight(chart, 'edit')">
                                <i v-if="findChartRight(chart, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['alert']) && showAddonRight[selAddon.code]">
            <tr v-for="alert in tableMeta._alerts">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ getAlertTitle(alert) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAlertRight(alert, 'view')">
                                <i v-if="findAlertRight(alert)" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAlertRight(alert, 'edit')">
                                <i v-if="findAlertRight(alert, 'can_edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAlertRight(alert, 'activate')">
                                <i v-if="findAlertRight(alert, 'can_activate')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Activate</span>
                    </label>
                </td>
            </tr>
        </template>
    </table>
</template>

<script>
    import {eventBus} from './../../../../../app';

    export default {
        name: "TabSettingsPermissionsBasicsAddon",
        components: {
        },
        data: function () {
            return {
                showAddonRight: {},
                addon_widths: {
                    title: '46%',
                    view: this.selAddon.code === 'alert' ? '18%' : '27%',
                    edit: this.selAddon.code === 'alert' ? '18%' : '27%',
                    activate: this.selAddon.code === 'alert' ? '18%' : '',
                },
            }
        },
        props:{
            tableMeta: Object,
            selAddon: Object,
            selPermission: Object,
        },
        computed: {
            sortedCharts() {
                return _.sortBy(this.tableMeta._charts, ['row_idx', 'col_idx'])
            },
        },
        methods: {
            in_array(key, arr) {
                return window.in_array(key, arr);
            },
            tglAddonRights() {
                this.showAddonRight[this.selAddon.code] = !this.showAddonRight[this.selAddon.code] && this.findAddonRight('view');
            },

            //Addons
            findAddonRight(value, field) {
                field = field || 'type';
                let present = false;
                _.each(this.selPermission._addons, (element) => {
                    if (element.id === this.selAddon.id && element._link[field] == value) {
                        present = true;
                    }
                });
                return present;
            },
            updateAddonRight(value, field) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table-permission/addon-right', {
                    addon_id: this.selAddon.id,
                    table_permission_id: this.selPermission.id,
                    fld: field,
                    val: value,
                }).then(({ data }) => {
                    this.selPermission._addons = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            toggleAddonRight(type) {
                this.$root.sm_msg_type = 1;
                if (this.findAddonRight(type)) {
                    axios.delete('/ajax/table-permission/addon-right', {
                        params: {
                            addon_id: this.selAddon.id,
                            table_permission_id: this.selPermission.id,
                            type: type
                        }
                    }).then(({ data }) => {
                        this.selPermission._addons = data;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    axios.post('/ajax/table-permission/addon-right', {
                        addon_id: this.selAddon.id,
                        table_permission_id: this.selPermission.id,
                        type: type
                    }).then(({ data }) => {
                        this.selPermission._addons = data;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            //Addon: Charts
            findChartRight(chart, type) {
                let right = _.find(chart._chart_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleChartRight(chart, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(chart._chart_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? chart._chart_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/table/chart/right', {
                        params: {
                            chart_id: chart.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        chart._chart_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/table/chart/right', {
                        chart_id: chart.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            chart._chart_rights.splice(rightIdx, 1 ,data);
                        } else {
                            chart._chart_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            getChartTitle(chart) {
                let pos = '(' + (Number(chart.row_idx)+1) + ',' + (Number(chart.col_idx)+1) + ')';
                return (chart.name || pos) + ' ' + chart.title;
            },

            //Addon: Alerts
            findAlertRight(alert, type) {
                let right = _.find(alert._alert_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type ? right && right[type] : right);
            },
            toggleAlertRight(alert, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(alert._alert_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? alert._alert_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/table/alert/right', {
                        params: {
                            alert_id: alert.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                            can_activate: 0,//not used
                        }
                    }).then(({ data }) => {
                        alert._alert_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    let canactivate = Number(right && right.can_activate);
                    axios.post('/ajax/table/alert/right', {
                        alert_id: alert.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                        can_activate: (type === 'activate') ? Number(!canactivate) : canactivate,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            alert._alert_rights.splice(rightIdx, 1 ,data);
                        } else {
                            alert._alert_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            getAlertTitle(alert) {
                return alert.name;
            },
        },
        mounted() {
            let obj = {};
            _.each(this.$root.settingsMeta.all_addons, (adn) => {
                obj[adn.code] = false;
            });
            this.showAddonRight = obj;
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabSettingsPermissions";
</style>