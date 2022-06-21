
export class ImportHelper {

    /**
     *
     * @param input
     * @returns {{name: string, show: string, f_header: (*|boolean), for_import: (*|boolean), source_file: (*|string), new_name: (*|string)}}
     */
    static importTemplate(input) {
        let obj = input || {};
        return {
            name: obj.name || '',
            show: obj.show || '',
            for_import: obj.for_import || true,
            f_header: obj.f_header || false,
            new_name: obj.new_name || '',
            source_file: obj.source_file || '',
            airtable_data: obj.airtable_data || {
                user_key_id: null,
                table_name: '',
            },
        };
    }
}