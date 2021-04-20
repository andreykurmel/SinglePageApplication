<script>
    /**
     *  should be present:
     *
     *  this.tablePermission: Object
     *  this.tableRow: Object - can be present
     *  */
    import {RequestFuncs} from "./RequestFuncs";

    export default {
        data: function () {
            return {}
        },
        computed: {
            allowUnfinish() {
                return this.tablePermission.dcr_record_url_field_id
                    && this.tablePermission.dcr_record_allow_unfinished;
            },
        },
        methods: {
            //row statuses
            isNew(tableRow) {
                return !Number(tableRow.id);
            },
            visibil(tableRow) {
                let visi_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.tablePermission, 'dcr_record_visibility_id');
                return this.isNew(tableRow)
                    ||
                    (visi_hdr ? !!tableRow[visi_hdr.field] : !!this.tablePermission.dcr_record_visibility_def);
            },
            editabil(tableRow) {
                let edit_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.tablePermission, 'dcr_record_editability_id');
                return this.isNew(tableRow)
                    ||
                    (edit_hdr ? !!tableRow[edit_hdr.field] : !!this.tablePermission.dcr_record_editability_def);
            },
            rowStatus(tableRow) {
                let status_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.tablePermission, 'dcr_record_status_id');
                return status_hdr ? tableRow[status_hdr.field] : '';
            },
            //buttons avails
            availSave(tableRow) {
                return this.allowUnfinish
                    &&
                    (
                        this.isNew(tableRow)
                        ||
                        (this.rowStatus(tableRow) === 'Saved' && this.visibil(tableRow) && this.editabil(tableRow))
                    );
            },
            availSubmit(tableRow) {
                return this.isNew(tableRow)
                    ||
                    (this.rowStatus(tableRow) === 'Saved' && this.visibil(tableRow));
            },
            availUpdate(tableRow) {
                return !this.isNew(tableRow)
                    && this.visibil(tableRow)
                    && this.editabil(tableRow)
                    && (this.rowStatus(tableRow) === 'Submitted' || this.rowStatus(tableRow) === 'Updated');
            },
            availAdd(tableRow) {
                return !this.tablePermission.one_per_submission
                    && (
                        this.isNew(tableRow)
                        ||
                        (this.visibil(tableRow) && this.editabil(tableRow))
                    );
            },
        },
    }
</script>