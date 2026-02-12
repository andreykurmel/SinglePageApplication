<template>
    <div class="full-height">
        <div class="full-height permissions-tab p5" :style="$root.themeMainBgStyle">

            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">

                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="changeTab('list')">
                        List
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'general'}" @click="changeTab('general')">
                        General
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'specific'}" @click="changeTab('specific')">
                        Field Specific
                    </button>

                    <div v-if="selectedSimplemapSett" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">
                        <div class="flex flex--center-v ml15 mr0" v-show="activeTab !== 'list'">
                            <button class="btn btn-default btn-sm blue-gradient full-height"
                                    :style="$root.themeButtonStyle"
                                    @click="copySmpSett()"
                            >Copy</button>
                            <select class="form-control full-height" v-model="snp_for_copy" style="width: 150px">
                                <option v-for="smp in ActiveSimplemapFields"
                                        v-if="smp.id != selectedSimplemapSett.id"
                                        :value="smp.id"
                                >{{ smp.name }}</option>
                            </select>
                        </div>

                        <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                            Loaded Settings for Simplemap:&nbsp;
                            <select-block
                                :options="smpsettOpts()"
                                :sel_value="selectedSimplemapSett.id"
                                :style="{ width:'150px', height:'32px', }"
                                @option-select="smpsettChange"
                            ></select-block>
                        </label>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <div class="full-height permissions-panel no-padding" v-show="activeTab === 'list'" :style="$root.themeMainBgStyle">
                        <custom-table
                            :cell_component_name="'custom-cell-simplemap-sett'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_simplemaps']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="ActiveSimplemapFields"
                            :adding-row="addingRowFields"
                            :rows-count="ActiveSimplemapFields.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :selected-row="selectedCol"
                            :user="$root.user"
                            :behavior="'settings_simplemap_add'"
                            :available-columns="['name','map','level_fld_id','smp_active','tb_smp_data_range']"
                            :use_theme="true"
                            @added-row="addSimplemap"
                            @delete-row="deleteSimplemap"
                            @updated-row="updateSimplemap"
                            @row-index-clicked="rowIndexClickedColumn"
                        ></custom-table>
                    </div>

                    <div class="full-height permissions-panel no-padding overflow-auto" v-show="activeTab === 'general'" :style="$root.themeMainBgStyle">
                        <div class="simplemap_additionals" v-if="selectedSimplemapSett" :style="textSysStyleSmart">
                            <div class="form-group flex flex--center-v no-wrap">
                                <div class="cell_wi" :class="[valueIsDDLwithColor() ? 'wi5' : 'wi4']">
                                    <label>Theme value:&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.smp_value_fld_id"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option></option>
                                        <option
                                            v-for="header in tableMeta._fields"
                                            :value="header.id"
                                        >{{ header.name }}</option>
                                    </select>
                                </div>

                                <div v-if="valueIsDDLwithColor()" class="cell_wi wi5">
                                    <label>&nbsp;&nbsp;&nbsp;Apply option color:&nbsp;</label>
                                    <span class="indeterm_check__wrap" style="color: #555">
                                        <span
                                            class="indeterm_check"
                                            @click="optColorClicked()"
                                        >
                                            <i v-if="selectedSimplemapSett.smp_value_ddl_color" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                </div>

                                <div class="cell_wi" :class="[valueIsDDLwithColor() ? 'wi5' : 'wi4']">
                                    <label>&nbsp;&nbsp;&nbsp;Color:&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.smp_color_fld_id"
                                        @change="selectedSimplemapSett.smp_value_ddl_color = 0;updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option></option>
                                        <option
                                            v-for="header in tableMeta._fields"
                                            v-if="header.f_type === 'Color'"
                                            :value="header.id"
                                        >{{ header.name }}</option>
                                    </select>
                                </div>

                                <div class="cell_wi" :class="[valueIsDDLwithColor() ? 'wi5' : 'wi4']">
                                    <label>&nbsp;&nbsp;&nbsp;Description:&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.smp_on_hover_fld_id"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option></option>
                                        <option
                                            v-for="header in tableMeta._fields"
                                            v-if="$root.inArray(header.f_type, ['String','Text','Long Text'])"
                                            :value="header.id"
                                        >{{ header.name }}</option>
                                    </select>
                                </div>

                                <div class="cell_wi" :class="[valueIsDDLwithColor() ? 'wi5' : 'wi4']">
                                    <label>&nbsp;&nbsp;&nbsp;Active status:&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.smp_active_status_fld_id"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option></option>
                                        <option
                                            v-for="header in tableMeta._fields"
                                            v-if="$root.inArray(header.f_type, ['Boolean'])"
                                            :value="header.id"
                                        >{{ header.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group flex flex--center-v no-wrap">
                                <div class="cell_wi">
                                    <label>Legend orientation:&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.smp_legend_orientation"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option value="vertical">Vertical</option>
                                        <option value="horizontal">Horizontal</option>
                                    </select>
                                </div>

                                <div class="cell_wi">
                                    <label>&nbsp;&nbsp;&nbsp;Size:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    <input
                                        :style="textSysStyle"
                                        class="form-control"
                                        v-model="selectedSimplemapSett.smp_legend_size"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    />
                                    <label>px</label>
                                </div>
                            </div>
                            <div class="form-group flex flex--center-v no-wrap">
                                <div class="cell_wi">
                                    <label>On click:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.smp_theme_pop_style"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option value="simple_pop">Simple Popup</option>
                                        <option value="link_pop">Link Popup</option>
                                    </select>
                                </div>

                                <div class="cell_wi">
                                    <label>&nbsp;&nbsp;&nbsp;Linked data:&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.smp_theme_pop_link_id"
                                        @change="changedThemeData()"
                                    >
                                        <option :value="null">Auto</option>
                                        <option
                                            v-for="lnk in getAllLinks()"
                                            :value="lnk.val"
                                        >{{ lnk.show }}</option>
                                    </select>
                                </div>
                            </div>
                            <div v-if="selectedSimplemapSett.smp_theme_pop_style === 'simple_pop'" class="form-group flex flex--center-v no-wrap">
                                <div class="cell_wi flex flex--center-v">
                                    <label>Header color:&nbsp;</label>
                                    <div class="color_wrap">
                                        <tablda-colopicker
                                            :init_color="selectedSimplemapSett.smp_header_color"
                                            :avail_null="true"
                                            @set-color="(clr) => {selectedSimplemapSett.smp_header_color = clr; updateSimplemap(selectedSimplemapSett)}"
                                        ></tablda-colopicker>
                                    </div>
                                </div>

                                <div class="cell_wi">
                                    <label>&nbsp;&nbsp;&nbsp;Width:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    <input
                                        :style="textSysStyle"
                                        class="form-control"
                                        v-model="selectedSimplemapSett.smp_card_width"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    />
                                    <label>px</label>
                                </div>

                                <div class="cell_wi">
                                    <label>&nbsp;&nbsp;&nbsp;Height:&nbsp;</label>
                                    <input
                                        v-if="selectedSimplemapSett.smp_card_height"
                                        :style="textSysStyle"
                                        class="form-control w-sm"
                                        v-model="selectedSimplemapSett.smp_card_height"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    />
                                    <label v-if="selectedSimplemapSett.smp_card_height">px&nbsp;&nbsp;</label>

                                    <span class="indeterm_check__wrap" style="color: #555">
                                        <span
                                            class="indeterm_check"
                                            @click="selectedSimplemapSett.smp_card_height = selectedSimplemapSett.smp_card_height ? null : 300;updateSimplemap(selectedSimplemapSett)"
                                        >
                                            <i v-if="!selectedSimplemapSett.smp_card_height" class="glyphicon glyphicon-ok group__icon"></i>
                                        </span>
                                    </span>
                                    <label>&nbsp;Auto</label>

                                    <label>&nbsp;&nbsp;&nbsp;Max Height:&nbsp;</label>
                                    <input
                                        :style="textSysStyle"
                                        class="form-control w-sm"
                                        v-model="selectedSimplemapSett.smp_card_max_height"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    />
                                    <label>px/%</label>
                                </div>
                            </div>
                            <div v-if="selectedSimplemapSett.smp_theme_pop_style === 'simple_pop'" class="form-group flex flex--center-v no-wrap">
                                <div class="cell_wi">
                                    <label>Image field:&nbsp;&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.smp_picture_field"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option></option>
                                        <option
                                            v-for="header in tableMeta._fields"
                                            :value="header.id"
                                        >{{ header.name }}</option>
                                    </select>
                                </div>

                                <div class="cell_wi">
                                    <label>&nbsp;&nbsp;&nbsp;Position:&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.smp_picture_position"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option value="left">Left</option>
                                        <option value="right">Right</option>
                                    </select>
                                </div>

                                <div v-if="selectedSimplemapSett.smp_picture_field" class="cell_wi">
                                    <label>&nbsp;&nbsp;&nbsp;Width:&nbsp;&nbsp;</label>
                                    <input
                                        :style="textSysStyle"
                                        class="form-control"
                                        v-model="selectedSimplemapSett.smp_picture_width"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    />
                                    <label>%</label>
                                </div>
                            </div>
                            <div v-if="selectedSimplemapSett.smp_theme_pop_style === 'simple_pop'" class="form-group flex flex--center-v no-wrap">
                                <div class="cell_wi">
                                    <label>Multi-Record, Style:&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.multirec_style"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option value="listing">Listing</option>
                                        <option value="tabs">Tabs</option>
                                        <option value="sections">Sections</option>
                                    </select>
                                </div>

                                <div class="cell_wi">
                                    <label>&nbsp;&nbsp;&nbsp;Name:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                    <select
                                        :style="textSysStyle"
                                        class="form-control s-select"
                                        v-model="selectedSimplemapSett.multirec_fld_id"
                                        @change="updateSimplemap(selectedSimplemapSett)"
                                    >
                                        <option></option>
                                        <option
                                            v-for="header in tableMeta._fields"
                                            v-if="header.f_type === 'Attachment'"
                                            :value="header.id"
                                        >{{ header.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex--center-v no-wrap" style="margin: 20px 0 5px 0;">
                                <label>Settings for showing location-based thematic information on the map.</label>
                            </div>
                            <div class="form-group flex flex--center-v no-wrap table_select">
                                <label>Table:&nbsp;</label>
                                <select-with-folder-structure
                                    :cur_val="selectedSimplemapSett.locations_table_id"
                                    :available_tables="$root.settingsMeta.available_tables"
                                    :user="$root.user"
                                    :empty_val="true"
                                    @sel-changed="updatedLocationsTable"
                                    class="form-control"
                                ></select-with-folder-structure>
                                <label>&nbsp;&nbsp;&nbsp;Data Range:&nbsp;</label>
                                <select-block
                                    :is_disabled="!selectedSimplemapSett.locations_table_id"
                                    :options="getRGr(locationsMeta, true)"
                                    :sel_value="selectedSimplemapSett.locations_data_range"
                                    style="width: 200px;"
                                    @option-select="updatedLocationsDataRange"
                                ></select-block>
                            </div>
                            <div class="form-group flex flex--center-v no-wrap">
                                <custom-table
                                    :cell_component_name="'custom-cell-simplemap-sett'"
                                    :global-meta="tableMeta"
                                    :table-meta="$root.settingsMeta['table_simplemaps']"
                                    :settings-meta="$root.settingsMeta"
                                    :all-rows="[selectedSimplemapSett]"
                                    :rows-count="1"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :is-full-width="true"
                                    :user="$root.user"
                                    :behavior="'settings_simplemap_add_sub'"
                                    :available-columns="['locations_name_fld_id','locations_lat_fld_id','locations_long_fld_id','locations_descr_fld_id','locations_icon_shape_fld_id','locations_icon_color_fld_id']"
                                    :use_theme="true"
                                    @updated-row="updateSimplemap"
                                ></custom-table>
                            </div>
                        </div>
                    </div>

                    <div class="full-height permissions-panel no-padding" v-show="activeTab === 'specific'" :style="$root.themeMainBgStyle">
                        <custom-table
                            v-if="selectedSimplemapSett"
                            :cell_component_name="'custom-cell-simplemap-sett'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_simplemaps_2_table_fields']"
                            :all-rows="allFields"
                            :rows-count="allFields.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="withEdit"
                            :user="$root.user"
                            :behavior="'settings_simplemap_add'"
                            :parent-row="selectedSimplemapSett"
                            :available-columns="availPivotFields"
                            :redraw_table="specificRedraw"
                            :use_theme="true"
                            @check-row="simplemapSettCheck"
                        ></custom-table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";
    import DataRangeMixin from "../../../../_Mixins/DataRangeMixin.vue";

    import CustomTable from "../../../../CustomTable/CustomTable";
    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import SelectWithFolderStructure from "../../../../CustomCell/InCell/SelectWithFolderStructure.vue";

    export default {
        name: "SimplemapSettings",
        components: {
            SelectWithFolderStructure,
            SelectBlock,
            TabldaColopicker,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
            DataRangeMixin,
        ],
        data: function () {
            return {
                snp_for_copy: null,
                withEdit: true,
                specificRedraw: 0,
                activeTab: 'list',
                selectedCol: 0,
                addingRowFields: {
                    active: true,
                    position: 'bottom'
                },
                availPivotFields: ['_name','is_header_show','is_header_value','table_show_name','table_show_value','cell_border',
                    'picture_style','picture_fit','is_start_table_popup','is_table_field_in_popup','is_hdr_lvl_one_row','width_of_table_popup'],
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
        },
        computed: {
            ActiveSimplemapFields() {
                return this.tableMeta._simplemaps;
            },
            selectedSimplemapSett() {
                return this.ActiveSimplemapFields[this.selectedCol];
            },
            allFields() {
                return _.filter(this.tableMeta._fields, (fld) => {
                    return this.$root.systemFieldsNoId.indexOf(fld.field) === -1;
                });
            },
            locationsMeta() {
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(this.selectedSimplemapSett.locations_table_id)}) || this.tableMeta;
            },
        },
        watch: {
            table_id(val) {
                this.setSelCol(0);
            }
        },
        methods: {
            valueIsDDLwithColor() {
                let fld = _.find(this.tableMeta._fields, {id: Number(this.selectedSimplemapSett.smp_value_fld_id)});
                if (fld && fld.ddl_id) {
                    let ddl = _.find(this.tableMeta._ddls, {id: Number(fld.ddl_id)})
                        || _.find(this.$root.settingsMeta.shared_ddls || [], {id: Number(fld.ddl_id)});

                    return ddl && (
                        _.find(ddl._references, (ref) => { return ref.color_field_id || (ref._reference_clr_img && ref._reference_clr_img.length) })
                        ||
                        _.find(ddl._items, (it) => { return it.opt_color })
                    );
                }
                return false;
            },
            optColorClicked() {
                this.selectedSimplemapSett.smp_value_ddl_color = this.selectedSimplemapSett.smp_value_ddl_color ? 0 : 1;
                this.selectedSimplemapSett.smp_color_fld_id = null;
                this.updateSimplemap(this.selectedSimplemapSett);
            },
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            copySmpSett() {
                if (this.snp_for_copy && this.selectedSimplemapSett) {
                    this.$root.sm_msg_type = 2;
                    let selCol = this.selectedCol;
                    axios.post('/ajax/addon-simplemap/copy', {
                        from_simplemap_id: this.snp_for_copy,
                        to_simplemap_id: this.selectedSimplemapSett.id,
                        field_pivot: this.activeTab === 'specific' ? 1 : 0,
                    }).then(({ data }) => {
                        this.tableMeta._simplemaps = data;
                        //redraw
                        this.selectedCol = -1;
                        this.$nextTick(() => {
                            this.selectedCol = selCol;
                        });
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            smpsettOpts() {
                return _.map(this.ActiveSimplemapFields, (smp) => {
                    return {
                        val: smp.id,
                        show: smp.name,
                    };
                });
            },
            smpsettChange(opt) {
                this.setSelCol( _.findIndex(this.ActiveSimplemapFields, {id: Number(opt.val)}) );
            },
            rowIndexClickedColumn(index) {
                this.setSelCol(index);
            },
            setSelCol(value) {
                this.selectedCol = -1;
                this.$nextTick(() => {
                    this.selectedCol = value;
                });
            },
            //Simplemap
            addSimplemap(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/addon-simplemap', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._simplemaps = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateSimplemap(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/addon-simplemap', {
                    model_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._simplemaps = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteSimplemap(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/addon-simplemap', {
                    params: {
                        model_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.tableMeta._simplemaps = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //
            simplemapSettCheck(field, val) {
                this.$root.sm_msg_type = 1;
                this.withEdit = false;
                axios.post('/ajax/addon-simplemap/column', {
                    simplemap_id: this.selectedSimplemapSett.id,
                    field_id: field,
                    setting: val.setting,
                    val: val.val,
                }).then(({ data }) => {
                    this.withEdit = true;
                    this.selectedSimplemapSett._fields_pivot = data._fields_pivot;
                    this.specificRedraw++;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            getAllLinks() {
                let res = [];
                _.each(this.tableMeta._fields, (fld) => {
                    _.each(fld._links, (link,idx) => {
                        if (link.link_type === 'Record') {
                            res.push({val: link.id, show: link.name,});
                        }
                    });
                });
                return res;
            },
            changedThemeData() {
                if (this.selectedSimplemapSett.smp_theme_pop_link_id) {
                    this.selectedSimplemapSett.smp_theme_pop_style = 'link_pop';
                }
                this.updateSimplemap(this.selectedSimplemapSett);
            },
            updatedLocationsTable(val) {
                this.selectedSimplemapSett.locations_table_id = val;
                this.updateSimplemap(this.selectedSimplemapSett);
            },
            updatedLocationsDataRange(opt) {
                this.selectedSimplemapSett.locations_data_range = opt.val;
                this.updateSimplemap(this.selectedSimplemapSett);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettingsPermissions";

    .overflow-auto {
        overflow: auto;
    }

    .simplemap_additionals {
        padding: 15px;
        max-width: 920px;

        label {
            margin: 0;
        }
        .color_wrap {
            width: 100%;
            height: 36px;
            position: relative;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .w-sm {
            width: 60px;
        }
        .w-md {
            width: 90px;
        }
        .w-lg {
            width: 150px;
        }
    }
    .group-params-wrapper {
        height: 200px;
        overflow: auto;
    }
    .cell_wi {
        width: 33.33%;
        white-space: nowrap;
        display: flex;
        align-items: center;
    }
    .wi4 {
        width: 25% !important;
    }
    .wi5 {
        width: 20% !important;
    }
    .s-select {
        padding-left: 6px;
    }
</style>
<style lang="scss">
.table_select {
    .select2-container {
        height: 36px !important;
        max-width: 510px !important;
    }
}
</style>