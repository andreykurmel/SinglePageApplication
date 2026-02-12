    <template>
        <tr>
            <!--CHART-->
            <template v-if="type === 'chart'">
                <td @click="editVar('name')">
                    <input v-if="edit_column === 'name'"
                           ref="edit_name"
                           type="text"
                           class="form-control"
                           v-model="variable_obj.name"
                           @change="changedVar()"
                           @blur="hideVar()"/>
                    <span v-else="">{{ variable_obj.name }}</span>
                </td>
                <td @click="editVar('chart_id')">
                    <select v-if="edit_column === 'chart_id'"
                            ref="edit_chart_id"
                            class="form-control"
                            v-model="variable_obj.chart_id"
                            @change="changedVar()"
                            @blur="hideVar()">
                        <option v-for="ch in tableMeta._bi_charts" v-if="ch.chart_settings.elem_type === 'bi_chart'" :value="ch.id">{{ ch.title }}</option>
                    </select>
                    <span v-else="">{{ linked_chart ? linked_chart.title : '' }}</span>
                </td>
                <td @click="editVar('group_1')">
                    <select v-if="edit_column === 'group_1'"
                            ref="edit_group_1"
                            class="form-control"
                            v-model="variable_obj.group_1"
                            @change="changedVar()"
                            @blur="hideVar()">
                        <option v-for="(none, key) in get_vals('chart', 'x')" :value="key">{{ key }}</option>
                    </select>
                    <span v-else="">{{ variable_obj.group_1 }}</span>
                </td>
                <td @click="editVar('group_2')">
                    <select v-if="edit_column === 'group_2'"
                            ref="edit_group_2"
                            class="form-control"
                            v-model="variable_obj.group_2"
                            @change="changedVar()"
                            @blur="hideVar()">
                        <option v-for="(none, key) in get_vals('chart', 'l1')" :value="key">{{ key }}</option>
                    </select>
                    <span v-else="">{{ variable_obj.group_2 }}</span>
                </td>
                <td @click="editVar('group_3')">
                    <select v-if="edit_column === 'group_3'"
                            ref="edit_group_3"
                            class="form-control"
                            v-model="variable_obj.group_3"
                            @change="changedVar()"
                            @blur="hideVar()">
                        <option v-for="(none, key) in get_vals('chart', 'l2')" :value="key">{{ key }}</option>
                    </select>
                    <span v-else="">{{ variable_obj.group_3 }}</span>
                </td>
                <td>
                    <span v-if="is_add">(Auto)</span>
                    <span v-else="">{{ cha_about() }}</span>
                </td>
                <td>
                    <button v-if="is_add" class="btn btn-success btn-sm" @click="$emit('add-signal')">Add</button>
                    <button v-else="" class="btn btn-danger btn-sm" @click="$emit('delete-signal')">Del</button>
                </td>
            </template>

            <!--TABLE-->
            <template v-else="">
                <td @click="editVar('name')">
                    <input v-if="edit_column === 'name'"
                           ref="edit_name"
                           type="text"
                           class="form-control"
                           v-model="variable_obj.name"
                           @change="changedVar()"
                           @blur="hideVar()"/>
                    <span v-else="">{{ variable_obj.name }}</span>
                </td>
                <td @click="editVar('chart_id')">
                    <select v-if="edit_column === 'chart_id'"
                            ref="edit_chart_id"
                            class="form-control"
                            v-model="variable_obj.chart_id"
                            @change="changedVar()"
                            @blur="hideVar()">
                        <option v-for="ch in tableMeta._bi_charts" v-if="ch.chart_settings.elem_type === 'pivot_table'" :value="ch.id">{{ ch.title }}</option>
                    </select>
                    <span v-else="">{{ linked_chart ? linked_chart.title : '' }}</span>
                </td>
                <td style="padding: 0;">
                    <table class="table_settings" v-if="linked_chart">
                        <colgroup><col style="width: 100px;"><col style="width: 200px;"></colgroup>
                        <tbody>
                        <tr v-for="ri in Number(linked_chart.chart_settings.pivot_table.hor_l)">
                            <td>{{ 'Row L'+ri }}</td>
                            <td @click="editVar('hor_'+ri)">
                                <select v-if="edit_column === 'hor_'+ri"
                                        :ref="'edit_hor_'+ri"
                                        class="form-control"
                                        v-model="variable_obj['hor_'+ri]"
                                        @change="changedVar()"
                                        @blur="hideVar()">
                                    <option v-for="(none, key) in get_vals('table', 'hor_l'+ri)" :value="key">{{ key }}</option>
                                </select>
                                <span v-else="">{{ variable_obj['hor_'+ri] }}</span>
                            </td>
                        </tr>
                        <tr v-for="ci in Number(linked_chart.chart_settings.pivot_table.vert_l)">
                            <td>{{ 'Column L'+ci }}</td>
                            <td @click="editVar('vert_'+ci)">
                                <select v-if="edit_column === 'vert_'+ci"
                                        :ref="'edit_vert_'+ci"
                                        class="form-control"
                                        v-model="variable_obj['vert_'+ci]"
                                        @change="changedVar()"
                                        @blur="hideVar()">
                                    <option v-for="(none, key) in get_vals('table', 'vert_l'+ci)" :value="key">{{ key }}</option>
                                </select>
                                <span v-else="">{{ variable_obj['vert_'+ci] }}</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td @click="editVar('about')">
                    <select v-if="linked_chart && edit_column === 'about'"
                            ref="edit_about"
                            class="form-control"
                            v-model="variable_obj.about"
                            @change="changedVar()"
                            @blur="hideVar()">
                        <option v-if="linked_chart.chart_settings.pivot_table.about.field"
                                :value="1">{{ fldName(linked_chart.chart_settings.pivot_table.about.field) }}</option>
                        <option v-if="linked_chart.chart_settings.pivot_table.about_2.field"
                                :value="2">{{ fldName(linked_chart.chart_settings.pivot_table.about_2.field) }}</option>
                        <option v-if="linked_chart.chart_settings.pivot_table.about_3.field"
                                :value="3">{{ fldName(linked_chart.chart_settings.pivot_table.about_3.field) }}</option>
                    </select>
                    <template v-if="linked_chart && edit_column !== 'about'">
                        <span v-if="variable_obj.about == 3">{{ fldName(linked_chart.chart_settings.pivot_table.about_3.field) }}</span>
                        <span v-else-if="variable_obj.about == 2">{{ fldName(linked_chart.chart_settings.pivot_table.about_2.field) }}</span>
                        <span v-else="">{{ fldName(linked_chart.chart_settings.pivot_table.about.field) }}</span>
                    </template>
                </td>
                <td>
                    <button v-if="is_add" class="btn btn-success btn-sm" @click="$emit('add-signal')">Add</button>
                    <button v-else="" class="btn btn-danger btn-sm" @click="$emit('delete-signal')">Del</button>
                </td>
            </template>
        </tr>
    </template>

    <script>
        export default {
            name: "BiChartSettingsVariable",
            data: function () {
                return {
                    edit_column: '',
                }
            },
            props:{
                type: String,
                is_add: Boolean,
                tableMeta: Object,
                variable_obj: Object,
                linked_chart: Object,
            },
            computed: {
            },
            methods: {
                changedVar() {
                    this.$emit('change-signal');
                },
                hideVar() {
                    this.edit_column = '';
                },
                editVar(editer) {
                    this.edit_column = editer;
                    this.$nextTick(function () {
                        let el = Array.isArray(this.$refs['edit_'+editer]) ? _.first(this.$refs['edit_'+editer]) : this.$refs['edit_'+editer];
                        if (el) {
                            el.focus();
                        }
                    });
                },
                cha_about() {
                    return this.linked_chart
                        ? this.fldName(this.linked_chart.chart_settings.bi_chart.y_axis.field)
                        : '';
                },
                fldName(db_field) {
                    let fld = _.find(this.tableMeta._fields, {field: db_field});
                    return fld ? fld.name : db_field;
                },
                get_vals(type, key) {
                    let data = (type === 'chart' ? 'chart_data' : 'table_data');
                    return this.linked_chart && this.linked_chart.cached_data
                        ? _.groupBy(this.linked_chart.cached_data[data], key)
                        : [];
                },
            },
            created() {
            },
            mounted() {
            },
            beforeDestroy() {
            }
        }
    </script>

    <style lang="scss" scoped>
        tr {
            td {
                vertical-align: middle;
                text-align: center;
                border: 1px solid #777;
                padding: 2px 5px;

                select, input {
                    padding: 3px;
                    height: 28px;
                }
            }
        }
    </style>