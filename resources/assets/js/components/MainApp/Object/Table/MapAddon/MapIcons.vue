<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <div class="top-text" style="height: 50px;">
                <span>Map marker icon association</span>
                <span>&nbsp;&nbsp;&nbsp;Style:</span>
                <select v-model="tableMeta.map_icon_style" class="form-control fixed_control" @change="colChanged()">
                    <option value="dist">Distinctive</option>
                    <option value="comp">Complete</option>
                </select>
                <span>&nbsp;&nbsp;&nbsp;Field for icons:</span>
                <select v-model="tableMeta.map_icon_field_id" class="form-control fixed_control" @change="colChanged()">
                    <option></option>
                    <option v-for="(fld, idx) in mapFields" :value="fld.id">{{ $root.uniqName(fld.name) }}</option>
                </select>
            </div>
            <div class="permissions-panel">
                <div v-if="tableMeta.map_icon_style == 'dist'" class="full-frame custom-table-wrapper">
                    <table class="full-width">
                        <thead class="table-head">
                        <tr>
                            <th>Field Value</th>
                            <th>Icon</th>
                            <th>Height</th>
                            <th>Upload</th>
                        </tr>
                        </thead>
                        <tbody class="table-body">
                        <tr v-for="(col, idx) in columnValues">
                            <td>{{col.row_val}}</td>
                            <td>
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
    import CustomTable from '../../../../CustomTable/CustomTable';
    import HeightIconInput from '../../../../Buttons/HeightIconInput';

    export default {
        name: "MapIcons",
        components: {
            CustomTable,
            HeightIconInput
        },
        data: function () {
            return {
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                selectedFile: null,
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            table_id: Number|null,
            user:  Object,
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
            uploadIcon(col_idx) {
                let data = new FormData();
                let file = this.$refs['upload_'+col_idx][0].files[0];
                data.append('table_field_id', this.iconFld.id);
                data.append('row_val', this.columnValues[col_idx].row_val);
                data.append('file', file);
                data.append('height', null);
                data.append('width', null);

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
                                row_val: col.row_val
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
            height: calc(100% - 56px);
            background-color: inherit;
        }
        .top-text {
            padding: 10px;

            span {
                font-size: 1.2em;
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