<template>
    <div class="full-height">
        <import-fields-block
                v-if="tableHeaders[partKey] && tableHeaders[partKey].length && fieldsColumns.length && selectedType.key"
                :table-meta="tableMeta"
                :selected-type="selectedType"
                :table-headers="tableHeaders[partKey]"
                :can-get-access="true"
                :present-source="true"
                :fields-columns="fieldsColumns"
                :mysql-columns="[]"
        ></import-fields-block>
        <div v-else="" class="full-height flex flex--center">
            <label>{{ message }}</label>
        </div>
    </div>
</template>

<script>
    import DataImportMixin from "./../../../_Mixins/DataImportMixin.vue";

    import ImportFieldsBlock from "../../../CommonBlocks/ImportFieldsBlock";

    export default {
        name: "FolderImportPrepare",
        components: {
            ImportFieldsBlock,
        },
        mixins: [
            DataImportMixin,
        ],
        data: function () {
            return {
                selectedType: {
                    key: '',
                    special_sources: false,
                },
                fieldsColumns: [],
                mysqlColumns: [],
                importAction: 'new',
                message: 'Loading...',
            }
        },
        props: {
            tableMeta: Object,
            tableHeaders: Object,
            partKey: String,
            import_settings: Object,
            sheet_settings: Object,
        },
        computed: {
        },
        methods: {
        },
        mounted() {
            if (this.import_settings.filetype === 'sheet') {
                this.selectedType.key = 'g_sheet';
                this.g_sheets_settings = {f_header: this.sheet_settings.f_header};
                this.g_sheets_file = this.import_settings.filename;
                this.g_sheets_element = this.sheet_settings.name;
                this.getFieldsFromGSheet(this.tableHeaders, this.partKey, 'message');
            }

            if (this.import_settings.filetype === 'xml') {
                this.selectedType.key = 'web_scrap';

                this.web_xml_url = '';
                this.web_scrap_headers = 0;
                this.web_html_index = '';
                this.web_html_query = '';

                this.webAction = 'xml';
                this.web_xml_xpath = this.import_settings.xpath;
                this.web_xml_file = this.import_settings.filename;
                this.web_xml_nested = this.import_settings.xml_nested;
                this.web_scrap_xpath_query = true;
                this.getScrapWeb(this.tableHeaders, this.partKey, 'message');
            }

            if (this.import_settings.filetype === 'csv') {
                this.selectedType.key = 'csv';
                this.csv_link = this.import_settings.filename;
                this.csvSettings = {
                    firstHeader:this.sheet_settings.f_header,
                    extension: 'csv',
                };
                this.getFieldsFromCSV(this.tableHeaders, this.partKey, 'message');
            }

            if (this.import_settings.filetype === 'xls') {
                this.selectedType.key = 'csv';
                this.csv_link = this.import_settings.filename;
                this.csvSettings = {
                    firstHeader:this.sheet_settings.f_header,
                    xls_sheet:this.sheet_settings.name,
                    extension: 'xls',
                };
                this.getFieldsFromCSV(this.tableHeaders, this.partKey, 'message');
            }

            if (this.import_settings.source === 'table_ocr') {
                this.selectedType.key = 'table_ocr';
                this.getFieldsFromOCR(this.tableHeaders, this.partKey, {
                    sourceFile: this.sheet_settings.source_file,
                    firstHeader: this.sheet_settings.f_header,
                }, 'message');
            }

            if (this.import_settings.source === 'airtable_import') {
                this.selectedType.key = 'airtable_import';
                this.selectedType.special_sources = true;
                this.loadFieldsFromAirtable(this.tableHeaders, this.partKey, this.sheet_settings.airtable_data, 'message');
            }
        }
    }
</script>

<style lang="scss" scoped>
</style>