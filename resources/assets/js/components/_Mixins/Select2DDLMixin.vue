<script>
    /**
     *  should be present:
     *
     *  this.$root.tableMeta: Object OR this.globalMeta: Object
     *  this.tableRow: Object
     *  this.tableHeader: Object
     *  this.rowIndex: Integer
     *  this.$root.user: Object
     *
     *  this.hideEdit()
     *  this.showEdit()
     *  this.updateValue()
     *  */
    import {SpecialFuncs} from './../../classes/SpecialFuncs';

    export default {
        data: function () {
            return {
                avail_ddl_items: [],
                ddl_cached: false,
                dateTemp: '',
            }
        },
        computed: {
            select2_header_field() {
                return this.table_header_field || this.tableHeader.field;
            }
        },
        methods: {
            changeField(val, no_ddl_updated) {
                if (typeof this.editValue !== 'undefined' && this.editValue !== val) {
                    this.editValue = val;
                    this.updateValue(no_ddl_updated);
                }
                else
                if (this.tableRow[this.select2_header_field] !== val) {
                    this.tableRow[this.select2_header_field] = val;
                    this.updateValue(no_ddl_updated);
                }
            },
            //edit Field is not present in new DDL options list
            listedField(all_ddl, no_ddl_updated) {
                if (typeof this.editValue !== 'undefined') {
                    if ($.inArray(this.editValue, all_ddl) === -1) {
                        this.editValue = null;
                        this.updateValue(no_ddl_updated);
                    }
                } else {
                    if ($.inArray(this.tableRow[this.select2_header_field], all_ddl) === -1) {
                        this.tableRow[this.select2_header_field] = null;
                        this.updateValue(no_ddl_updated);
                    }
                }
            },
            
            // show/hide select2
            showHideDDLs(settings) {
                settings.height = this.cellHeight ? this.cellHeight+'px' : '100%';

                if (this.$refs.inline_input) {
                    //activate embed buttons
                    settings.templateResult = function(item) {
                        if ($(item.element).data('is-embed-btn')) {
                            return $('<button class="btn btn-xs btn-success select2__embed-btn">'+item.text+'</button>');
                        } else {
                            return item.text;
                        }
                    };

                    //create select2
                    $(this.$refs.inline_input).select2(settings)
                        .on('select2:open', (e) => {
                            this.correctEmbedBtnsPos();
                        }).on('select2:selecting', (e) => {
                            let item = this.getEventItemData(e);
                            if (item['is_embed_btn']) {
                                let fn = item['click_fn'];
                                (this[fn])();
                                $(this.$refs.inline_input).select2('close');
                                return false;
                            }
                        }).on('change', (e) => {
                            this.changeField($(this.$refs.inline_input).val());
                        }).on('select2:close', (e) => {
                            if (this.$refs.inline_input && $(this.$refs.inline_input).hasClass('select2-hidden-accessible')) {
                                $(this.$refs.inline_input).select2('destroy');
                            }
                            this.hideEdit();
                        });

                    if (settings && settings.height) {
                        let cont = $(this.$refs.inline_input).data('select2').$container;
                        $(cont).css('height', settings.height);
                    }

                    this.$nextTick(() => {
                        $('.select2-search__field').on('input', (e) => {
                            this.correctEmbedBtnsPos();
                        });
                    });

                    $(this.$refs.inline_input).select2('focus');
                    $(this.$refs.inline_input).select2('open');
                }
            },
            correctEmbedBtnsPos() {
                setTimeout(() => {
                    let embeds = $('.select2-container .select2__embed-btn');
                    _.each(embeds, (item) => {
                        $(item).parent().addClass('select2__embed-wrapper');
                    });
                }, 1);
            },
            showHideDatePicker(noformat) {
                if (this.$refs.inline_input) {
                    let defDate = this.editValue || null;
                    let format = '';
                    switch (this.tableHeader.f_type) {
                        case 'Date':
                            format = SpecialFuncs.dateFormat();
                            break;
                        case 'Date Time':
                            format = SpecialFuncs.dateTimeFormat();
                            break;
                        case 'Time':
                            format = SpecialFuncs.timeFormat();
                            break;
                    }

                    if (SpecialFuncs.isSpecialVar(defDate)) {
                        defDate = null;
                    } else {
                        if (!noformat) {
                            defDate = SpecialFuncs.format(this.tableHeader, defDate);
                        }
                        if (this.tableHeader.f_type === 'Time') {
                            defDate = defDate ? '0001-01-01 ' + defDate : '';
                        }
                    }
                    $(this.$refs.inline_input).datetimepicker({
                        fixedPositioned: true,
                        useCurrent: false,
                        defaultDate: defDate || null,
                        format: format,
                    }).on("dp.hide", (e) => {
                        if (this.$refs.inline_input) {
                            $(this.$refs.inline_input).datetimepicker('destroy');
                        }
                    }).on("input", (e) => {
                        let val = $(this.$refs.inline_input).val();
                        this.dateTemp = val;
                        if (String(val).match(/am|pm/gi)) {
                            $(this.$refs.inline_input).val( moment( val ).format('YYYY-MM-DD HH:mm:ss') );
                        }
                    });
                    $(this.$refs.inline_input).focus();
                }
            },
            showHideUserPicker(can_group) {
                if (this.$refs.inline_input) {
                    $(this.$refs.inline_input).select2({
                        ajax: {
                            url: can_group ? '/ajax/user/search-can-group' : '/ajax/user/search',
                            dataType: 'json',
                            delay: 250,
                            data: (params) => {
                                let meta = this.getMetaCalc();
                                return {
                                    q: params.term,
                                    table_id: can_group ? meta.id : null
                                }
                            },
                        },
                        minimumInputLength: {val:3},
                        width: '150%',
                        height: '100%',
                    }).on('change', (e) => {
                        let data = $(this.$refs.inline_input).select2('data');
                        this.changeField(data[0].id);
                    }).on('select2:close', (e) => {
                        if (this.$refs.inline_input && $(this.$refs.inline_input).hasClass('select2-hidden-accessible')) {
                            $(this.$refs.inline_input).select2('destroy');
                        }
                        this.hideEdit();
                    });
                    $(this.$refs.inline_input).select2('open');
                }
            },
            // ^^^^^^^
            
            getEventItemData(e) {
                let item = e.params.data || e.params.args.data;
                return {
                    is_embed_btn: $(item.element).data('is-embed-btn'),
                    click_fn: $(item.element).data('click-fn')
                };
            },
            getMetaCalc() {
                if (this.linkTableMeta !== undefined) {
                    return this.linkTableMeta;
                } else
                if (this.globalMeta !== undefined) {
                    return this.globalMeta;
                } else {
                    return this.$root.tableMeta;
                }
            },
        }
    }
</script>