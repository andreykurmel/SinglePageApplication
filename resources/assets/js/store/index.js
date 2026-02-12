
import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        allSettings: {},
        user: {},
    },

    getters: {
        SETTINGS(state) {
            return state.allSettings;
        }
    },

    mutations: {
        SET_ALL_SETTINGS(state, object) {
            state.allSettings = object;
        },
        SET_USER(state, object) {
            state.user = object;
        }
    },

    actions: {},

    modules: {},
});