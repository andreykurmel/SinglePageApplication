<template>
    <div>
        <div class="flex flex--center-v no-wrap form-group">
            <label>Select a Base:&nbsp;</label>
            <select class="form-control w w-long" v-model="user_api_obj" @change="singleChanged('base')">
                <option v-for="air in $root.user._airtable_api_keys" :value="air">{{ air.name }}</option>
            </select>
        </div>
        <template v-if="just_one">
            <div class="flex flex--center-v no-wrap form-group">
                <label>Table name:&nbsp;</label>
                <input class="form-control" v-model="single_table_name" @blur="singleChanged('table')">
            </div>
        </template>
        <template v-else>
            <div class="flex flex--center-v no-wrap form-group">
                <label>Enter the name of a "master" table saving all tables to be imported in the Base:&nbsp;</label>
                <input class="form-control" :disabled="!user_api_obj" v-model="master_table_name" @change="loadFields">
            </div>
            <div class="flex flex--center-v no-wrap form-group" v-show="!table_names_string">
                <label>Select the field saving the names of tables to be imported:&nbsp;</label>
                <select class="form-control w w-long" :disabled="!master_table_fields.length" v-model="master_field" @change="loadColumnValues">
                    <option v-for="fld in master_table_fields" :value="fld">{{ fld }}</option>
                </select>
            </div>
            <div class="flex flex--center-v no-wrap form-group" v-show="!master_field">
                <label>
                    <span>Enter names of tables to be imported, separted by semi-colons:&nbsp;</span><br>
                </label>
                <textarea class="form-control"
                          :disabled="!user_api_obj"
                          rows="3"
                          v-model="table_names_string"
                          placeholder="Separate table names by semi-color, space or comma, or put each of them in a new line."
                          @change="parseNames"
                ></textarea>
            </div>
        </template>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import {ImportHelper} from "../../classes/helpers/ImportHelper";

    import FileUploaderBlock from "./FileUploaderBlock";

    export default {
        name: 'AirtableImportBlock',
        mixins: [
        ],
        components: {
            FileUploaderBlock,
        },
        data() {
            return {
                user_api_obj: null,
                single_table_name: '',

                master_table_name: '',
                master_table_fields: [],
                master_field: '',
                table_names_string: '',
                multiple_table_names: [],
            }
        },
        props: {
            just_one: Boolean,
            import_action: String,
            some_presets: Object,
        },
        methods: {
            clearDatas(type) {
                if (type == 'base') {
                    this.single_table_name = '';
                    this.master_table_name = '';
                }
                this.master_table_fields = [];
                this.master_field = '';
                this.table_names_string = '';
                this.multiple_table_names = [];
            },
            emitProps() {
                this.$emit('props-changed', this.$data);
            },

            //SINGLE
            singleChanged(type) {
                if (!this.import_action) {
                    Swal('Please select an option.');
                    return;
                }
                this.clearDatas(type);
                this.emitProps();
            },

            //FOLDER
            loadFields() {
                if (!this.import_action) {
                    Swal('Please select an option.');
                    return;
                }
                this.master_table_fields = [];
                if (this.user_api_obj.id && this.master_table_name) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/airtable', {
                        user_key_id: this.user_api_obj.id,
                        table_name: this.master_table_name,
                        fromtype: 'folder/master',
                    }).then(({ data }) => {
                        this.master_table_fields = data.fields;
                        this.emitProps();
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    this.singleChanged();
                }
            },
            loadColumnValues() {
                if (!this.import_action) {
                    Swal('Please select an option.');
                    return;
                }
                this.table_names_string = '';
                this.multiple_table_names = [];
                if (this.user_api_obj.id && this.master_table_name && this.master_field) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/airtable/col-values', {
                        user_key_id: this.user_api_obj.id,
                        table_name: this.master_table_name,
                        col_name: this.master_field,
                        fromtype: 'single',
                    }).then(({ data }) => {
                        _.each(data, (table) => {
                            this.multiple_table_names.push(table);
                        });
                        this.emitNamesArray();
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Base or Table name are empty!', '', 'info');
                }
            },
            parseNames() {
                this.master_field = '';
                this.multiple_table_names = String(this.table_names_string)
                    .split(/[;,\n]/i)
                    .map((el) => {
                        return _.trim(el)
                    })
                    .filter((el) => { return !!el; });

                this.table_names_string = this.multiple_table_names.join('; ');
                this.emitNamesArray();
            },
            emitNamesArray() {
                this.emitProps();
                this.$emit('multiple-names-parsed', this.user_api_obj.id, this.multiple_table_names);
            },
        },
        mounted() {
            if (this.some_presets) {
                if (this.some_presets.base_id) {
                    this.user_api_obj = _.find(this.$root.user._airtable_api_keys, {id: Number(this.some_presets.base_id)});
                }
                if (this.some_presets.single_table) {
                    this.single_table_name = this.some_presets.single_table;
                    this.singleChanged('table');
                }
                if (this.some_presets.master_table) {
                    this.master_table_name = this.some_presets.master_table;
                    this.loadFields();
                }
                if (this.some_presets.master_field) {
                    this.master_field = this.some_presets.master_field;
                    this.loadColumnValues();
                }
                if (this.some_presets.table_names) {
                    this.table_names_string = this.some_presets.table_names;
                    this.parseNames();
                }
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    label {
        margin: 0;
        white-space: normal;
        width: 60%;
    }
    .notavail {
        cursor: not-allowed !important;
        z-index: 100;
    }
    .noter {
        font-size: 1rem;
        color: #aaa;
    }
</style>