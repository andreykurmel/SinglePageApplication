<script>
    /**
     *  should be present:
     *  */
    export default {
        data: function () {
            return {
                paste_lines: 0,
                paste_chars: 0,
                paste_row_limit: 1000,
                paste_char_limit: 50000,

                paste_data: '',
                paste_settings: {
                    f_header: true,
                    replace_values: null,
                },
                paste_file: null,
                alert_note: '',
            }
        },
        computed: {
        },
        methods: {
            pasteFieldsFromBackend() {
                return new Promise((resolve) => {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/import/get-fields/paste', {
                        paste_data: this.paste_data,
                        paste_settings: this.paste_settings,
                    }).then(({ data }) => {
                        this.paste_file = data.file_hash;
                        resolve(data);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                });
            },
            onPaste(inputText) {
                this.alert_note = '';
                window.event.preventDefault();
                var cb;
                var clipText = '';
                if (inputText !== undefined) {
                    clipText = inputText;
                } else if (window.clipboardData && window.clipboardData.getData) {
                    cb = window.clipboardData;
                    clipText = cb.getData('Text');
                } else if (window.event.clipboardData && window.event.clipboardData.getData) {
                    cb = window.event.clipboardData;
                    clipText = cb.getData('text/plain');
                } else {
                    cb = window.event.originalEvent.clipboardData;
                    clipText = cb.getData('text/plain');
                }

                this.paste_chars = clipText.length;
                if (clipText.length > this.paste_char_limit) {
                    this.alert_note = 'More than '+this.paste_char_limit+' characters are pasted. Only the first '+this.paste_char_limit+' will be parsed and imported. Using other importing method is recommended.';
                    clipText = clipText.substr(0, this.paste_char_limit);
                }

                //ignore \n between "..."
                let canrem = false;
                clipText = String(clipText)
                    .split('')
                    .filter((el) => {
                        if(el == '\n' && canrem) { return false; }
                        if(el == '"') { canrem = !canrem; }
                        return true;
                    })
                    .join('');

                clipText = clipText ? clipText.replace(/["\r]/gi, '') : '';

                let clipRows = clipText.split('\n');

                this.paste_lines = clipRows.length;
                if (clipRows.length > this.paste_row_limit) {
                    this.alert_note = 'More than '+this.paste_row_limit+' lines are pasted. Only the first '+this.paste_row_limit+' will be parsed and imported. Using other importing method is recommended.';
                    clipRows = clipRows.slice(0, this.paste_row_limit);
                }

                //remove last row
                if (this.paste_chars > this.paste_char_limit) {
                    clipRows = clipRows.slice(0, clipRows.length-1);
                }

                //clear if called from input action.
                if (inputText !== undefined) {
                    this.paste_data = '';
                }

                if (/\t/.test(clipText)) {
                    for (let i = 0; i < clipRows.length; i++) {
                        clipRows[i] = clipRows[i].split('\t');

                        if (clipRows[i].length < 2) {
                            continue;
                        }

                        for (let j = 0; j < clipRows[i].length; j++) {
                            clipRows[i][j] = clipRows[i][j].replace(/["\r]/gi, '');
                            clipRows[i][j] = '"'+ (clipRows[i][j] || ' ') +'"';
                        }
                        clipRows[i] = clipRows[i].join('\t');
                    }
                    this.paste_data += clipRows.join('\n');
                } else {
                    for (let i = 0; i < clipRows.length; i++) {
                        clipRows[i] = '"'+ String(clipRows[i]).split(',').join('","') +'"';
                    }
                    this.paste_data += clipRows.join('\n');
                }
            },
            onPasteChange(e) {
                this.onPaste(e.target.value || '');
            },
        },
    }
</script>