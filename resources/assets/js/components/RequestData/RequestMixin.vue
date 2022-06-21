<script>
    /**
     *  should be present:
     *
     *  this.dcrObject: Object
     *  this.tableRow: Object - can be present
     *  */
    import {RequestFuncs} from "./RequestFuncs";

    export default {
        data: function () {
            return {}
        },
        computed: {
            allowUnfinish() {
                return this.dcrObject.dcr_record_url_field_id
                    && this.dcrObject.dcr_record_allow_unfinished;
            },
        },
        methods: {
            //row statuses
            isNew(tableRow) {
                return !Number(tableRow.id);
            },
            visibil(tableRow) {
                let visi_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_visibility_id');
                let visible_def = this.rowStatus(tableRow) === 'Saved'
                    ? !!this.dcrObject.dcr_record_save_visibility_def
                    : !!this.dcrObject.dcr_record_visibility_def;

                return this.isNew(tableRow)
                    ||
                    (visi_hdr ? !!tableRow[visi_hdr.field] : visible_def);
            },
            editabil(tableRow) {
                let edit_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_editability_id');
                let editable_def = this.rowStatus(tableRow) === 'Saved'
                    ? !!this.dcrObject.dcr_record_save_editability_def
                    : !!this.dcrObject.dcr_record_editability_def;

                return this.isNew(tableRow)
                    ||
                    (edit_hdr ? !!tableRow[edit_hdr.field] : editable_def);
            },
            rowStatus(tableRow) {
                let status_hdr = RequestFuncs.recordUrlHeader(this.$root.tableMeta, this.dcrObject, 'dcr_record_status_id');
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
                return !this.dcrObject.one_per_submission
                    && (
                        this.isNew(tableRow)
                        ||
                        (this.visibil(tableRow) && this.editabil(tableRow))
                    );
            },
        },
    }
</script>