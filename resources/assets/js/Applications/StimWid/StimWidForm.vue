<template>
    <div class="full-frame flex flex--col" v-if="vuex_found_models._is_loaded">

        <top-panel-elem v-show="visible_navbar"></top-panel-elem>

        <div class="divider divider--v">
            <div class="divi-item divi-item--v" title="Show/Hide Navbar" @click="toggleNavbar()"></div>
        </div>

        <div class="full-frame flex" style="overflow: hidden">
            <div class="main_el" :style="{width: vuex_main+'%'}">
                <div class="full-height" style="padding: 10px;">
                    <template v-for="(stims,tab) in vuex_settings.tabs" v-if="tab">
                        <template v-for="(group,select) in stims" v-if="group.master_table">

                            <accordion-tab v-if="group.tab_style === 'accordion'"
                                           v-show="vuex_cur_tab === tab && vuex_cur_select === select"
                                           :is_showed="vuex_cur_tab === tab && vuex_cur_select === select"
                                           :tab="tab"
                                           :select="select"
                                           :tab_object="group"
                                           :found_model="vuex_found_models[group.master_table]"
                            ></accordion-tab>

                            <universal-tab v-else=""
                                           v-show="vuex_cur_tab === tab && vuex_cur_select === select"
                                           :is_showed="vuex_cur_tab === tab && vuex_cur_select === select"
                                           :tab="tab"
                                           :select="select"
                                           :tab_object="group"
                                           :found_model="vuex_found_models[group.master_table]"
                            ></universal-tab>

                        </template>
                    </template>
                </div>
            </div>

            <div class="full-height divider">
                <div class="divi-item" title="Resize to left" @click="toggleLeft()"></div>
                <div class="divi-item" title="Resize to right" @click="toggleRight()"></div>
            </div>

            <div :style="{width: vuex_3d+'%'}">
                <three3d-wid :cur_tab_sel="vuex_cur_tab+'_'+vuex_cur_select"></three3d-wid>
            </div>
        </div>

        <!--Popup for showing very long datas-->
        <table-data-string-popup :max-cell-rows="$root.maxCellRows"></table-data-string-popup>

    </div>
</template>

<script>
    import {SpecialFuncs} from '../../classes/SpecialFuncs';
    import {FoundModel} from '../../classes/FoundModel';

    import {eventBus} from './../../app';

    import {mapState, mapGetters, mapMutations, mapActions} from 'vuex';

    import wid from './_store/wid';

    import TopPanelElem from "./TopPanel/TopPanelElem";
    import Three3dWid from "./Three3dWid";
    import UniversalTab from "./MainData/UniversalTab";
    import AccordionTab from "./MainData/AccordionTab";
    import TableDataStringPopup from './../../components/CustomPopup/TableDataStringPopup';

    export default {
        name: 'StimWidForm',
        store: wid,
        mixins: [
        ],
        components: {
            AccordionTab,
            UniversalTab,
            Three3dWid,
            TopPanelElem,
            TableDataStringPopup,
        },
        data() {
            return {
                visible_navbar: true,
            }
        },
        computed: {
            ...mapState({
                vuex_settings: state => state.stim_settings,
                vuex_links: state => state.stim_links_container,
                vuex_found_models: state => state.found_models,
                vuex_cur_tab: state => state.cur_tab,
                vuex_cur_select: state => state.cur_select,
                vuex_main: state => state.width_main,
                vuex_3d: state => state.width_3d,
                vuex_settings_3d: state => state.settings_3d,
            }),
            ...mapGetters({
                vuex_master_table: 'cur_master_table',
            }),
        },
        props: {
            init_tab: String,
            init_select: String,
            init_model: Object,
            settings_meta: Object,
            stim_link_params: Object,
            stim_settings: Object,
        },
        methods: {
            ...mapMutations([
                'SET_STIM_SETTINGS', 'SET_STIM_LINK_PARAMS',
                'SET_FOUND_MODELS', 'SET_WIDTH_3D',
            ]),
            ...mapActions([
                'SET_SELECTED_VIEW',
            ]),
            //VIEWS
            toggleNavbar() {
                if (!this.sideIsNa('side_top')) {
                    this.$root.toggleTopMenu();
                    this.visible_navbar = !this.visible_navbar;
                }
            },
            toggleLeft() {
                if (!this.sideIsNa('side_left')) {
                    this.SET_WIDTH_3D(this.vuex_main > 0 ? {main:0, d3:this.vuex_3d} : {main:60, d3:this.vuex_3d});
                }
            },
            toggleRight() {
                if (!this.sideIsNa('side_right')) {
                    this.SET_WIDTH_3D(this.vuex_3d > 0 ? {main:this.vuex_main, d3:0} : {main:this.vuex_main, d3:40});
                }
            },
            sideIsNa(side_str) {
                let pnls = this.stim_settings._app_cur_view;
                return pnls && pnls[side_str] === 'na';
            },
            //GLOBAL KEYS
            globalKeyHandler(e) {
                if (e.target.nodeName === 'BODY') {//target not in Input
                    if (e.ctrlKey) {
                        if (e.keyCode === 37) {//ctrl + left arrow
                            this.toggleLeft();
                        }
                        if (e.keyCode === 38) {//ctrl + up arrow
                            this.toggleNavbar();
                        }
                        if (e.keyCode === 39) {//ctrl + right arrow
                            this.toggleRight();
                        }
                    }
                    if (e.shiftKey) {
                        if (e.keyCode === 79) {//shift + 'O'
                            this.toggleLeft();
                            this.toggleNavbar();
                        }
                    }
                }
            },
            //INIT/
            prepareMasterTables() {
                let found_models = { _is_loaded: true };
                _.each(this.vuex_links, (params) => {
                    let is_master = this.vuex_settings.master_tables.indexOf(params.app_table) > -1;
                    found_models[params.app_table] = new FoundModel();
                    found_models[params.app_table].prepare(params, this.$root.user, is_master);
                    //preload for Model3D Popups
                    /*let find = _.find(this.vuex_settings.popups_models, (i) => {return i === params.app_table});
                    if (find) {
                        found_models[params.app_table].meta.loadHeaders();
                    }*/
                });
                this.SET_FOUND_MODELS(found_models);
            },
        },
        mounted() {//order of functions is important
            console.log('STIM STATE', this.$store.state);

            //start 3d settings
            this.vuex_settings_3d.applyProps({}, this.$root.user.id);

            //INITS
            this.SET_STIM_SETTINGS(this.stim_settings);
            this.SET_STIM_LINK_PARAMS(this.stim_link_params);
            this.prepareMasterTables();

            //SET TAB
            this.SET_SELECTED_VIEW({
                tab: this.init_tab,
                select: this.init_select,
            });

            //SET PRELOAD MODEL
            if (this.init_model) {
                this.$nextTick(() => {
                    let key = this.vuex_master_table;
                    this.vuex_found_models[key].setSelectedRow(this.init_model);
                    this.$store.dispatch('SET_SELECTED_MODEL_ROW', this.init_model);
                });
            }

            //init sides
            let pnls = this.stim_settings._app_cur_view;
            if (pnls) {
                this.visible_navbar = pnls.side_top == 'show';
                (pnls.side_top == 'show' ? $('#main_navbar').show() : $('#main_navbar').hide());

                let main = pnls.side_left == 'show' ? 60 : 0;
                let d3 = pnls.side_right == 'show' ? 40 : 0;
                this.SET_WIDTH_3D({main:main, d3:d3});
            }

            //No Edit at all if 'is view'
            this.$root.global_no_edit = !!this.stim_settings._app_cur_view;

            eventBus.$on('global-keydown', this.globalKeyHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.globalKeyHandler);
        }
    }
</script>

<style lang="scss" scoped="">
    .full-frame {
        border: 1px solid #BBB;
    }

    .flex__elem__inner {
        background-color: #FFF;
        padding: 10px;
    }

    .main_el {
        overflow: hidden;
        background-color: #FFF;
    }

    .divider {
        width: 7px;
        background: #DDD;
        border: 1px solid #BBB;
        overflow: hidden;

        .divi-item {
            cursor: pointer;
            width: 100%;
            height: 50%;

            &:hover {
                background-color: #ffaa22;
            }
        }
    }
    .divider--v {
        height: 5px;
        width: 100%;
        border: none;

        .divi-item--v {
            height: 100%;
        }
    }
</style>