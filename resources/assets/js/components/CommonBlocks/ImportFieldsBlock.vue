<template>
    <div>
        <div v-if="selectedType.key === 'reference'" class="for_references">
            <label>Search and add data sources:</label>
            <table class="table" :style="{width: ref_tb_widths.ref_table.itself.width+'%', float: 'none'}">
                <thead :style="textSysStyle">
                <tr>
                    <th :style="{width: ref_tb_widths.ref_table.id.width+'%', height: th_height.main}">
                        <span>#</span>
                        <header-resizer :table-header="ref_tb_widths.ref_table.id"></header-resizer>
                    </th>
                    <th :style="{width: ref_tb_widths.ref_table.name.width+'%', height: th_height.main}">
                        <span>Name</span>
                        <header-resizer :table-header="ref_tb_widths.ref_table.name"></header-resizer>
                    </th>
                    <th :style="{width: ref_tb_widths.ref_table.table.width+'%', height: th_height.main}">
                        <span>Table</span>
                        <header-resizer :table-header="ref_tb_widths.ref_table.table"></header-resizer>
                    </th>
                    <th :style="{width: ref_tb_widths.ref_table.row_group.width+'%', height: th_height.main}">
                        <span>Row Group</span>
                        <header-resizer :table-header="ref_tb_widths.ref_table.row_group"></header-resizer>
                    </th>
                    <th :style="{width: ref_tb_widths.ref_table.actions.width+'%', height: th_height.main}">
                        <span>Actions</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(tb_refer,i) in tableMeta._table_references" :style="{backgroundColor: tb_refer.id === selRefId ? '#FFD' : ''}">
                    <td>{{ i+1 }}</td>
                    <td>
                        <input class="form-control input-sm" :style="textSysContentSt" v-model="tb_refer.name" @change="updateRefer(tb_refer)"/>
                    </td>
                    <td>{{ refTable(tb_refer.ref_table_id, 'name') }}</td>
                    <td>
                        <select class="form-control input-sm" v-model="tb_refer.ref_row_group_id" :style="textSysContentSt" @change="updateRefer(tb_refer)">
                            <option v-for="rg in refTable(tb_refer.ref_table_id, '_row_groups')" :value="rg.id">{{ rg.name }}</option>
                        </select>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-default blue-gradient"
                                :style="$root.themeButtonStyle"
                                @click="deleteRefer(tb_refer)"
                        >&times;</button>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input class="form-control input-sm" :style="textSysContentSt" v-model="newRefer.name"/>
                    </td>
                    <td>
                        <select-with-folder-structure
                                :cur_val="newRefer.ref_table_id"
                                :empty_val="true"
                                :available_tables="$root.settingsMeta.available_tables"
                                :user="$root.user"
                                @sel-changed="function(val) {newRefer.ref_table_id = val;}"
                                :style="textSysContentSt"
                                class="form-control">
                        </select-with-folder-structure>
                    </td>
                    <td>
                        <select class="form-control input-sm" v-model="newRefer.ref_row_group_id" :style="textSysContentSt">
                            <option v-for="rg in refTable(newRefer.ref_table_id, '_row_groups')" :value="rg.id">{{ rg.name }}</option>
                        </select>
                    </td>
                    <td>
                        <input type="button"
                               class="btn btn-primary btn-sm"
                               :style="$root.themeButtonStyle"
                               @click="refTableAdd()"
                               value="Add">
                    </td>
                </tr>
                </tbody>
            </table>
            <label>Field correspondences for importing:</label>
        </div>

        <table class="wrapper-table">
            <tr>
                <td :style="{width: selectedType.key !== 'reference' ? '100%' : ref_tb_widths.cur_table.itself.width+'%'}">
                    <table class="table" style="table-layout: auto;">
                        <thead :style="textSysStyle">
                        <tr v-show="selectedType.key !== 'reference'">
                            <th style="width: 3%" rowspan="2">#</th>
                            <th style="width: 23%" rowspan="2">Table Header</th>
                            <th style="width: 8%" rowspan="2">Formula Symbol</th>
                            <th style="width: 20%"
                                :rowspan="selectedType.special_sources ? 1 : 2"
                                colspan="2"
                                v-show="availSourceFld">Source Field</th>
                            <th style="width: 14%" rowspan="2" v-show="canGetAccess">Type</th>
                            <th style="width: 18%" colspan="2" v-show="canGetAccess">Format Parameters</th>
                            <th style="width: 12%" rowspan="2">Default Value</th>
                            <th style="width: 8%" rowspan="2">Actions</th>
                        </tr>
                        <tr v-show="selectedType.key !== 'reference'">
                            <th v-show="selectedType.special_sources">Name</th>
                            <th v-show="selectedType.special_sources">Type</th>
                            <th v-show="canGetAccess" style="width: 12%">#1</th>
                            <th v-show="canGetAccess" style="width: 6%">#2</th>
                        </tr>
                        <tr v-show="selectedType.key === 'reference'">
                            <th colspan="7" class="centered" :style="{height: th_height.add}">Current Table</th>
                        </tr>
                        <tr v-show="selectedType.key === 'reference'">
                            <th style="width: 3%">#</th>
                            <th :style="{width: ref_tb_widths.cur_table.header.width+'%', height: th_height.main}">
                                <span>Table Header</span>
                                <header-resizer :table-header="ref_tb_widths.cur_table.header"></header-resizer>
                            </th>
                            <th :style="{width: ref_tb_widths.cur_table.symbol.width+'%', height: th_height.main}">
                                <span>Formula Symbol</span>
                                <header-resizer :table-header="ref_tb_widths.cur_table.symbol"></header-resizer>
                            </th>
                            <th :style="{width: ref_tb_widths.cur_table.type.width+'%', height: th_height.main}">
                                <span>Type</span>
                                <header-resizer :table-header="ref_tb_widths.cur_table.type"></header-resizer>
                            </th>
                            <th :style="{width: ref_tb_widths.cur_table.def.width+'%', height: th_height.main}">
                                <span>Default</span>
                                <header-resizer :table-header="ref_tb_widths.cur_table.def"></header-resizer>
                            </th>
                        </tr>
                        </thead>

                        <tbody id="import_table_body">
                        <!--Present rows-->
                        <tr v-for="(header, index) in tableHeaders"
                            v-if="!inArray(header.field, $root.systemFields)
                                            && header.status !== 'del'
                                            && canViewEditCol(header, 'view_fields')"
                        >
                            <td class="td-indexes" :class="[overIndex === index ? 'td-overed' : '']" :style="$root.themeButtonStyle">
                                <span
                                        title="Click and drag to change order."
                                        draggable="true"
                                        @dragstart="startChangeOrder(index)"
                                        @dragover.prevent=""
                                        @dragenter="overIndex = index"
                                        @dragend="overIndex = null"
                                        @drop="endChangeOrder(index)"
                                >{{ index+1 }}</span>
                            </td>
                            <td>
                                <input type="text"
                                       :style="textSysContentSt"
                                       class="form-control"
                                       :disabled="!presentSource || !canViewEditCol(header, 'edit_fields')"
                                       v-model="header.name"
                                />
                            </td>
                            <td>
                                <input type="text"
                                       :style="textSysContentSt"
                                       class="form-control"
                                       maxlength="20"
                                       :disabled="!presentSource || !canViewEditCol(header, 'edit_fields')"
                                       v-model="header.formula_symbol"
                                />
                            </td>

                            <!--Source Field-->
                            <td v-show="availSourceFld" :colspan="selectedType.special_sources ? 1 : 2">
                                <select class="form-control"
                                        :style="textSysContentSt"
                                        :disabled="!presentSource || !canViewEditCol(header, 'edit_fields')"
                                        v-model="header.col"
                                >
                                    <template v-if="fieldsColumns">
                                        <option></option>
                                        <option v-for="(col, i) in fieldsColumns" :value="i">{{ col }}</option>
                                    </template>
                                    <template v-if="mysqlColumns">
                                        <option></option>
                                        <option v-for="col in mysqlColumns" :value="col">{{ col }}</option>
                                    </template>
                                </select>
                            </td>
                            <td v-show="availSourceFld && selectedType.special_sources">
                                <select class="form-control" :style="textSysContentSt" :disabled="!presentSource" v-model="header._source_type" @change="autoTypeFromAir(header)">
                                    <option v-for="src in specialSources" :disabled="src.disabled">{{ src.val }}</option>
                                </select>
                            </td>

                            <!--Type-->
                            <td v-show="canGetAccess">
                                <select class="form-control" :style="textSysContentSt" :disabled="!presentSource" v-model="header.f_type" @change="checkSize(header)">
                                    <option v-for="type in columnTypes" :disabled="type.disabled" :value="type.value">{{ type.name }}</option>
                                </select>
                            </td>

                            <!--Format Parameters-->
                            <td v-show="selectedType.key !== 'reference' && canGetAccess" :colspan="spanFormat(header) ? 2 : 1">
                                <select-block
                                    :options="loadFormats(header)"
                                    :sel_value="header._f_format_l"
                                    :fixed_pos="true"
                                    :is_disabled="!availfFormat(header)"
                                    :is_multiselect="formatMsel(header)"
                                    style="text-align: left"
                                    :style="textSysContentSt"
                                    @option-select="(opt) => { selectChangeFormat(header, opt); }"
                                ></select-block>
                            </td>
                            <td v-show="selectedType.key !== 'reference' && canGetAccess" v-if="!spanFormat(header)">
                                <input v-if="availFormatAttach(header)"
                                       type="text"
                                       :style="textSysContentSt"
                                       class="form-control"
                                       v-model="header._f_format_r"
                                       @change="chanfFormat(header)">
                                <input v-else-if="availFormatInput(header)"
                                       type="number"
                                       :style="textSysContentSt"
                                       class="form-control"
                                       v-model="header._f_format_r"
                                       @change="chanfFormat(header)">
                                <select class="form-control"
                                        :style="textSysContentSt"
                                        v-else-if="availFormatDecim(header)"
                                        v-model="header._f_format_r"
                                        @change="chanfFormat(header)"
                                >
                                    <option v-if="header.f_type != 'Rating'" value="0">0</option>
                                    <option v-for="i in 10" :value="i">{{ i }}</option>
                                </select>
                                <select v-else="" class="form-control" :style="textSysContentSt" disabled="disabled"></select>
                            </td>

                            <td v-if="header.f_type === 'Rating' && header._f_format_l === 'Custom' && header.id && !header.rating_icon">
                                <file-uploader-block
                                    :format="header.f_format"
                                    :clear_before="true"
                                    :just_default="true"
                                    :header-index="index"
                                    :table_id="tableMeta.id"
                                    :field_id="header.id"
                                    :row_id="'rating_icon'"
                                    @uploaded-file="saveIconPath"
                                ></file-uploader-block>
                            </td>
                            <td
                                    v-else=""
                                    :is="'custom-cell-table-data'"
                                    :global-meta="tableMeta"
                                    :table-meta="tableMeta"
                                    :settings-meta="$root.settingsMeta"
                                    :table-row="header._empty_row"
                                    :table-header="header"
                                    :cell-value="header._empty_row[header.field]"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :behavior="'list_view'"
                                    :with_edit="presentSource && canViewEditCol(header, 'edit_fields') && !cannotEditDefa(header)"
                                    :user="$root.user"
                                    :style="{backgroundColor: !presentSource || !canViewEditCol(header, 'edit_fields') || cannotEditDefa(header) ? '#EEE' : null}"
                                    @updated-cell="(row) => { changeDefaultVal(row, header) }"
                                    @remove-icon="() => { header.rating_icon = '' }"
                            ></td>

                            <td class="centered" v-show="!inArray(selectedType.key, ['reference'])">
                                <button v-if="canGetAccess"
                                        title="Delete the row."
                                        type="button"
                                        class="btn btn-sm btn-default blue-gradient"
                                        :disabled="!presentSource"
                                        :style="$root.themeButtonStyle"
                                        style="line-height: 19px;font-size: 2em;padding: 5px;"
                                        @click="delHeader(header)"
                                >&times;</button>
                                <button v-if="!inArray(selectedType.key, ['reference'])"
                                        title="Insert a blank row below."
                                        type="button"
                                        class="btn btn-sm btn-default blue-gradient"
                                        :disabled="!presentSource"
                                        :style="$root.themeButtonStyle"
                                        @click="addHeader(index+1)"
                                ><i class="fas fa-reply"></i></button>
                            </td>
                        </tr>
                        <!--Row for adding new headers-->
                        <tr v-if="selectedType.key !== 'reference'">
                            <td :style="$root.themeButtonStyle">
                                <span></span>
                            </td>
                            <td>
                                <input type="text" :style="textSysContentSt" class="form-control" :disabled="!presentSource" v-model="newHeader.name"/>
                            </td>
                            <td>
                                <input type="text"
                                       :style="textSysContentSt"
                                       class="form-control"
                                       maxlength="20"
                                       :disabled="!presentSource"
                                       v-model="newHeader.formula_symbol"/>
                            </td>

                            <!--Source Field-->
                            <td v-show="availSourceFld" :colspan="selectedType.special_sources ? 1 : 2">
                                <select class="form-control" :style="textSysContentSt" :disabled="!presentSource" v-model="newHeader.col">
                                    <template v-if="fieldsColumns">
                                        <option></option>
                                        <option v-for="(col, i) in fieldsColumns" :value="i">{{ col }}</option>
                                    </template>
                                    <template v-if="mysqlColumns">
                                        <option></option>
                                        <option v-for="col in mysqlColumns" :value="col">{{ col }}</option>
                                    </template>
                                </select>
                            </td>
                            <td v-show="availSourceFld && selectedType.special_sources">
                                <select class="form-control" :style="textSysContentSt" :disabled="!presentSource" v-model="newHeader._source_type" @change="autoTypeFromAir(newHeader)">
                                    <option v-for="src in specialSources" :disabled="src.disabled">{{ src.val }}</option>
                                </select>
                            </td>

                            <!--Type-->
                            <td v-show="canGetAccess">
                                <select class="form-control" :style="textSysContentSt" :disabled="!presentSource" v-model="newHeader.f_type" @change="checkSize(newHeader)">
                                    <option v-for="type in columnTypes" :disabled="type.disabled" :value="type.value">{{ type.name }}</option>
                                </select>
                            </td>

                            <!--Format Parameters-->
                            <td v-show="selectedType.key !== 'reference' && canGetAccess" :colspan="spanFormat(newHeader) ? 2 : 1">
                                <select-block
                                    :style="textSysContentSt"
                                    :options="loadFormats(newHeader)"
                                    :sel_value="newHeader._f_format_l"
                                    :fixed_pos="true"
                                    :is_disabled="!availfFormat(newHeader)"
                                    :is_multiselect="formatMsel(newHeader)"
                                    style="text-align: left"
                                    @option-select="(opt) => { selectChangeFormat(newHeader, opt); }"
                                ></select-block>
                            </td>
                            <td v-show="selectedType.key !== 'reference' && canGetAccess" v-if="!spanFormat(newHeader)">
                                <input v-if="availFormatAttach(newHeader)"
                                       :style="textSysContentSt"
                                       type="text"
                                       class="form-control"
                                       v-model="newHeader._f_format_r"
                                       @change="chanfFormat(newHeader)">
                                <input v-else-if="availFormatInput(newHeader)"
                                       :style="textSysContentSt"
                                       type="number"
                                       class="form-control"
                                       v-model="newHeader._f_format_r"
                                       @change="chanfFormat(newHeader)">
                                <select class="form-control"
                                        :style="textSysContentSt"
                                        v-else-if="availFormatDecim(newHeader)"
                                        v-model="newHeader._f_format_r"
                                        @change="chanfFormat(newHeader)"
                                >
                                    <option v-if="newHeader.f_type != 'Rating'" value="0">0</option>
                                    <option v-for="i in 10" :value="i">{{ i }}</option>
                                </select>
                                <select v-else="" class="form-control" :style="textSysContentSt" disabled="disabled"></select>
                            </td>

                            <td
                                    :is="'custom-cell-table-data'"
                                    :global-meta="tableMeta"
                                    :table-meta="tableMeta"
                                    :settings-meta="$root.settingsMeta"
                                    :table-row="newHeader._empty_row"
                                    :table-header="newHeader"
                                    :cell-value="newHeader._empty_row[newHeader.field]"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :behavior="'list_view'"
                                    :with_edit="presentSource && canViewEditCol(newHeader, 'edit_fields')"
                                    :user="$root.user"
                                    :style="{backgroundColor: !presentSource || !canViewEditCol(newHeader, 'edit_fields') || cannotEditDefa(newHeader) ? '#EEE' : null}"
                                    @updated-cell="(row) => { changeDefaultVal(row, newHeader) }"
                            ></td>
                            <td class="centered" v-show="!inArray(selectedType.key, ['reference'])">
                                <button type="button"
                                        class="btn btn-info"
                                        :style="$root.themeButtonStyle"
                                        :disabled="!presentSource"
                                        @click="addHeader()"
                                >Add</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <header-resizer :table-header="ref_tb_widths.cur_table.itself"></header-resizer>
                </td>

                <td :style="{width: ref_tb_widths.ref_field.itself.width+'%'}" v-if="selectedType.key === 'reference'">
                    <table class="table">
                        <thead :style="textSysStyle">
                        <tr>
                            <th colspan="2" :style="{height: th_height.add}">
                                <div class="flex flex--center-v">
                                    <span>Current Source:&nbsp;</span>
                                    <select class="form-control input-sm" :style="textSysContentSt" v-model="selRefId" @change="setSelRef" style="max-width: 50%">
                                        <option v-for="tb_refer in tableMeta._table_references" :value="tb_refer.id">
                                            {{ tb_refer.name || refTable(tb_refer.ref_table_id, 'name') }}
                                        </option>
                                    </select>
                                    <button type="button"
                                            class="btn btn-sm btn-default"
                                            :style="$root.themeButtonStyle"
                                            @click="selRefId ? emitImport() : null"
                                    >
                                        <span class="fa fa-arrow-right"></span>
                                    </button>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th :style="{width: ref_tb_widths.ref_field.field.width+'%', height: th_height.main}">
                                <span>Field</span>
                                <header-resizer :table-header="ref_tb_widths.ref_field.field"></header-resizer>
                            </th>
                            <th :style="{width: ref_tb_widths.ref_field.type.width+'%', height: th_height.main}">
                                <span>Type</span>
                                <header-resizer :table-header="ref_tb_widths.ref_field.type"></header-resizer>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <template v-if="selectedRefer">
                            <tr v-for="(header, index) in tableHeaders"
                                v-if="!inArray(header.field, $root.systemFields)
                                            && header.status !== 'del'
                                            && canViewEditCol(header, 'view_fields')"
                            >
                                <td>
                                    <select class="form-control" :style="textSysContentSt" @change="changedRefField(header.id, header._ref_tmp_id)" v-model="header._ref_tmp_id">
                                        <option></option>
                                        <option v-for="r_field in refTable(selectedRefer.ref_table_id, '_fields')" :value="r_field.id">{{ r_field.name }}</option>
                                    </select>
                                </td>
                                <td><input class="form-control" :style="textSysContentSt" disabled type="text"></td>
                            </tr>
                        </template>
                        </tbody>
                    </table>

                    <header-resizer :table-header="ref_tb_widths.ref_field.itself"></header-resizer>
                </td>
            </tr>
        </table>
    </div>
</template>

<script>
    import {SpecialFuncs} from  '../../classes/SpecialFuncs';

    import {eventBus} from '../../app';

    import DataImportMixin from  './../_Mixins/DataImportMixin.vue';
    import CellStyleMixin from "../_Mixins/CellStyleMixin";

    import HeaderResizer from './../CustomTable/Header/HeaderResizer';
    import SelectWithFolderStructure from './../CustomCell/InCell/SelectWithFolderStructure';
    import CustomCellTableData from "./../CustomCell/CustomCellTableData";
    import FileUploaderBlock from "./FileUploaderBlock";
    import SelectBlock from "./SelectBlock";

    export default {
        name: "ImportFieldsBlock",
        mixins: [
            CellStyleMixin,
            DataImportMixin,
        ],
        components: {
            SelectBlock,
            FileUploaderBlock,
            SelectWithFolderStructure,
            CustomCellTableData,
            HeaderResizer,
        },
        data: function () {
            return {
                newRefer: this.emptyRefer(),
                selRefId: null,

                newHeader: this.emptyHeader(),
                fieldsWithoutSize: [
                    'Date',
                    'Date Time',
                    'Time',
                    'Duration',
                    'Auto Number',
                    'Attachment',
                    'Address',
                    'Rating',
                    'Boolean',
                    'Color',
                    'Vote',
                    'User',
                    'Text',
                    'Long Text',
                ],

                ref_tb_widths: {
                    cur_table: {
                        header: {width: 25},
                        symbol: {width: 10},
                        type: {width: 25},
                        format: {width: 25},
                        size: {width: 25},
                        def: {width: 25},
                        formula: {width: 25},
                        itself: {width: 55}
                    },
                    ref_table: {
                        id: {width: 10},
                        name: {width: 25},
                        table: {width: 25},
                        row_group: {width: 25},
                        actions: {width: 15},
                        itself: {width: 50}
                    },
                    ref_field: {
                        field: {width: 50},
                        type: {width: 50},
                        itself: {width: 45}
                    },
                },
                th_height: {
                    add: '48px',
                    main: '36px',
                },

                draggedIndex: null,
                overIndex: null,
            };
        },
        computed: {
            availSourceFld() {
                return this.inArray(this.selectedType.key, ['csv','mysql','paste','g_sheets','web_scrap','table_ocr','airtable_import']);
            },
            selectedRefer() {
                return _.find(this.tableMeta._table_references, {id: Number(this.selRefId)}) || {};
            },
            columnTypes() {
                let ar = [
                    'User',
                    'String',
                    'Text',
                    'Long Text',
                    'Integer',
                    'Decimal',
                    'Currency',
                    'Percentage',
                    'Date',
                    'Time',
                    'Duration',
                    'Date Time',
                    'Auto Number',
                    'Auto String',
                    'Attachment',
                    'Address',
                    'Rating',
                    'Progress Bar',
                    'Boolean',
                    'Color',
                    'Vote',
                ].sort();

                return _.map(ar, (it) => {
                    let avail = false;
                    switch (it) {
                        case 'User': avail = !!this.$root.checkAvailable(this.$root.user, 'field_type_user'); break;
                        case 'Address': avail = !!this.$root.checkAvailable(this.$root.user, 'can_google_autocomplete'); break;
                        default: avail = true; break;
                    }

                    let renamer = it;
                    switch (it) {
                        case 'Attachment': renamer = 'Document'; break;
                    }

                    return {
                        disabled: !avail,
                        name: renamer,
                        value: it,
                    };
                });
            },
            specialSources() {
                switch (this.selectedType.key) {
                    case 'airtable_import': return [
                        { val: 'Attachment', tablda: 'Attachment' },
                        { val: 'Auto Number', tablda: 'Auto Number' },
                        { val: 'Barcode', tablda: 'String' },
                        { val: 'Button', tablda: 'String' },
                        { val: 'Checkbox', tablda: 'Boolean' },
                        { val: 'Collaborator', tablda: 'User' },
                        { val: 'Count', tablda: 'Integer' },
                        { val: 'Created By', tablda: 'User' },
                        { val: 'Created Time', tablda: 'Date Time' },
                        { val: 'Currency', tablda: 'Currency' },
                        { val: 'Date', tablda: 'Date' },
                        { val: 'Date Time', tablda: 'Date Time' },
                        { val: 'Duration', tablda: 'Duration' },
                        { val: 'Email', tablda: 'String' },
                        { val: 'Formula', tablda: 'String' },
                        { val: 'Last Modified By', tablda: 'User' },
                        { val: 'Last Modified Time', tablda: 'Date Time' },
                        { val: 'Linked Record', tablda: 'String' },
                        { val: 'Lookup', tablda: 'String' },
                        { val: 'Long Text', tablda: 'String' },
                        { val: 'Multiple Select', tablda: 'String' },
                        { val: 'Number', tablda: 'Decimal' },
                        { val: 'Percent', tablda: 'Percentage' },
                        { val: 'Phone Number', tablda: 'String' },
                        { val: 'Rating', tablda: 'Rating' },
                        { val: 'Single Line Text', tablda: 'String' },
                        { val: 'Single Select', tablda: 'String' },
                        { val: 'URL', tablda: 'String' },
                    ];
                    default: return [];
                }
            },
        },
        props: {
            tableMeta: Object,
            selectedType: Object,
            tableHeaders: Array,
            canGetAccess: Boolean,
            presentSource: Boolean,
            fieldsColumns: Array,
            mysqlColumns: Array,
        },
        watch: {
        },
        methods: {
            //Headers
            saveIconPath(idx, file) {
                this.tableHeaders[idx].rating_icon = file.filepath + file.filename;
            },
            addHeader(index) {
                index = index || this.tableHeaders.length;
                this.tableHeaders.splice(index, 0, Object.assign({}, this.newHeader) );
                this.newHeader = Object.assign({}, this.emptyHeader());

                let pre_col = {};
                pre_col[index] = this.tableHeaders[index];
                axios.post('/ajax/import/presave-column', {
                    table_id: this.tableMeta.id,
                    pre_columns: pre_col,
                }).then(({ data }) => {
                    let hdr = _.first(data);
                    this.tableHeaders[index].id = hdr.id;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            delHeader(header) {
                header.status = 'del';
            },
            checkSize(header) {
                switch (header.f_type) {
                    case 'Progress Bar':
                        header.f_size = '2.4';
                        break;
                    case 'Text':
                    case 'Vote':
                        header.f_size = '65535';
                        break;
                    case 'Long Text':
                        header.f_size = '4294967295';
                        break;
                    case 'Integer':
                    case 'Auto Number':
                    case 'Rating':
                    case 'Boolean':
                    case 'Color':
                    case 'Duration':
                        //case 'Formula':
                        header.f_size = '10';
                        break;
                    case 'Decimal':
                    case 'Currency':
                    case 'Percentage':
                        header.f_size = '8.2';
                        break;
                    case 'Date':
                    case 'Date Time':
                    case 'Time':
                        header.f_size = '16';
                        break;
                    default: header.f_size = '64';
                }

                header._f_format_r = (header.f_type === 'Auto String' ? 7 : 2);
            },
            autoTypeFromAir(header) {
                let air = _.find(this.specialSources, {val: header._source_type});
                if (air) {
                    header.f_type = air.tablda;
                    this.checkSize(header);
                }
            },

            //Formats
            loadFormats(header) {
                let filename = header.rating_icon
                    ? header.rating_icon.replace(/^.*[\\\/]/, '').replace(/\.[^.]+$/gi, '')
                    : '';
                if (!header.id) {
                    filename = 'Custom (click "Add")';
                }
                switch (header.f_type) {
                    case 'Decimal':
                    case 'Currency':
                    case 'Percentage':
                    case 'Progress Bar':
                        return [
                            {val:null},
                            {val:'Float'},
                            {val:'Comma'},
                            {val:'Scientific'}
                        ];

                    case 'Date':
                        return [
                            {val:null},
                            {val:'mm-dd-yyyy'},
                            {val:'{id:m-d-yyy'},
                            {val:'yyyy-mm-dd'},
                            {val:'yyy-m-d'},
                            {val:'dd-mm-yyyy'},
                            {val:'d-m-yyy'},
                            {val:'Month D, Yr'},
                            {val:'Mon. D, Yr'}
                        ];

                    case 'Duration':
                        return [
                            {val:null},
                            {val:'s'},
                            {val:'m, s'},
                            {val:'h, m, s'},
                            {val:'d, h, m, s'},
                            {val:'wk, d, h, m, s'}
                        ];

                    case 'Boolean':
                        return [
                            {val:null},
                            {val:'Checkbox'},
                            {val:'Slider'}
                        ];

                    case 'Vote':
                        return [
                            {val:null},
                            {val:'Yes/No'},
                            {val:'Like/Dislike'}
                        ];

                    case 'Rating':
                        return [
                            {val:null},
                            {val:'Star'},
                            {val:'Flag'},
                            {val:'Custom', show: filename || 'Custom', disabled: !header.id}
                        ];

                    case 'Auto String':
                        return [
                            {val:null},
                            {val:'num', show: 'Numbers'},
                            {val:'upper', show: 'LETTERS'},
                            {val:'lower', show: 'letters'},
                            {val:'num_upper', show: 'NBR_LTR'},
                            {val:'num_lower', show: 'NBR_ltr'},
                            {val:'mixed', show: 'MIXED'},
                        ];

                    case 'Attachment':
                        return this.$root.settingsMeta.upload_file_formats
                            || [
                                {val:null, html: 'Image/Picture', hasGroup: ['jpg','jpeg','png','tiff','gif','eps']},
                                {val:'jpg', html: '&nbsp;&nbsp;&nbsp;Jpg'},
                                {val:'jpeg', html: '&nbsp;&nbsp;&nbsp;Jpeg'},
                                {val:'png', html: '&nbsp;&nbsp;&nbsp;Png'},
                                {val:'tiff', html: '&nbsp;&nbsp;&nbsp;Tiff'},
                                {val:'gif', html: '&nbsp;&nbsp;&nbsp;Gif'},
                                {val:'eps', html: '&nbsp;&nbsp;&nbsp;Eps'},
                                {val:null, html: 'Document', hasGroup: ['pdf','docx','doc','xls','xlsx','xlsm','txt','dat']},
                                {val:'pdf', html: '&nbsp;&nbsp;&nbsp;Pdf'},
                                {val:'docx', html: '&nbsp;&nbsp;&nbsp;Docx'},
                                {val:'doc', html: '&nbsp;&nbsp;&nbsp;Doc'},
                                {val:'xls', html: '&nbsp;&nbsp;&nbsp;Xls'},
                                {val:'xlsx', html: '&nbsp;&nbsp;&nbsp;Xlsx'},
                                {val:'xlsm', html: '&nbsp;&nbsp;&nbsp;Xlsm'},
                                {val:'txt', html: '&nbsp;&nbsp;&nbsp;Txt'},
                                {val:'dat', html: '&nbsp;&nbsp;&nbsp;Dat'},
                                {val:null, html: 'Video', hasGroup: ['mp4','wmv','mov','avi']},
                                {val:'mp4', html: '&nbsp;&nbsp;&nbsp;Pdf'},
                                {val:'wmv', html: '&nbsp;&nbsp;&nbsp;Docx'},
                                {val:'mov', html: '&nbsp;&nbsp;&nbsp;Doc'},
                                {val:'avi', html: '&nbsp;&nbsp;&nbsp;Xls'},
                                {val:null, html: 'Compressed', hasGroup: ['zip','tar']},
                                {val:'zip', html: '&nbsp;&nbsp;&nbsp;Pdf'},
                                {val:'tar', html: '&nbsp;&nbsp;&nbsp;Docx'},
                            ];

                    default: return [];
                }
            },
            spanFormat(header) {
                return ['Duration','Date','Boolean','Vote'].indexOf(header.f_type) > -1;
            },
            availfFormat(header) {
                return this.inArray(header.f_type,
                    ['Attachment','Decimal','Currency','Percentage','Progress Bar','Date','Duration','Boolean','Vote','Rating','Auto String']);
            },
            formatMsel(header) {
                return this.inArray(header.f_type,['Attachment']);
            },
            availFormatInput(header) {
                return this.inArray(header.f_type, ['Attachment','Auto String']);
            },
            availFormatAttach(header) {
                return this.inArray(header.f_type, ['Attachment']);
            },
            availFormatDecim(header) {
                return this.inArray(header.f_type, ['Decimal','Currency','Percentage','Progress Bar','Rating']);
            },
            cannotEditDefa(header) {
                return header.input_type === 'Formula'
                    || ['Attachment','Auto Number','Auto String'].indexOf(header.f_type) > -1;
            },
            changeDefaultVal(row, header) {
                header.f_default = row[header.field];
                axios.post('/ajax/table-data/info-row', {
                    table_id: this.tableMeta.id,
                    table_row: header._empty_row,
                }).then(({data}) => {
                    this.$root.assignObject(data.row, header._empty_row);
                });
            },
            selectChangeFormat(header, opt) {
                _.each(opt.hasGroup || [opt.val], (optval) => {
                    if (this.formatMsel(header)) {
                        let arr = String(header._f_format_l || '').split(',');
                        if (arr.indexOf(optval) === -1) {
                            arr.push(optval);
                            header._f_format_l = arr.join(',');
                        } else {
                            header._f_format_l = arr.filter(e => e != optval).join(',');
                        }
                    } else {
                        header._f_format_l = optval;
                    }
                });
                this.chanfFormat(header);
            },
            chanfFormat(header) {
                if (header.f_type === 'Attachment') {
                    header._f_format_r = parseInt(header._f_format_r) + 'MB';
                }
                if (header.f_type === 'Auto String') {
                    header._f_format_r = Math.max(header._f_format_r, 3);
                    header._f_format_r = Math.min(header._f_format_r, 20);
                }
                if (['Attachment','Decimal','Currency','Percentage','Progress Bar','Rating','Auto String'].indexOf(header.f_type) > -1) {
                    header.f_format = header._f_format_l + '-' + header._f_format_r;
                } else {
                    header.f_format = header._f_format_l;
                }
            },

            //change order of the rows
            startChangeOrder(index) {
                this.draggedIndex = index;
            },
            endChangeOrder(index) {
                if (this.draggedIndex) {
                    index = this.draggedIndex < index ? index-1 : index;
                    let hdr = this.tableHeaders.splice( this.draggedIndex, 1 );
                    this.tableHeaders.splice( index, 0, hdr[0] );
                    this.draggedIndex = null;
                }
            },


            //Refer save to backend Functions
            addRefer(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/refer', {
                    table_id: this.tableMeta.id,
                    fields: this.$root.deleteSystemFields( _.cloneDeep(tableRow) )
                }).then(({ data }) => {
                    this.tableMeta._table_references = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateRefer(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/refer', {
                    table_id: this.tableMeta.id,
                    refer_id: tableRow.id,
                    fields: this.$root.deleteSystemFields( _.cloneDeep(tableRow) )
                }).then(({ data }) => {
                    this.tableMeta._table_references = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteRefer(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/refer', {
                    params: {
                        table_id: this.tableMeta.id,
                        refer_id: tableRow.id,
                    }
                }).then(({ data }) => {
                    this.tableMeta._table_references = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            addReferCorr(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/refer/corrs', {
                    refer_id: this.selectedRefer.id,
                    fields: this.$root.deleteSystemFields( _.cloneDeep(tableRow) )
                }).then(({ data }) => {
                    this.tableMeta._table_references = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateReferCorr(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/refer/corrs', {
                    refer_id: this.selectedRefer.id,
                    corr_id: tableRow.id,
                    fields: this.$root.deleteSystemFields( _.cloneDeep(tableRow) )
                }).then(({ data }) => {
                    this.tableMeta._table_references = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteReferCorr(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/refer/corrs', {
                    params: {
                        refer_id: this.selectedRefer.id,
                        corr_id: tableRow.id,
                    }
                }).then(({ data }) => {
                    this.tableMeta._table_references = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },


            //methods for 'reference' import
            emptyRefer() {
                return {
                    name: '',
                    ref_table_id: null,
                    ref_row_group_id: null,
                };
            },
            refTable(id, field) {
                let rTb = _.find(this.$root.settingsMeta.available_tables, {id: Number(id)}) || {};
                return field ? rTb[field] : rTb;
            },
            refTableAdd() {
                this.addRefer(this.newRefer);
                this.newRefer = this.emptyRefer();
            },
            setSelRef() {
                this.$emit('sel-ref-table', this.selRefId);
                if (this.selectedRefer) {
                    _.each(this.tableHeaders, (header) => {
                        let found = _.find(this.selectedRefer._reference_corrs, {table_field_id: Number(header.id)});
                        header._ref_tmp_id = found ? found.ref_field_id : null;
                    });
                }
            },
            changedRefField(table_field_id, ref_field_id) {
                if (this.selectedRefer) {

                    let found = _.find(this.selectedRefer._reference_corrs, {table_field_id: Number(table_field_id)});

                    if (ref_field_id) {
                        if (found) {
                            found.ref_field_id = ref_field_id;
                            this.updateReferCorr(found);
                        } else {
                            this.addReferCorr( {table_field_id:table_field_id, ref_field_id:ref_field_id} );
                        }
                    } else {
                        if (found) {
                            this.deleteReferCorr(found);
                        }
                    }
                }
            },
            emitImport() {
                this.$emit('reference-import');
            },
        },
        mounted() {
        },
        beforeDestroy() {
        },
    }
</script>

<style lang="scss" scoped>
    .wrapper-table {
        width: 100%;

        tr td {
            position: relative;
            vertical-align: top;
        }
    }

    .table {
        table-layout: fixed;
        float: left;
        margin-bottom: 0;

        .centered {
            text-align: center;
        }

        .right-cell {
            text-align: right;
            border-right: none;
        }

        .left-cell {
            text-align: left;
            border-left: none;
        }

        tr th, tr td {
            border: 1px solid #d3e0e9;
            text-align: center;
        }
        tr th {
            position: relative;
            background-color: #CCC;
            line-height: 0.7em;
            vertical-align: middle;
        }
        tr td {
            vertical-align: middle;
            padding: 0;
        }
    }

    .for_references {
        //background-color: #FFF;

        label {
            margin: 5px 0 0 5px;
            font-size: 1.5em;
            color: #333;
        }
    }

    .fa-reply {
        transform: scaleY(-1);
    }

    .td-indexes {
        background-color: #DDD;

        span {
            display: block;
            cursor: pointer;
        }
    }

    .td-overed {
        border-top: 2px dashed #000 !important;
    }
</style>