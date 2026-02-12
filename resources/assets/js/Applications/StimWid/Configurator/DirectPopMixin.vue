
<script>
    export default {
        data() {
            return {
                //direct popups
                popup_row_id: null,
                popup_app_tb: '',
                popup_type: '',
                link_rows: null,

                //add libs popup
                found_model_add: null,
                empty_object_add: null,
                stim_link_add: null,
            };
        },
        methods: {
            popupLibElem(category, row_id) {
                switch (category) {
                    case 'model': this.popupModel(row_id); break;
                    case 'eqpt_lib': this.popupEqptLib(row_id); break;
                    case 'feedline': this.popupFeedline(row_id); break;
                    case 'line_lib': this.popupLineLib(row_id); break;
                    case 'tech': this.popupTech(row_id); break;
                    case 'status': this.popupStatus(row_id); break;
                    case 'elev': this.popupElev(row_id); break;
                    case 'azimuth': this.popupAzimuth(row_id); break;
                }
                this.link_rows = this.vuex_fm[this.popup_app_tb] ? this.vuex_fm[this.popup_app_tb].rows : null;
            },
            popupEqpt(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.eqptdata_2d;
                this.popup_type = 'eqpt';
            },
            popupEqptSett(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.eqptsett_2d;
                this.popup_type = 'eqpt_sett';
            },
            popupModel(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.vuex_settings.popups_models.equipment;
                this.popup_type = 'model';
            },
            popupEqptLib(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.eqptlib_2d;
                this.popup_type = 'eqpt_lib';
            },
            popupLineLib(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.linelib_2d;
                this.popup_type = 'line_lib';
            },
            popupPos(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.pos_2d;
                this.popup_type = 'pos';
            },
            popupSector(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.sectors_2d;
                this.popup_type = 'sector';
            },
            popupTech(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.tech_2d;
                this.popup_type = 'tech';
            },
            popupStatus(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.status_2d;
                this.popup_type = 'status';
            },
            popupElev(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.elevs_2d;
                this.popup_type = 'elev';
            },
            popupAzimuth(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.azimuth_2d;
                this.popup_type = 'azimuth';
            },
            popupConn(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.popup_tables.linedata_2d;
                this.popup_type = 'line';
            },
            popupFeedline(row_id) {
                this.popup_row_id = row_id;
                this.popup_app_tb = this.vuex_settings.popups_models.feedline;
                this.popup_type = 'feedline';
            },

            //add lib popups
            openAddPopup(type) {
                let app_tb = '';
                switch (type) {
                    case 'eqpt': app_tb = this.popup_tables.eqptlib_2d;
                        break;
                    case 'line': app_tb = this.popup_tables.linelib_2d;
                        break;
                    case 'tech': app_tb = this.popup_tables.tech_2d;
                        break;
                    case 'status': app_tb = this.popup_tables.status_2d;
                        break;
                    case 'elev': app_tb = this.popup_tables.elevs_2d;
                        break;
                    case 'azimuth': app_tb = this.popup_tables.azimuth_2d;
                        break;
                }
                this.found_model_add = this.vuex_fm[app_tb];
                if (this.found_model_add) {
                    Promise.all([
                        this.found_model_add.meta.loadHeaders(),//check that Headers is loaded
                    ]).then(() => {
                        this.stim_link_add = this.vuex_links[app_tb];
                        this.empty_object_add = this.found_model_add.meta.emptyObject();
                        this.empty_object_add = this.stim_link_add.fillRowFromMaster(this.master_row, this.empty_object_add, true);
                    })
                }
            },
            addPopupInsert(tableRow) {
                this.$root.sm_msg_type = 1;
                this.found_model_add.rows.insertRow(tableRow).then((data) => {
                    this.load2D ? this.load2D() : null;
                    this.closeAddPopUp();
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            closeAddPopUp() {
                this.found_model_add = null;
                this.empty_object_add = null;
                this.stim_link_add = null;
            },
        },
    }
</script>