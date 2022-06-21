<template>
    <div class="top-panel flex flex--center-v">
        <button v-if="!!$root.user.id && cur_found_model && cur_found_model._id && !cur_found_model._virtual_mr && !vuex_settings._app_cur_view"
                class="btn btn-primary btn-sm blue-gradient ps-absolute"
                @click="showAppViews()"
                :style="$root.themeButtonStyle">Views</button>

        <!--FEEDBACK PART-->
        <feedback-block v-if="vuex_settings._app_cur_view" class="ps-absolute"></feedback-block>

        <!--MODEL SEARCH-->
        <template v-for="(stims,tab) in vuex_settings.tabs" v-if="tab">
            <template v-for="(group,select) in stims" v-if="group.master_table && vuex_found_models[group.master_table] && vuex_links[group.master_table]">
                <div v-show="vuex_cur_tab === tab && vuex_cur_select === select" class="flex flex--center-v">
                    <wid-search-model
                            class="m-left"
                            :is_visible="vuex_cur_tab === tab && vuex_cur_select === select"
                            :found_model="vuex_found_models[group.master_table]"
                            :stim_link_params="vuex_links[group.master_table]"
                            :tab_object="group"
                            :is_disabled="!!vuex_settings._app_cur_view"
                            @set-found-model="localSetFoundModel"
                    ></wid-search-model>

                    <!--SELECTS-->
                    <img v-if="getLogoSrc(group.master_table)" class="img_selector" :src="getLogoSrc(group.master_table)"/>
                    <select v-else=""
                            v-show="selects_show && vuex_cur_tab === tab && vuex_cur_select === select"
                            class="form-control view_selector"
                            v-model="vuex_select"
                    >
                        <option v-for="(tab_object,vert_k) in avail_selects"
                                v-if="!vuex_settings._app_cur_view || vuex_settings._app_cur_view.v_select === vert_k"
                                :value="vert_k">{{ tab_object.init_select }}</option>
                    </select>

                </div>
            </template>
        </template>

        <!--TABS-->
        <ul class="nav nav-tabs flex flex--col m-left">
            <li
                v-for="(stims,tab) in vuex_settings.tabs"
                v-if="tab && (!vuex_settings._app_cur_view || vuex_settings._app_cur_view.v_tab === tab)"
                :class="[vuex_cur_tab === tab ? 'active' : '']"
            >
                <a href="javascript:void(0)"
                   class="nav-item"
                   :class="[vuex_cur_tab === tab ? 'btn btn-default btn-sm blue-gradient' : '']"
                   :style="vuex_cur_tab === tab ? $root.themeButtonStyle : null"
                   @click="localSetCurTab(tab)"
                >{{ getStimsTab(stims) }}</a>
            </li>
        </ul>

        <!--VIEWS-->
        <app-views-popup :cur_tab="vuex_cur_tab" :cur_sel="vuex_cur_select" :found_row="cur_found_model"></app-views-popup>

        <!--VIEW REQUEST EMAIL-->
        <views-email-request-popup></views-email-request-popup>
    </div>
</template>

<script>
    import {eventBus} from '../../../app';

    import {SpecialFuncs} from '../../../classes/SpecialFuncs';

    import { mapState, mapGetters, mapActions } from 'vuex';

    import WidSearchModel from "./WidSearchModel";
    import AppViewsPopup from "./AppViewsPopup";
    import ViewsEmailRequestPopup from "./ViewsEmailRequestPopup";
    import FeedbackBlock from "./Feedback/FeedbackBlock";

    export default {
        name: 'TopPanelElem',
        mixins: [
        ],
        components: {
            FeedbackBlock,
            ViewsEmailRequestPopup,
            AppViewsPopup,
            WidSearchModel,
        },
        computed: {
            ...mapState({
                vuex_cur_tab: state => state.cur_tab,
                vuex_cur_select: state => state.cur_select,
                vuex_links: state => state.stim_links_container,
                vuex_settings: state => state.stim_settings,
                vuex_found_models: state => state.found_models,
            }),
            ...mapGetters({
                special_key: 'special_key',
                cur_found_model: 'cur_found_model',
            }),
            vuex_select: {
                get() {
                    return this.vuex_cur_select;
                },
                set(val) {
                    this.SET_SELECTED_VIEW({
                        tab: this.vuex_cur_tab,
                        select: val,
                    });
                },
            },
            selects_show() {
                let keys = Object.keys( this.vuex_settings.tabs[this.vuex_cur_tab] );
                return keys && keys[0];
            },
            avail_selects() {
                return this.vuex_settings.tabs[this.vuex_cur_tab];
            },
        },
        data() {
            return {
            }
        },
        props: {
        },
        methods: {
            ...mapActions([
                'SET_SELECTED_VIEW', 'SET_SELECTED_MODEL_ROW'
            ]),
            getStimsTab(stims) {
                let tab_object;
                for (let i in stims) {
                    tab_object = stims[i];
                    break;
                }
                return tab_object ? tab_object.init_top : '';
            },
            localSetCurTab(tab) {
                let sele = Object.keys( this.vuex_settings.tabs[tab] );
                this.SET_SELECTED_VIEW({
                    tab: tab,
                    select: sele[0] || '',
                });
            },
            localSetFoundModel(model_row) {
                this.SET_SELECTED_MODEL_ROW(model_row);
            },
            showAppViews() {
                eventBus.$emit('stim-app-show-view-popup');
            },
            getLogoSrc(master) {
                let repfld = this.vuex_links[master].logo_replace_field;
                let row = this.vuex_found_models[master].masterRow();
                let img = repfld && row ? _.first(row['_images_for_'+repfld] || []) : '';
                return img ? this.$root.fileUrl(img) : '';
            },
        },
        mounted() {
            $('#main_navbar').css('height', '85px');
        }
    }
</script>

<style lang="scss" scoped>
    .top-panel {
        position: fixed;
        top: 0;
        left: 200px;
        height: 85px;
        z-index: 1000;

        .ps-absolute {
            position: absolute;
        }

        .m-left {
            margin-left: 125px;
        }

        .nav-tabs {
            border-left: 1px solid #ddd;
            border-bottom: none;
            position: fixed;
            top: 0;
            right: 40%;
            height: 85px;
            
            li {
                margin-left: -1px;
            }

            .nav-item {
                overflow: hidden;
                padding: 0 4px;
                font-size: 12px;
                text-transform: capitalize;
            }

            .active {
                border-bottom: 1px solid #ddd;
                border-left-color: transparent;
            }
        }

        .view_selector {
            width: 130px;
            padding: 6px;
            margin-left: 40px;
        }
        .img_selector {
            max-height: 75px;
            max-width: 130px;
            padding: 6px;
            margin-left: 40px;
        }
    }

    @media (max-width: 1440px) {
        .top-panel {
            left: 130px;

            .nav-tabs {
                right: 30%;
            }
        }
    }
</style>