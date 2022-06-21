<template>
    <div v-if="srvRecord" id="tables" class="full-frame" :style="{backgroundColor: fullBgColor}">
        <img v-if="fullBgImage"
             class="srv__image"
             :src="$root.fileUrl({url:fullBgImage})"
             style="z-index: auto;position: fixed;"
             :style="{
                height: (['Height','Fill'].indexOf(tableMeta.single_view_bg_fit) > -1 ? '100%' : null),
                width: (['Width','Fill'].indexOf(tableMeta.single_view_bg_fit) > -1 ? '100%' : null),
                objectFit: (tableMeta.single_view_bg_fit === 'Fill' ? 'cover' : null),
             }"
        />
        
        <template v-if="initObject.single_view_password_id && !pass">

            <single-rec-pass-block
                :table_id="initObject.id"
                :row_id="srvRecord.id"
                @correct-pass="() => { pass=true }"
                @cancel-pass="goHomepage"
            ></single-rec-pass-block>

        </template>
        <template v-else-if="isAvail">

            <div :style="srvHeaderStyle">
                [{{ tableMeta.name }}] <span v-html="getPopUpHeader()"></span>
            </div>
            <vertical-table
                :td="$root.tdCellComponent(tableMeta.is_system)"
                :global-meta="tableMeta"
                :table-meta="tableMeta"
                :settings-meta="$root.settingsMeta"
                :table-row="srvRecord"
                :cell-height="$root.cellHeight"
                :max-cell-rows="$root.maxCellRows"
                :user="$root.user"
                :behavior="'single-record-view'"
                :style="vertTbStyle"
                @updated-cell="updateRow"
                @show-src-record="showSrcRecord"
            ></vertical-table>

            <!--Link Popups from ListView and MapView.-->
            <template v-for="(linkObj, idx) in linkPopups">
                <link-pop-up
                    v-if="linkObj.key === 'show'"
                    :idx="linkObj.index"
                    :settings-meta="$root.settingsMeta"
                    :user="$root.user"
                    :link="linkObj.link"
                    :meta-header="linkObj.header"
                    :meta-row="linkObj.row"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    :popup-key="idx"
                    @show-src-record="showSrcRecord"
                    @link-popup-close="closeLinkPopup"
                ></link-pop-up>
            </template>

        </template>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import {RefCondHelper} from "../../classes/helpers/RefCondHelper";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import VerticalTable from "../CustomTable/VerticalTable";
    import SingleRecPassBlock from "../CommonBlocks/SingleRecPassBlock";

    import CellStyleMixin from "../_Mixins/CellStyleMixin";
    import LinkPopUp from "../CustomPopup/LinkPopUp";

    export default {
        name: "SingleRecordView",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            LinkPopUp,
            SingleRecPassBlock,
            VerticalTable,
        },
        data: function () {
            return {
                srvRecord: null,
                tableMeta: {},
                pass: false,
                linkPopups: [],
            }
        },
        props: {
            initObject: Object,
            settingsMeta: Object,
        },
        computed: {
            isAvail() {
                return this.tableMeta && this.tableMeta.single_view_active && this.tableMeta.single_view_permission_id;
            },
            fullBgColor() {
                return this.tableMeta.single_view_background_by === 'color' ? this.tableMeta.single_view_bg_color : null;
            },
            fullBgImage() {
                return this.tableMeta.single_view_background_by === 'image' ? this.tableMeta.single_view_bg_img : null;
            },
            vertTbStyle() {
                let styles = {
                    margin: 'auto',
                    paddingLeft: '15px'
                };
                styles.width = this.tableMeta.single_view_form_width ? this.tableMeta.single_view_form_width+'px' : '800px';
                styles.backgroundColor = this.tableMeta.single_view_form_color;

                if (styles.backgroundColor) {
                    let transp = to_float(this.tableMeta.single_view_form_transparency || 0) / 100 * 255;
                    transp = Math.ceil(transp);
                    transp = Math.max(Math.min(transp, 255), 0);
                    styles.backgroundColor += Number(255 - transp).toString(16);
                }

                return styles;
            },
            srvHeaderStyle() {
                return {
                    margin: 'auto',
                    padding: '10px',
                    background: '#444',
                    color: '#FFF',
                    width: this.tableMeta.single_view_form_width ? this.tableMeta.single_view_form_width+'px' : '800px',
                    position: 'relative',
                    fontWeight: 'bold',
                    ...this.textStyle,
                };
            },
        },
        methods: {
            updateRow(tableRow) {
                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                //front-end RowGroups and CondFormats
                RefCondHelper.updateRGandCFtoRow(this.tableMeta, tableRow);

                this.$root.sm_msg_type = 1;
                this.$root.prevent_cell_edit = true;
                axios.put('/ajax/table-data', {
                    table_id: this.tableMeta.id,
                    row_id: row_id,
                    fields: fields,
                    get_query: {
                        table_id: this.tableMeta.id,
                        page: 1,
                        rows_per_page: 0,
                    },
                    special_params: {
                        srv_hash: this.srvRecord.static_hash,
                    },
                }).then(({ data }) => {
                    if (data.rows && data.rows.length) {
                        SpecialFuncs.assignProps(tableRow, data.rows[0]);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.$root.prevent_cell_edit = false;
                });
            },
            goHomepage() {
                window.location.href = '/';
            },
            getPopUpHeader() {
                let headers = this.tableMeta._fields;
                let row = this.srvRecord;
                let res = [];
                _.each(headers, (hdr) => {
                    if (hdr.popup_header) {
                        res.push('{' + this.$root.uniqName(hdr.name) + '}: ' + (row ? row[hdr.field] : '') );
                    }
                });
                return res.length ? ' - '+res.join('<br>') : '';
            },
            //LOADING
            loadSrv() {
                axios.get('/ajax/srv/row', {
                    params: {
                        table_id: this.initObject.id,
                        srv_hash: location.hash,
                    },
                }).then(({ data }) => {
                    this.srvRecord = data.srv;
                    this.loadMeta();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            loadMeta() {
                axios.post('/ajax/table-data/get-headers', {
                    table_id: this.initObject.id,
                    special_params: {
                        srv_hash: this.srvRecord.static_hash,
                    },
                }).then(({ data }) => {
                    this.$root.metaSrvObject = data;
                    this.$root.tableMeta = data;
                    this.tableMeta = data;

                    if (!this.isAvail) {
                        Swal({
                            title: 'Single-Record View not Available!',
                            animation: 'slide-from-top'
                        }).then(() => {
                            this.goHomepage();
                        });
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            //Link PopUps
            showSrcRecord(lnk, header, tableRow, behavior) {
                let index = this.linkPopups.filter((el) => {return el.key === 'show'}).length;
                this.linkPopups.push({
                    key: 'show',
                    index: index,
                    link: lnk,
                    header: header,
                    row: tableRow,
                    behavior: behavior,//['map','link','list_view']
                });
            },
            closeLinkPopup(idx, should_update) {
                if (idx > -1) {
                    this.linkPopups[idx].key = 'hide';
                    this.$forceUpdate();

                    if (should_update) {
                        eventBus.$emit('reload-page');
                    }
                }
            },
        },
        mounted() {
            console.log('srvRecord', this.srvRecord, 'size about: ', JSON.stringify(this.srvRecord).length);
            this.loadSrv();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    #tables {
        overflow: hidden;
    }
    .srv__image {
        max-width: 100%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
</style>