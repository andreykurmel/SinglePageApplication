<script>
    /**
     *  should be present:
     *  */
    export default {
        data: function () {
            return {
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
            onPaste() {
                window.event.preventDefault();
                var cb;
                var clipText = '';
                if (window.clipboardData && window.clipboardData.getData) {
                    cb = window.clipboardData;
                    clipText = cb.getData('Text');
                } else if (window.event.clipboardData && window.event.clipboardData.getData) {
                    cb = window.event.clipboardData;
                    clipText = cb.getData('text/plain');
                } else {
                    cb = window.event.originalEvent.clipboardData;
                    clipText = cb.getData('text/plain');
                }

                if (clipText.length > 50000) {
                    this.alert_note = 'More than 50000 symbols is pasted. Only the first 50000 will be parsed and imported. Using other importing method is recommended.';
                    clipText = clipText.substr(0, 50000);
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

                if (clipRows.length > 1000) {
                    this.alert_note = 'More than 1000 records is pasted. Only the first 1000 will be parsed and imported. Using other importing method is recommended.';
                    clipRows = clipRows.slice(0, 1000);
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
        },
    }
</script>