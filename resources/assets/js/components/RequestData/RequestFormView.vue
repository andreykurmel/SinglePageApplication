<template>
    <div class="flex flex--col full-height">
        <div v-if="dcrObject.dcr_form_line_top" :style="{
            borderBottom: (dcrObject.dcr_form_line_type == 'line' ? (dcrObject.dcr_form_line_thick || 1)+'px solid '+(dcrObject.dcr_form_line_color || '#d3e0e9') : null),
            marginBottom: (dcrObject.dcr_form_line_type == 'space' ? (dcrObject.dcr_form_line_thick || 1)+'px' : null),
        }"></div>

        <div v-if="scrlTabs" class="flex flex--col full-height" :style="{backgroundColor: frm_color}">
            <div class="flex flex--center-h" style="flex-wrap: wrap;">
                <div v-for="(avails, idx) in availGroups"
                     class="accordion_btn tab_btn flex flex--center-v"
                     @click="showAccTab(avails)"
                     :style="tabStyle()"
                     :class="{tab_btn_active: avails.showitem}"
                >{{ accTabItemName(avails) }}</div>
            </div>
            <div class="full-height" style="border-top: 1px solid #ccc; overflow: auto;">
                <template v-for="(avails, idx) in availGroups">
                    <request-form-view-element
                        v-show="avails.showitem"
                        :avail-gr="avails"
                        :user="user"
                        :settings-meta="settingsMeta"
                        :cell-height="cellHeight"
                        :dcr-object="dcrObject"
                        :dcr-linked-rows="dcrLinkedRows"
                        :table-row="tableRow"
                        :scrl-groups="scrlGroups"
                        :frm_color="frm_color"
                        :box_shad="box_shad"
                        :with_edit="with_edit"
                        @linked-rows-changed="checkRowAutocomplete"
                        @scroll-fields="scrollEvent"
                        @check-row-autocomplete="checkRowAutocomplete"
                        @show-src-record="showSrcRecord"
                        @show-add-ddl="showAddDDLOption"
                    ></request-form-view-element>
                </template>
            </div>
            <div v-if="dcrObject.dcr_form_line_bot" :style="{
                    borderBottom: (dcrObject.dcr_form_line_type == 'line' ? (dcrObject.dcr_form_line_thick || 1)+'px solid '+(dcrObject.dcr_form_line_color || '#d3e0e9') : null),
                    marginBottom: (dcrObject.dcr_form_line_type == 'space' ? (dcrObject.dcr_form_line_thick || 1)+'px' : null),
                }"></div>
            <request-form-buttons
                :clear-after-submis="clearAfterSubmis"
                :has-changes="hasChanges"
                :table-row="tableRow"
                :can-add-row="canAddRow"
                :available-adding="availableAdding"
                :dcr-object="dcrObject"
                :box_shad="box_shad"
                :frm_color="frm_color"
                @add-row="addRow"
                @store-rows-click="storeRows"
                @clear-submission-changed="clearSubmisClicked"
            ></request-form-buttons>
        </div>

        <div v-else-if="scrlAccordion" class="flex flex--col flex--space full-height" style="overflow: auto;">
            <div>
                <template v-for="(avails, idx) in availGroups">
                    <div class="accordion_btn flex flex--center-v mt3"
                         :style="tabStyle()"
                         @click="showAccTab(avails)"
                    >
                        <span style="width: 15px">{{ avails.showitem ? '-' : '+' }}</span>
                        <span>{{ accTabItemName(avails) }}</span>
                    </div>
                    <request-form-view-element
                        :avail-gr="avails"
                        :user="user"
                        :settings-meta="settingsMeta"
                        :cell-height="cellHeight"
                        :dcr-object="dcrObject"
                        :dcr-linked-rows="dcrLinkedRows"
                        :table-row="tableRow"
                        :scrl-groups="scrlGroups"
                        :frm_color="frm_color"
                        :box_shad="box_shad"
                        :with_edit="with_edit"
                        :style="{
                            maxHeight: avails.showitem ? '100%' : '0',
                            overflow: 'hidden',
                            transition: 'all 0.5s linear',
                        }"
                        @linked-rows-changed="checkRowAutocomplete"
                        @scroll-fields="scrollEvent"
                        @check-row-autocomplete="checkRowAutocomplete"
                        @show-src-record="showSrcRecord"
                        @show-add-ddl="showAddDDLOption"
                    ></request-form-view-element>
                </template>
            </div>
            <div v-if="dcrObject.dcr_form_line_bot" :style="{
                    borderBottom: (dcrObject.dcr_form_line_type == 'line' ? (dcrObject.dcr_form_line_thick || 1)+'px solid '+(dcrObject.dcr_form_line_color || '#d3e0e9') : null),
                    marginBottom: (dcrObject.dcr_form_line_type == 'space' ? (dcrObject.dcr_form_line_thick || 1)+'px' : null),
                }"></div>
            <request-form-buttons
                :clear-after-submis="clearAfterSubmis"
                :has-changes="hasChanges"
                :table-row="tableRow"
                :can-add-row="canAddRow"
                :available-adding="availableAdding"
                :dcr-object="dcrObject"
                :box_shad="box_shad"
                :frm_color="frm_color"
                @add-row="addRow"
                @store-rows-click="storeRows"
                @clear-submission-changed="clearSubmisClicked"
            ></request-form-buttons>
        </div>

        <div v-else-if="scrlConversational"
             class="carousel slide"
             data-ride="carousel"
             :style="carouselStyle"
        >
            <!-- Wrapper for slides -->
            <div class="carousel-inner full-height" role="listbox">
                <div
                    v-for="(avails, idx) in availGroups"
                    class="item full-height flex flex--col"
                    :style="{
                        opacity: (idx === active_idx) ? 1 : 0,
                        left: getOffset(idx, false),
                        right: getOffset(idx, true),
                    }"
                >
                    <template v-if="avails.isSlideTitle">
                        <main-request-title
                            :dcr-object="dcrObject"
                            :is_embed="is_embed"
                            :loaded="true"
                            :dcr_form_msg="dcrFormMsage"
                            :get_ava_rows="getAvaRows"
                            style="margin: auto 0 5px 0;"
                        ></main-request-title>
                        <request-form-buttons
                            :clear-after-submis="clearAfterSubmis"
                            :hide-part="'buttons'"
                            :can-add-row="canAddRow"
                            :available-adding="availableAdding"
                            :dcr-object="dcrObject"
                            :box_shad="box_shad"
                            :frm_color="frm_color"
                            style="margin: 5px 0 auto 0;"
                            @store-rows-click="storeRows"
                            @clear-submission-changed="clearSubmisClicked"
                        ></request-form-buttons>
                    </template>
                    <request-form-view-element
                        v-else
                        :avail-gr="avails"
                        :user="user"
                        :settings-meta="settingsMeta"
                        :cell-height="cellHeight"
                        :dcr-object="dcrObject"
                        :dcr-linked-rows="dcrLinkedRows"
                        :table-row="tableRow"
                        :scrl-groups="scrlGroups"
                        :frm_color="frm_color"
                        :box_shad="box_shad"
                        :with_edit="with_edit"
                        style="margin: auto 0 5px 0;"
                        @linked-rows-changed="checkRowAutocomplete"
                        @scroll-fields="scrollEvent"
                        @check-row-autocomplete="checkRowAutocomplete"
                        @show-src-record="showSrcRecord"
                        @show-add-ddl="showAddDDLOption"
                    ></request-form-view-element>

                    <request-form-buttons
                        v-if="idx == 0 && !dcrObject.dcr_sec_slide_top_header"
                        :clear-after-submis="clearAfterSubmis"
                        :hide-part="'buttons'"
                        :can-add-row="canAddRow"
                        :available-adding="availableAdding"
                        :dcr-object="dcrObject"
                        :box_shad="box_shad"
                        :frm_color="frm_color"
                        style="margin: auto 0 5px 0;"
                        @store-rows-click="storeRows"
                        @clear-submission-changed="clearSubmisClicked"
                    ></request-form-buttons>
                    <request-form-buttons
                        v-else-if="idx == availGroups.length-1"
                        :hide-part="'notes'"
                        :has-changes="hasChanges"
                        :table-row="tableRow"
                        :can-add-row="canAddRow"
                        :available-adding="availableAdding"
                        :dcr-object="dcrObject"
                        :box_shad="box_shad"
                        :frm_color="frm_color"
                        style="margin: 5px 0 auto 0;"
                        @add-row="addRow"
                        @store-rows-click="storeRows"
                    ></request-form-buttons>
                    <div v-else style="margin: 5px 0 auto 0;"></div>

                </div>
            </div>
            <a v-if="active_idx > 0"
               class="left carousel-control"
               role="button"
               data-slide="prev"
               @click.stop.prevent="prevSlide()"
            >
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a v-if="active_idx < availGroups.length-1"
               class="right carousel-control"
               role="button"
               data-slide="next"
               @click.stop.prevent="nextSlide()"
            >
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>

        <template v-else v-for="(avails, idx) in availGroups">
            <request-form-view-element
                :avail-gr="avails"
                :user="user"
                :settings-meta="settingsMeta"
                :cell-height="cellHeight"
                :dcr-object="dcrObject"
                :dcr-linked-rows="dcrLinkedRows"
                :table-row="tableRow"
                :scrl-groups="scrlGroups"
                :frm_color="frm_color"
                :box_shad="box_shad"
                :with_edit="with_edit"
                @linked-rows-changed="checkRowAutocomplete"
                @scroll-fields="scrollEvent"
                @check-row-autocomplete="checkRowAutocomplete"
                @show-src-record="showSrcRecord"
                @show-add-ddl="showAddDDLOption"
            ></request-form-view-element>
            <div v-if="dcrObject.dcr_form_line_bot && !!avails.showthis" :style="{
                borderBottom: (dcrObject.dcr_form_line_type == 'line' ? (dcrObject.dcr_form_line_thick || 1)+'px solid '+(dcrObject.dcr_form_line_color || '#d3e0e9') : null),
                marginBottom: (dcrObject.dcr_form_line_type == 'space' ? (dcrObject.dcr_form_line_thick || 1)+'px' : null),
            }"></div>
            <request-form-buttons
                v-if="idx == availGroups.length-1"
                :clear-after-submis="clearAfterSubmis"
                :has-changes="hasChanges"
                :table-row="tableRow"
                :can-add-row="canAddRow"
                :available-adding="availableAdding"
                :dcr-object="dcrObject"
                :box_shad="box_shad"
                :frm_color="frm_color"
                @add-row="addRow"
                @store-rows-click="storeRows"
                @clear-submission-changed="clearSubmisClicked"
            ></request-form-buttons>
        </template>

        <!--Progress Bar-->
        <progress-bar
            v-if="scrlConversational && dcrObject.dcr_sec_slide_progresbar"
            :pr_val="scrlProgrs"
            :ignore_format="true"
            style="position: absolute; left: 0; right: 0; bottom: 0; height: 30px"
        ></progress-bar>

        <!--Add Select Option Popup-->
        <add-option-popup
                v-if="addOptionPopup.show"
                :table-header="addOptionPopup.tableHeader"
                :table-row="addOptionPopup.tableRow"
                :table-meta="$root.tableMeta"
                :settings-meta="$root.settingsMeta"
                :user="$root.user"
                :dcr_hash="dcrObject.dcr_hash"
                @updated-row="checkRowAutocomplete"
                @hide="addOptionPopup.show = false"
                @show-src-record="showSrcRecord"
        ></add-option-popup>
    </div>
</template>

<script>
    import {VerticalTableFldObject} from "../CustomTable/VerticalTableFldObject";
    import {RefCondHelper} from "../../classes/helpers/RefCondHelper";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";
    import {RequestFuncs} from "./RequestFuncs";

    import CheckRowBackendMixin from './../_Mixins/CheckRowBackendMixin';
    import SortFieldsForVerticalMixin from './../_Mixins/SortFieldsForVerticalMixin.vue';

    import AddOptionPopup from "../CustomPopup/AddOptionPopup";
    import RequestFormButtons from "./RequestFormButtons";
    import RequestFormViewElement from "./RequestFormViewElement";
    import MainRequestTitle from "./MainRequestTitle";
    import ProgressBar from "../CustomCell/InCell/ProgressBar";

    export default {
        name: "RequestFormView",
        mixins: [
            CheckRowBackendMixin,
            SortFieldsForVerticalMixin,
        ],
        components: {
            ProgressBar,
            MainRequestTitle,
            RequestFormViewElement,
            RequestFormButtons,
            AddOptionPopup,
        },
        data: function () {
            return {
                scrlProgrs: 0,
                addOptionPopup: {
                    show: false,
                    tableHeader: null,
                    tableRow: null,
                },
                availGroups: [],
                hasChanges: false,

                //carousel
                prev_idx: null,
                next_idx: null,
                active_idx: 0,
            };
        },
        computed: {
            carouselStyle() {
                let wi = this.dcrObject.dcr_form_line_thick || 1;
                if (this.scrlConversational && this.dcrObject.dcr_sec_slide_progresbar) {
                    wi += 35;
                }
                return {height: 'calc(100% - '+wi+'px)'};
            },
            scrlGroups() {
                return this.scrlFlow || this.scrlConversational || this.scrlAccordion || this.scrlTabs;
            },
        },
        props:{
            user: Object,
            tableRow: Object|null,
            settingsMeta: Object,
            cellHeight: Number,
            canAddRow: Boolean|Number,
            dcrObject: Object,
            dcrLinkedRows: Object,
            footer_height: Number,
            frm_color: String,
            box_shad: String,
            scrlFlow: Boolean,
            scrlConversational: Boolean,
            scrlAccordion: Boolean,
            scrlTabs: Boolean,
            with_edit: Boolean,
            is_embed: Boolean,
            dcrFormMsage: String,
            getAvaRows: String|Number,
            clearAfterSubmis: Boolean,
            availableAdding: Boolean,
        },
        watch: {
            'tableRow.id': function (val) {
                this.checkRowAutocomplete();
            }
        },
        methods: {
            storeRows(status) {
                this.$emit('store-rows-click', status);
            },
            clearSubmisClicked(val) {
                this.$emit('clear-submission-changed', val);
            },
            addRow(param, new_status) {
                this.$emit('add-row', param, new_status, this.tableRow);
            },
            scrollEvent(e) {
                this.$emit('scroll-fields');
            },
            //rows changing
            showAddDDLOption(tableHeader, tableRow) {
                this.addOptionPopup = {
                    show: true,
                    tableHeader: tableHeader,
                    tableRow: tableRow,
                };
            },

            //backend autocomplete
            checkRowAutocomplete() {
                //front-end RowGroups and CondFormats
                this.hasChanges = true;
                RefCondHelper.updateRGandCFtoRow(this.$root.tableMeta, this.tableRow);

                let specParams = SpecialFuncs.specialParams();
                specParams.dcr_rows_linked = this.dcrLinkedRows;
                this.checkRowOnBackend(this.$root.tableMeta.id, this.tableRow, null, specParams, true);
            },
            //src record and tables function
            showSrcRecord(lnk, field, tableRow) {
                this.$emit('show-src-record', lnk, field, tableRow, 'list_view');
            },
            //
            makeGroups(tableMeta, tableRow) {
                let availGroups = [];
                if (this.scrlConversational && this.dcrObject.dcr_sec_slide_top_header) {
                    availGroups.push({ isSlideTitle: true, fields:[], avail_columns:[], showthis:1, showitem:0, activetab:'Fields', uuid:uuidv4() });
                }
                this.availGroups = this.getSectionGroups(tableMeta, tableRow, this.dcrObject._fields_pivot, availGroups, this.scrlGroups, 'dcr', this.dcrObject._dcr_linked_tables || []);
                if (this.scrlTabs) {
                    _.first(this.availGroups).showitem = 1;
                }
            },

            //accordion/tabs
            showAccTab(group) {
                let someWasVisible = _.sumBy(this.availGroups, 'showitem');
                let oneAcc = this.scrlAccordion && this.dcrObject.dcr_accordion_single_open;
                if (oneAcc || this.scrlTabs) {
                    _.each(this.availGroups, (gr) => {
                        if (!oneAcc || gr.uuid != group.uuid) {
                            gr.showitem = 0;
                        }
                    });//hide all tabs
                }

                if (oneAcc && someWasVisible) {
                    window.setTimeout(() => {
                        group.showitem = Number( !group.showitem );//delay to close tabs, then open new
                    }, 1000);
                } else {
                    group.showitem = Number( !group.showitem );
                }
            },
            accTabItemName(group) {
                let fld = _.first(group.fields) || {name: ''};
                let sectionName = VerticalTableFldObject.fieldSetting('dcr_section_name', fld, this.dcrObject._fields_pivot, 'dcr');
                return sectionName || fld.name;
            },
            fontStyleObj() {
                return SpecialFuncs.fontStyleObj('dcr_tab_font', this.dcrObject);
            },
            tabStyle() {
                let stl = this.fontStyleObj();
                stl.backgroundColor = this.dcrObject.dcr_tab_bg_color || '#BBB';
                stl.height = this.dcrObject.dcr_tab_height ? (this.dcrObject.dcr_tab_height+'px') : 'auto';
                return stl;
            },

            //carousel
            nextSlide() {
                if (this.prev_idx === null && this.next_idx === null) {
                    if (this.active_idx === this.availGroups.length - 1) {
                        this.next_idx = 0;
                        this.moveImages(true);
                    } else {
                        this.next_idx = this.active_idx + 1;
                        this.moveImages(true);
                    }
                }
            },
            prevSlide() {
                if (this.prev_idx === null && this.next_idx === null) {
                    if (this.active_idx === 0) {
                        this.prev_idx = this.availGroups.length - 1;
                        this.moveImages(false);
                    } else {
                        this.prev_idx = this.active_idx - 1;
                        this.moveImages(false);
                    }
                }
            },
            moveImages(forvard) {
                setTimeout(() => {
                    if (forvard) {
                        this.prev_idx = this.active_idx;
                        this.active_idx = this.next_idx;
                    } else {
                        this.next_idx = this.active_idx;
                        this.active_idx = this.prev_idx;
                    }
                    this.scrlProgrs = this.active_idx / (this.availGroups.length - 1);
                    setTimeout(this.clearMoving, 500);
                }, 100);
            },
            clearMoving() {
                this.prev_idx = null;
                this.next_idx = null;
            },
            getOffset(idx, right) {
                let left;
                if (idx === this.active_idx) {
                    left = 0;
                } else
                if (idx < this.active_idx) {
                    left = right ? '100%' : '-100%';
                } else
                if (idx > this.active_idx) {
                    left = right ? '-100%' : '100%';
                }
                return left;
            },
        },
        mounted() {
            this.makeGroups(this.$root.tableMeta, this.tableRow);

            let specParams = SpecialFuncs.specialParams();
            specParams.dcr_rows_linked = this.dcrLinkedRows;
            this.checkRowOnBackend(this.$root.tableMeta.id, this.tableRow, null, specParams);
            this.$emit('set-form-elem', _.first(this.$refs.scroll_elem));

            document.addEventListener('keydown', (e) => {
                if ((e.keyCode === 37 || e.keyCode === 38) && this.active_idx > 0) {//left/home/PgUp arrow
                    this.prevSlide();
                }
                if ((e.keyCode === 39 || e.keyCode === 40) && this.active_idx < this.availGroups.length-1) {//right/End/PgDn arrow
                    this.nextSlide();
                }
            });
        }
    }
</script>

<style lang="scss" scoped>
    .carousel {
        .left.carousel-control {
            left: -15%;
            background: transparent;
        }
        .right.carousel-control {
            right: -15%;
            background: transparent;
        }
        .item {
            display: flex !important;
            overflow: auto;
            transition: all 1s ease-in;
            perspective: none;
            opacity: 1;

            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }
    }
    .accordion_btn {
        background: #BBB;
        color: #000;
        padding: 5px 10px;
        font-weight: bold;
        cursor: pointer;
    }
    .tab_btn {
        margin: 5px 0 0 5px;
    }
    .tab_btn_active {
        background-color: #DDD !important;
    }
</style>