<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab" :style="textSysStyle">
            <div class="top-text" :style="textSysStyle">
                <span>Map marker icon association</span>
                <span>&nbsp;&nbsp;&nbsp;Style:</span>
                <select :style="textSysStyle" v-model="tableMeta.map_icon_style" class="form-control fixed_control" @change="colChanged()">
                    <option value="dist">Distinctive</option>
                    <option value="comp">Complete</option>
                </select>
                <span>&nbsp;&nbsp;&nbsp;Field for icons:</span>
                <select :style="textSysStyle" v-model="tableMeta.map_icon_field_id" class="form-control fixed_control" @change="colChanged()">
                    <option></option>
                    <option v-for="(fld, idx) in mapFields" :value="fld.id">{{ $root.uniqName(fld.name) }}</option>
                </select>
            </div>
            <div class="permissions-panel">
                <div v-if="tableMeta.map_icon_style == 'dist'" class="full-frame custom-table-wrapper">
                    <table class="full-width">
                        <thead class="table-head">
                        <tr>
                            <th :style="textSysStyle">Field Value</th>
                            <th :style="textSysStyle">Icon</th>
                            <th :style="textSysStyle">Height</th>
                            <th :style="textSysStyle">Upload</th>
                        </tr>
                        </thead>
                        <tbody class="table-body">
                        <tr v-for="(col, idx) in columnValues">
                            <td>{{ col.row_val }}</td>
                            <td :ref="'cell_icon_'+idx"
                                @dragenter="iconEnter(idx)"
                                @dragover.prevent=""
                                @dragleave.prevent=""
                                @dragend="iconLeave"
                                @drop.prevent.stop="(e) => { iconDrop(e, idx); }"
                                :style="iconStyle(idx)"
                            >
                                <template v-if="col.icon_path">
                                    <img class="preview_icon" :src="$root.fileUrl({url:col.icon_path})"/>
                                    <span class="delete-icon-btn" @click="deleteIcon(col, idx)">&times;</span>
                                </template>
                            </td>
                            <td>
                                <height-icon-input :col="col" @height-changed="changeIcon(col)"></height-icon-input>
                            </td>
                            <td><input class="form-control" type="file" :ref="'upload_'+idx" @change="uploadIcon(idx)"/></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    import CustomTable from '../../../../CustomTable/CustomTable';
    import HeightIconInput from '../../../../Buttons/HeightIconInput';

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "MapIcons",
        components: {
            CustomTable,
            HeightIconInput
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                selectedFile: null,
                attach_overed: -1,
            }
        },
        props:{
            tableMeta: Object,
            columnValues: Array,
        },
        computed: {
            mapFields() {
                return _.filter(this.tableMeta._fields, (fld) => {
                    return (this.tableMeta.map_icon_style === 'dist' && fld.f_type !== 'Attachment')
                        || (this.tableMeta.map_icon_style === 'comp' && fld.f_type === 'Attachment');
                });
            },
            iconFld() {
                return _.find(this.tableMeta._fields, {id: Number(this.tableMeta.map_icon_field_id)});
            },
        },
        methods: {
            colChanged() {
                let data = this.iconFld ?
                    { field_id: this.iconFld.id } :
                    { table_id: this.tableMeta.id };
                data.map_style = this.tableMeta.map_icon_style;
                data.special_params = SpecialFuncs.specialParams();

                this.$root.sm_msg_type = 2;
                axios.get('/ajax/table-data/field/map-icons', {
                    params: data
                }).then(({ data }) => {
                    this.$emit('icons-changed', data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            uploadIcon(col_idx, fore_file) {
                let data = new FormData();
                let file = fore_file || this.$refs['upload_'+col_idx][0].files[0];
                data.append('table_field_id', this.iconFld.id);
                data.append('row_val', this.columnValues[col_idx].row_val);
                data.append('file', file);
                data.append('height', null);
                data.append('width', null);
                data.append('special_params', JSON.stringify(SpecialFuncs.specialParams()) );

                if (file) {
                    this.$root.sm_msg_type = 1;
                    axios.post('/ajax/table-data/field/map-icons', data, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(({ data }) => {
                        this.columnValues[col_idx].icon_path = data.path;
                        this.columnValues[col_idx].height = null;
                        this.columnValues[col_idx].width = null;
                        this.$emit('should-redraw-map');
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                    this.selectedFile = null;
                } else {
                    Swal('No file', '', 'info');
                }
            },
            changeIcon(col) {
                if (this.tableMeta.map_icon_field_id && col.icon_path) {
                    let img = new Image();
                    img.addEventListener('load', () => {
                        this.changeIconNextStep(col.row_val, img.width, img.height, col.height);
                    });
                    img.src = this.$root.fileUrl({url:col.icon_path});
                }
            },
            changeIconNextStep(row_val, width, height, new_height) {
                if (this.tableMeta.map_icon_field_id) {
                    let new_width = width / height * new_height;
                    this.$root.sm_msg_type = 1;
                    axios.put('/ajax/table-data/field/map-icons', {
                        table_field_id: this.tableMeta.map_icon_field_id,
                        row_val: row_val,
                        height: Number(new_height),
                        width: Number(new_width) || Number(new_height),
                        special_params: SpecialFuncs.specialParams(),
                    }).then(({ data }) => {
                        this.colChanged();
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            deleteIcon(col, idx) {
                Swal({
                    title: 'Delete Icon',
                    text: 'Are you sure?',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value && this.tableMeta.map_icon_field_id) {
                        this.$root.sm_msg_type = 1;
                        axios.delete('/ajax/table-data/field/map-icons', {
                            params: {
                                table_field_id: this.tableMeta.map_icon_field_id,
                                row_val: col.row_val,
                                special_params: SpecialFuncs.specialParams(),
                            }
                        }).then(({ data }) => {
                            this.columnValues[idx].icon_path = '';
                            this.columnValues[idx].height = null;
                            this.columnValues[idx].width = null;
                            this.columnValues[idx].table_field_id = null;
                            this.colChanged();
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            this.$root.sm_msg_type = 0;
                        });
                    }
                });
            },

            //drag&drop to the cell directly
            iconStyle(idx) {
                if (idx == this.attach_overed) {
                    return {border: '4px dashed #F77'};
                }
                return {};
            },
            iconEnter(idx) {
                this.attach_overed = idx;
            },
            iconLeave(e, idx) {
                this.attach_overed = -1;
            },
            iconDrop(ev, idx) {
                let file = ev.dataTransfer.items && ev.dataTransfer.items[0] && ev.dataTransfer.items[0].kind === 'file'
                    ? ev.dataTransfer.items[0].getAsFile()
                    : null;
                this.uploadIcon(idx, file);
                this.attach_overed = -1;
            },
        },
        mounted() {
            if (this.tableMeta.map_icon_field_id) {
                this.colChanged();
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "../SettingsModule/TabSettingsPermissions";
    @import "../../../../CustomTable/CustomTable";

    .permissions-tab {
        .permissions-panel {
            height: calc(100% - 38px);
            background-color: inherit;
            padding: 0;
        }
        .top-text {
            height: 38px;
            padding: 3px 10px;
            color: #555;
            font-size: 1.1em;
            background: linear-gradient(to bottom, #efeff4, #d6dadf);

            .form-control {
                padding: 3px 6px;
                height: 32px;
            }
        }
    }
    .fixed_control {
        width: 20%;
        display: inline-block;
    }
    .preview_icon {
        max-width: 300px;
        max-height: 100px;
    }
    .delete-icon-btn {
        font-size: 1.5em;
        cursor: pointer;
    }
</style>