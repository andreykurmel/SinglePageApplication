<template>
    <div ref="dwn_button" class="download_main_button" title="Download Data" v-if="tableMeta">
        <button class="btn btn-default blue-gradient"
                @click="menu_opened = !menu_opened"
                :style="$root.themeButtonStyle"
        >
            <i class="glyphicon glyphicon-download-alt"></i>
        </button>
        <div v-show="menu_opened" class="download_menu">
            <button type="button"
                    class="btn btn-default download_btn"
                    :class="[isActive(0) ? 'active' : '']"
                    @click="download(0, 'PRINT')"
                    style="background-color: #a00;">Print</button>

            <button type="button"
                    class="btn btn-default download_btn"
                    :class="[isActive(1) ? 'active' : '']"
                    @click="download(1, 'CSV')"
                    style="background-color: #a00;">CSV</button>

            <button type="button"
                    class="btn btn-default download_btn"
                    :class="[isActive(2) ? 'active' : '']"
                    @click="download(2, 'PDF')"
                    style="background-color: #aa0;">PDF</button>

            <button type="button"
                    class="btn btn-default download_btn"
                    :class="[isActive(3) ? 'active' : '']"
                    @click="download(3, 'XLSX')"
                    style="background-color: #0a0;">XLSX</button>

            <button type="button"
                    class="btn btn-default download_btn"
                    :class="[isActive(4) ? 'active' : '']"
                    @click="download(4, 'JSON')"
                    style="background-color: #0aa;">JSON</button>

            <button type="button"
                    class="btn btn-default download_btn"
                    :class="[isActive(5) ? 'active' : '']"
                    @click="download(5, 'XML')"
                    style="background-color: #00a;">XML</button>

            <button type="button"
                    class="btn btn-default download_btn"
                    :class="[isActive(6) ? 'active' : '']"
                    @click="download(6, 'PNG')"
                    style="background-color: #a0a;">PNG</button>
        </div>
    </div>
</template>

<script>
    import {MetaTabldaRows} from './../../classes/MetaTabldaRows';

    import {eventBus} from './../../app';

    export default {
        name: "DownloadButton",
        mixins: [
        ],
        data: function () {
            return {
                menu_opened: false
            }
        },
        props:{
            tb_id: String,
            png_name: String,
            tableMeta: Object,
            searchObject: Object,
            allRows: MetaTabldaRows,
            listRows: Array,
        },
        methods: {
            download(key, method) {
                if (!this.isActive(key)) {
                    return;
                }

                if (method === 'PRINT') {
                    let rows = this.allRows ? this.allRows.all_rows : this.listRows;
                    eventBus.$emit('print-table-data', this.tableMeta._fields, rows, false);
                    return;
                }
                if (method === 'PNG') {
                    let elem = document.getElementById(this.tb_id);
                    export_to_png(elem, this.png_name || 'table');
                    return;
                }

                if (this.tableMeta._view_rows_count > 10000 && (method === 'PDF' || method === 'XLSX')) {
                    Swal('', 'Data has more than 10k rows. Only up to 10k rows of data can be exported at a time.')
                }

                $('#downloader_data').val( this.getRequestData() );
                $('#downloader_method').val(method);
                $('#downloader_time').val( moment().format('YYYY-MM-DD HH.mm.ss') );
                $('#downloader_form').submit();
            },
            getRequestData() {
                let request_params;
                if (this.allRows) {
                    request_params = this.allRows.rowsRequest();
                    request_params.page = 1;
                    request_params.rows_per_page = 0;
                } else {
                    request_params = {
                        table_id: this.tableMeta.id,
                        page: 1,
                        rows_per_page: 0,
                        search_words: this.searchObject.keyWords,
                        search_columns: this.searchObject.columns,
                        row_id: this.searchObject.direct_row_id || null,
                        applied_filters: this.$root.filters
                    };
                }
                return JSON.stringify(request_params);
            },
            hideMenu(e) {
                let container = $(this.$refs.dwn_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
            isActive(val) {
                return this.tableMeta._is_owner || this.tableMeta._current_right.can_download.charAt(val) === '1';
            }
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .download_main_button {
        cursor: pointer;
        width: 35px;
        height: 30px;
        padding: 0;
        background-color: transparent;
        position: relative;
        outline: none;

        button {
            width: 100%;
            height: 100%;
            padding: 3px 0 0 0;
        }

        .download_menu {
            position: absolute;
            top: 100%;
            right: 100%;
            display: flex;
            z-index: 500;
            padding: 5px;
            background-color: #FFF;
            border: 1px solid #CCC;

            .download_btn {
                border-radius: 50%;
                width: 40px;
                height: 40px;
                opacity: 0.1;
                color: #FFF;
                font-weight: bold;
                font-size: 0.8em;
                padding: 1px 0 0 0;
            }
            .active {
                opacity: 0.4;

                &:hover {
                    opacity: 0.9;
                }
            }
        }
    }
</style>