<script>
    /**
     *
     */
    export default {
        data: function () {
            return {
                requestFields: null,
                avail_fonts: [
                    'Arial', 'Calibri', 'Courier New', 'Helvetica', 'Times New Roman'
                ],
                rrm_avail_fields: [
                    'dcr_sec_line_top','dcr_sec_line_bot','dcr_sec_line_color','dcr_sec_line_thick','dcr_sec_bg_top',
                    'dcr_sec_bg_bot','dcr_sec_bg_img','dcr_sec_bg_img_fit','dcr_sec_scroll_style','dcr_sec_background_by',
                    'dcr_form_line_height','dcr_form_font_size',

                    'dcr_title','dcr_title_width','dcr_title_height','dcr_title_bg_color','dcr_title_font_type',
                    'dcr_title_font_size','dcr_title_font_color','dcr_title_bg_img','dcr_title_bg_fit',
                    'dcr_title_font_style','dcr_title_background_by',

                    'dcr_form_line_top','dcr_form_line_bot','dcr_form_line_thick','dcr_form_line_color','dcr_form_line_radius',
                    'dcr_form_message','dcr_form_message_font','dcr_form_message_size','dcr_form_message_color',
                    'dcr_form_message_style','dcr_form_width','dcr_form_shadow','dcr_form_shadow_color',
                    'dcr_form_line_type','dcr_form_shadow_dir','dcr_form_bg_color','dcr_form_transparency',

                    'dcr_confirm_msg','dcr_email_field_id','dcr_cc_email_field_id','dcr_bcc_email_field_id','dcr_email_subject','dcr_addressee_txt',
                    'dcr_email_message','dcr_email_format','dcr_email_col_group_id','dcr_email_field_static','dcr_cc_email_field_static','dcr_bcc_email_field_static',

                    'dcr_save_confirm_msg','dcr_save_email_field_id','dcr_save_cc_email_field_id','dcr_save_bcc_email_field_id','dcr_save_email_subject','dcr_save_addressee_txt',
                    'dcr_save_email_message','dcr_save_email_format','dcr_save_email_col_group_id','dcr_save_email_field_static','dcr_save_cc_email_field_static','dcr_save_bcc_email_field_static',

                    'dcr_upd_confirm_msg','dcr_upd_email_field_id','dcr_upd_cc_email_field_id','dcr_upd_bcc_email_field_id','dcr_upd_email_subject','dcr_upd_addressee_txt',
                    'dcr_upd_email_message','dcr_upd_email_format','dcr_upd_email_col_group_id','dcr_upd_email_field_static','dcr_upd_cc_email_field_static','dcr_upd_bcc_email_field_static',

                    'dcr_unique_msg','dcr_save_unique_msg','dcr_upd_unique_msg',
                    'one_per_submission','dcr_record_url_field_id','dcr_record_allow_unfinished',
                    'dcr_record_visibility_id','dcr_record_editability_id',
                    'dcr_record_status_id','dcr_record_visibility_def','dcr_record_editability_def',
                    'dcr_record_save_visibility_def','dcr_record_save_editability_def','stored_row_protection','stored_row_pass_id',
                ],

                sec_tooltip_bgc: false,
                sec_bgi_tooltip: false,
                tit_tooltip_bgc: false,
                tit_bgi_tooltip: false,
                form_tooltip_bgc: false,

                help_left: 0,
                help_top: 0,
                help_offset: 0,

                formula_dcr_unique_msg: false,
                formula_dcr_confirm_msg: false,
                formula_dcr_email_subject: false,
                formula_dcr_addressee_txt: false,
                formula_dcr_email_message: false,
            };
        },
        methods: {
            uploadFile() {
                this.$emit('upload-file', this.requestRow, 'dcr_title_bg_img', this.$refs.bg_img.files[0]);
                this.$refs.bg_img.value = null;
            },
            delFile() {
                this.$emit('del-file', this.requestRow, 'dcr_title_bg_img');
            },
            uploadSecFile() {
                this.$emit('upload-file', this.requestRow, 'dcr_sec_bg_img', this.$refs.bg_img_sec.files[0]);
                this.$refs.bg_img.value = null;
            },
            delSecFile() {
                this.$emit('del-file', this.requestRow, 'dcr_sec_bg_img');
            },
            updatedCell() {
                this.requestRow['dcr_title'] = this.$root.strip_tags(this.requestRow['dcr_title']);
                this.requestRow['dcr_form_message'] = this.$root.strip_danger_tags(this.requestRow['dcr_form_message']);
                this.$emit('updated-cell', this.requestRow);
                this.formula_dcr_unique_msg = false;
                this.formula_dcr_confirm_msg = false;
                this.formula_dcr_email_subject = false;
                this.formula_dcr_addressee_txt = false;
                this.formula_dcr_email_message = false;
            },
            recreateFormul(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
            updateSecColorLine(clr, save) {
                this.updateColor('dcr_sec_line_color', clr, save);
            },
            updateSecBotColor(clr, save) {
                this.updateColor('dcr_sec_bg_bot', clr, save);
            },
            updateSecTopColor(clr, save) {
                this.updateColor('dcr_sec_bg_top', clr, save);
            },
            updateColorFont(clr, save) {
                this.updateColor('dcr_title_font_color', clr, save);
            },
            updateColorBg(clr, save) {
                this.updateColor('dcr_title_bg_color', clr, save);
            },
            updateColorFormBg(clr, save) {
                this.updateColor('dcr_form_bg_color', clr, save);
            },
            updateShadowColor(clr, save) {
                this.updateColor('dcr_form_shadow_color', clr, save);
            },
            updateColorLine(clr, save) {
                this.updateColor('dcr_form_line_color', clr, save);
            },
            updateColorMsgFont(clr, save) {
                this.updateColor('dcr_form_message_color', clr, save);
            },
            updateColor(hdr, clr, save) {
                if (save) {
                    this.$root.saveColorToPalette(clr);
                }
                this.requestRow[hdr] = clr;
                this.updatedCell();
            },
            setAvailFields() {
                this.requestFields = null;
                this.$nextTick(() => {

                    this.requestFields = {};
                    _.each(this.rrm_avail_fields, (fld_key) => {
                        this.requestFields[fld_key] = _.find(this.tableRequest._fields, {field: fld_key}) || {};
                    });

                });
            },

            updateMSelect(item, field) {
                let editval = this.$root.parseMsel(this.requestRow[field]);
                if (editval.indexOf(item) > -1) {
                    editval.splice( editval.indexOf(item), 1 );
                } else {
                    editval.push(item);
                }
                this.requestRow[field] = JSON.stringify(editval);
                this.updatedCell();
            },

            //tooltips////
            showSecBGC(e) {
                this.showTooltip(e, 'sec_tooltip_bgc');
            },
            showBGISec(e) {
                this.showTooltip(e, 'sec_bgi_tooltip');
            },
            showTitBGC(e) {
                this.showTooltip(e, 'tit_tooltip_bgc');
            },
            showBGITit(e) {
                this.showTooltip(e, 'tit_bgi_tooltip');
            },
            showFormBGC(e) {
                this.showTooltip(e, 'form_tooltip_bgc');
            },
            showTooltip(e, key) {
                let bounds = this.$refs[key] ? this.$refs[key].getBoundingClientRect() : {};
                let px = (bounds.left + bounds.right) / 2;
                let py = (bounds.top + bounds.bottom) / 2;
                this[key] = true;
                this.help_left = px || e.clientX;
                this.help_top = py || e.clientY;
                this.help_offset = ( Math.abs(bounds.top - bounds.bottom) || 0 ) / 2;
            },
        },
    }
</script>