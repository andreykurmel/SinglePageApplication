
import Vuex from 'vuex';
import {is_strict} from './is_strict';

import {FoundModel} from '../../../classes/FoundModel';
import {StimLinkParams} from '../../../classes/StimLinkParams';
import {StimSettings} from '../../../classes/StimSettings';
import {SpecialFuncs} from '../../../classes/SpecialFuncs';
import {WidSettings} from './../WidSettings';

/*import equipment from './modules/equipment';
import geometry from './modules/geometry';
import ma_services from './modules/ma_services';
import tsa_services from './modules/tsa_services';
import site from './modules/site';*/


const available_tabs = ['data', 'geometry', 'site', 'services'];
const available_components = [
    'equipment_data', 'feedline_data', 'loading_data', 'design_data',
    'geometry', 'site', 'ma_services', 'tsa_services'
];

//keys are from available_tabs
/*const found_models = {};
_.each(available_components, (key) => {
    found_models[key] = new FoundModel();
});*/
const modules = {
    /*'equipment': equipment,
    'geometry': geometry,
    'ma_services': ma_services,
    'tsa_services': tsa_services,
    'site': site,*/
};



export default new Vuex.Store({
    strict: is_strict,

    state: {
        settings_3d: new WidSettings(),
        stim_settings: {}, // StimSettings()
        stim_links_container: {}, // {key: StimLinkParams(), ...}
        found_models: {
            _is_loaded: false,
            // {key: FoundModel(), ...}
        },

        cur_tab: '',
        cur_select: '',
        redraw_counter: 0,
        redraw_soft_counter: 0,
        width_main: 60,
        width_3d: 40,
    },

    getters: {
        cur_found_model(state) {
            let mtabl = _s_wid_master_table(state);
            return state.found_models[mtabl];
        },
        cur_master_table(state) {
            return _s_wid_master_table(state);
        },
        cur_tab_object(state) {
            return _s_wid_tab_object(state);
        },
    },

    mutations: {
        //INITIALS
        SET_STIM_SETTINGS(state, settings) {
            state.stim_settings = new StimSettings(settings);
        },
        SET_STIM_LINK_PARAMS(state, params) {
            let params_obj = {};
            for (let i in params) {
                let lower = String(i).toLowerCase();
                params_obj[lower] = new StimLinkParams(params[i]);
            }
            state.stim_links_container = params_obj;
        },
        SET_FOUND_MODELS(state, models) {
            state.found_models = models;
        },
        //TABS
        SET_CUR_TAB_SELECT(state, payload) {
            let tab = String(payload.tab).toLowerCase();
            let tab_avail = Object.keys( state.stim_settings.tabs );
            tab = tab_avail.indexOf(tab) > -1 ? tab : tab_avail[0];

            let select = String(payload.select).toLowerCase();
            let select_avail = Object.keys( state.stim_settings.tabs[tab] );
            select = select_avail.indexOf(select) > -1 ? select : select_avail[0];

            state.cur_tab = tab;
            state.cur_select = select;
        },
        //3D WIDTHS
        SET_WIDTH_3D(state, payload) {
            state.width_main = !payload.main ? 0 : (payload.d3 ? 60 : 100);
            state.width_3d = !payload.d3 ? 0 : (payload.main ? 40 : 100);
        },
        //URL CHANGING
        CHANGE_HREF(state) {
            if (state.stim_settings._app_cur_view) {
                return;
            }
            let href = window.location.href.replace(window.location.search, '');
            let params = [];

            let tab = SpecialFuncs.lowerCase(state.cur_tab);
            (tab ? params.push('tab='+tab) : '');

            let select = SpecialFuncs.lowerCase(state.cur_select);
            (select ? params.push('sel='+select) : '');

            $('head title').html('STIM: '+tab+'/'+select);

            let key = _s_wid_master_table(state);
            let model = state.found_models[key];
            if (model) {
                params = _fill_model_params(params, model, state.stim_links_container[key]);
            }

            if (params) {
                href += '?' + params.join('&');
            }
            window.history.pushState('', '', href);
        },
        //REDRAW 3D
        REDRAW_3D(state, soft) {
            if (soft) {
                state.redraw_soft_counter++;
            } else {
                state.redraw_counter++;
            }
        },
    },

    actions: {
        //TABS
        SET_SELECTED_VIEW(store, payload) {
            store.commit('SET_CUR_TAB_SELECT', payload);
            let cur_tab_obj = _s_wid_tab_object(store.state);
            if (cur_tab_obj && !cur_tab_obj.type_3d) {
                store.commit('SET_WIDTH_3D', {main:100, d3:0});
            } else {
                store.commit('SET_WIDTH_3D', {main:60, d3:40});
            }
            store.commit('CHANGE_HREF');
        },
        //LOAD 3D DATA
        SET_SELECTED_MODEL_ROW(store, model_row) {
            store.commit('CHANGE_HREF');
            store.commit('REDRAW_3D');
        },
        /*LOAD_3D_DATA(store, model_row) {
            switch (store.getters.special_key) {
                case 'equipment_data':
                    store.dispatch('equipment/LOAD_MODEL_DATA', model_row);
                    break;
                case 'geometry':
                    store.dispatch('geometry/LOAD_MODEL_DATA', model_row);
                    break;
                case 'ma_services':
                    store.dispatch('ma_services/LOAD_MODEL_DATA', model_row);
                    break;
            }
        },*/
    },

    modules: {
        ...modules,
    },
});

/**
 * Get Current master table
 * @param state
 * @returns {string}
 * @private
 */
function _s_wid_master_table(state) {
    let group = _s_wid_tab_object(state);
    return group ? group.master_table : '';
}

/**
 * Get Current TabObject
 * @param state
 * @returns {TabObject}
 * @private
 */
function _s_wid_tab_object(state) {
    return state.stim_settings.tabs[state.cur_tab][state.cur_select];
}

/**
 * Fill Params in URL
 * @param {array} params
 * @param {FoundModel} found_model
 * @param {StimLinkParams} stim_link_params
 * @returns {array}
 * @private
 */
function _fill_model_params(params, found_model, stim_link_params) {
    if (stim_link_params && found_model && found_model._id) {
        let row = found_model.masterRow();
        _.each(stim_link_params.in_url_elements, (elem) => {
            params.push(elem.app_field+'='+row[elem.data_field]);
        });
        if (row._virtual_mr) {
            params.push('range=1');
        }
    }
    return params;
}