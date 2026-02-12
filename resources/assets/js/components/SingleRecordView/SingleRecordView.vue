<template>
    <div v-if="srvRecord" id="tables" class="full-frame flex" :style="globalStyle()">
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
        
        <div v-if="initObject.single_view_password_id && !pass">

            <single-rec-pass-block
                :table_id="initObject.id"
                :row_id="srvRecord.id"
                @correct-pass="() => { pass=true }"
                @cancel-pass="goHomepage"
            ></single-rec-pass-block>

        </div>
        <div v-else-if="isAvail()">

            <div :style="srvHeaderStyle()" class="flex">
                <div v-if="tableMeta.single_view_header">
                    {{ getFrmHeader() }}
                </div>
                <div v-else>
                    {{ tableMeta.name }} <span v-html="getPopUpHeader()"></span>
                </div>
            </div>
            <div v-for="(availGr, idx) in srvGroups" class="srv-section">
                <div v-if="Object.keys(availGr.fieldTabs).length > 1" class="popup-menu">
                    <button
                        v-for="(tab, key) in availGr.fieldTabs"
                        class="btn btn-default mr5"
                        :class="{active: availGr.activetab === key}"
                        @click="availGr.activetab = key"
                    >
                        {{ key }}
                    </button>
                </div>
                <div :class="{'popup-tab': Object.keys(availGr.fieldTabs).length > 1}"
                     v-for="(tab, key) in availGr.fieldTabs"
                     v-show="availGr.activetab === key"
                >
                    <vertical-table
                        :td="$root.tdCellComponent(tableMeta.is_system)"
                        :global-meta="tableMeta"
                        :table-meta="tableMeta"
                        :settings-meta="$root.settingsMeta"
                        :table-row="srvRecord"
                        :cell-height="$root.cellHeight"
                        :max-cell-rows="$root.maxCellRows"
                        :user="$root.user"
                        :with_edit="canEdit()"
                        :behavior="'single-record-view'"
                        :extra-pivot-fields="tableMeta._fields_pivot"
                        :style="vertTbStyle(Object.keys(availGr.fieldTabs).length > 1)"
                        :available-columns="tab.fields"
                        @updated-cell="updateRow"
                        @show-src-record="showSrcRecord"
                    ></vertical-table>
                </div>
            </div>

            <!--Link Popups from ListView and MapView.-->
            <template v-for="(linkObj, idx) in linkPopups">
                <header-history-pop-up
                    v-if="linkObj.key === 'show' && linkObj.link.link_type === 'History'"
                    :idx="linkObj.index"
                    :table-meta="tableMeta"
                    :table-row="linkObj.row"
                    :history-header="linkObj.header"
                    :link="linkObj.link"
                    :popup-key="idx"
                    :is-visible="true"
                    @popup-close="closeLinkPopup"
                ></header-history-pop-up>
                <link-pop-up
                    v-else-if="linkObj.key === 'show'"
                    :source-meta="tableMeta"
                    :idx="linkObj.index"
                    :link="linkObj.link"
                    :meta-header="linkObj.header"
                    :meta-row="linkObj.row"
                    :popup-key="idx"
                    :view_authorizer="{
                        mrv_marker: $root.is_mrv_page,
                        srv_marker: $root.is_srv_page,
                        dcr_marker: $root.is_dcr_page,
                        srv_hash: srvRecord.static_hash
                    }"
                    @show-src-record="showSrcRecord"
                    @link-popup-close="closeLinkPopup"
                ></link-pop-up>
            </template>

            <!--Edit Popup for 'Email','Phone Number'-->
            <cell-email-phone-popup :table-meta="tableMeta"></cell-email-phone-popup>

        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import {JsFomulaParser} from "../../classes/JsFomulaParser";
    import {RefCondHelper} from "../../classes/helpers/RefCondHelper";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import CellStyleMixin from "../_Mixins/CellStyleMixin";
    import SortFieldsForVerticalMixin from "../_Mixins/SortFieldsForVerticalMixin";

    import SingleRecPassBlock from "../CommonBlocks/SingleRecPassBlock";
    import HeaderHistoryPopUp from "../CustomPopup/HeaderHistoryPopUp";
    import CellEmailPhonePopup from "../CustomPopup/CellEmailPhonePopup.vue";

    export default {
        name: "SingleRecordView",
        mixins: [
            CellStyleMixin,
            SortFieldsForVerticalMixin,
        ],
        components: {
            CellEmailPhonePopup,
            HeaderHistoryPopUp,
            SingleRecPassBlock,
        },
        data: function () {
            return {
                srvRecord: null,
                tableMeta: {},
                pass: false,
                linkPopups: [],
                srvGroups: [],
            }
        },
        props: {
            initObject: Object,
            settingsMeta: Object,
        },
        computed: {
            fullBgColor() {
                return this.tableMeta.single_view_background_by === 'color' ? this.tableMeta.single_view_bg_color : null;
            },
            fullBgImage() {
                return this.tableMeta.single_view_background_by === 'image' ? this.tableMeta.single_view_bg_img : null;
            },
        },
        methods: {
            vertTbStyle(withTabs) {
                let maxWidth = Number(this.tableMeta.single_view_form_width || '800');
                if (withTabs) {
                    maxWidth -= 12;
                }

                let styles = {
                    margin: 'auto',
                    paddingLeft: '10px',
                    paddingRight: '10px',
                    paddingTop: '5px',
                };
                styles.width = '100%';
                styles.maxWidth = maxWidth+'px';
                styles.backgroundColor = this.tableMeta.single_view_form_background;

                if (styles.backgroundColor) {
                    let transp = to_float(this.tableMeta.single_view_form_transparency || 0) / 100 * 255;
                    transp = Math.ceil(transp);
                    transp = Math.max(Math.min(transp, 255), 0);
                    styles.backgroundColor += Number(255 - transp).toString(16);
                }

                return styles;
            },
            srvHeaderStyle() {
                let bg = this.tableMeta.single_view_header_background || '#444444';
                let stl = {
                    margin: 'auto',
                    padding: '10px',
                    background: bg,
                    width: '100%',
                    maxWidth: this.tableMeta.single_view_form_width ? this.tableMeta.single_view_form_width+'px' : '800px',
                    position: 'relative',
                    fontWeight: 'bold',
                    fontSize: (Number(this.tableMeta.single_view_header_font_size) || Number(this.themeTextFontSize+2)) + 'px',
                    color: this.tableMeta.single_view_header_color || SpecialFuncs.smartTextColorOnBg(bg),
                    justifyContent: this.tableMeta.single_view_header_align_h === 'Right'
                        ? 'right'
                        : (this.tableMeta.single_view_header_align_h === 'Center' ? 'center' : 'left'),
                    alignItems: this.tableMeta.single_view_header_align_v === 'Top'
                        ? 'start'
                        : (this.tableMeta.single_view_header_align_v === 'Bottom' ? 'end' : 'center'),
                    height: Number(this.tableMeta.single_view_header_height || 100)+'px',
                    fontStyle: null,
                    textDecoration: null,
                };

                let fonts = SpecialFuncs.parseMsel(this.tableMeta.single_view_header_font);
                _.each(fonts, (f) => {
                    (f === 'Italic' ? stl.fontStyle = 'italic' : stl.fontStyle = stl.fontStyle || null);
                    (f === 'Bold' ? stl.fontWeight = 'bold' : stl.fontWeight = stl.fontWeight || null);
                    (f === 'Strikethrough' ? stl.textDecoration = 'line-through' : stl.textDecoration = stl.textDecoration || null);
                    (f === 'Overline' ? stl.textDecoration = 'overline' : stl.textDecoration = stl.textDecoration || null);
                    (f === 'Underline' ? stl.textDecoration = 'underline' : stl.textDecoration = stl.textDecoration || null);
                });

                return {
                    ...this.textStyle,
                    ...stl,
                };
            },
            globalStyle() {
                return {
                    backgroundColor: this.fullBgColor,
                    justifyContent: this.tableMeta.single_view_form_align_h === 'Right'
                        ? 'right'
                        : (this.tableMeta.single_view_form_align_h === 'Center' ? 'center' : 'left'),
                    alignItems: this.tableMeta.single_view_form_align_v === 'Top'
                        ? 'start'
                        : (this.tableMeta.single_view_form_align_v === 'Bottom' ? 'end' : 'center'),
                };
            },
            getFrmHeader() {
                if (this.tableMeta && this.srvRecord) {
                    let parser = new JsFomulaParser(this.tableMeta);
                    return parser.formulaEval(this.srvRecord, this.tableMeta.single_view_header, this.tableMeta);
                }
                return '';
            },
            isAvail() {
                let availStatus = false;
                if (this.tableMeta && this.srvRecord) {
                    let statusFld = _.find(this.tableMeta._fields, {id: Number(this.tableMeta.single_view_status_id)});
                    availStatus = !statusFld || this.srvRecord[statusFld.field];
                }
                return availStatus
                    && this.tableMeta
                    && this.tableMeta.single_view_active
                    && this.tableMeta.single_view_permission_id;
            },
            canEdit() {
                let canEdit = false;
                if (this.tableMeta && this.srvRecord) {
                    let statusFld = _.find(this.tableMeta._fields, {id: Number(this.tableMeta.single_view_edit_id)}) || {};
                    canEdit = ! this.tableMeta.single_view_edit_id || !! this.srvRecord[statusFld.field];
                }
                return canEdit;
            },
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
                    Swal('Info', getErrors(errors));
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
                    if (hdr.popup_header || hdr.popup_header_val) {
                        let row_value = row
                            ? SpecialFuncs.showhtml(hdr, row, row[hdr.field], this.tableMeta)
                            : '';
                        let ar = hdr.popup_header ? [this.$root.uniqName(hdr.name)] : [];
                        if (hdr.popup_header_val) { ar.push(row_value) }
                        res.push( ar.join(': ') );
                    }
                });
                return res.length ? ' | '+res.join(' | ') : '';
            },
            //LOADING
            loadSrv(withMeta) {
                axios.get('/ajax/srv/row', {
                    params: {
                        table_id: this.initObject.id,
                        srv_hash: location.hash,
                    },
                }).then(({ data }) => {
                    this.srvRecord = data.srv;
                    this.$root.user._srv_hash = this.srvRecord.static_hash;
                    this.$root.is_srv_page = this.srvRecord.id;
                    if (withMeta) {
                        this.loadMeta();
                        console.log('srvRecord', this.srvRecord, 'size about: ', JSON.stringify(this.srvRecord).length);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                    console.log('srvTableMeta', this.tableMeta, 'size about: ', JSON.stringify(this.tableMeta).length);

                    if (!this.isAvail()) {
                        Swal({
                            title: 'Info',
                            text: 'The requested Single-Record View(SRV) is not available!',
                            animation: 'slide-from-top'
                        }).then(() => {
                            this.goHomepage();
                        });
                    }
                    this.srvGroups = this.getSectionGroups(this.tableMeta, this.srvRecord, this.tableMeta._fields_pivot, [], true, 'srv');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
            intervalTickHandler(e) {
                if (this.srvRecord && this.$root.tableMeta && !this.$root.sm_msg_type) {
                    axios.post('/ajax/table/version_hash', {
                        table_id: this.$root.tableMeta.id,
                        row_list_ids: [],
                        row_fav_ids: [],
                        automations_check: !document.hidden,
                    }).then(({ data }) => {
                        this.loadSrv();
                    });
                }
            },
        },
        mounted() {
            this.loadSrv(true);

            //sync datas with collaborators
            setInterval(() => {
                if (!localStorage.getItem('no_ping')) {
                    this.intervalTickHandler();
                }
            }, this.$root.version_hash_delay);
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "../CustomPopup/CustomEditPopUp";

    .srv__image {
        max-width: 100%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .srv-section {
        margin-bottom: 5px;
    }
</style>