import {TabObject} from './TabObject';

export class StimSettings {
    /**
     * constructor
     * this.tabs[tab][select] = TabObject()
     * @param obj
     */
    constructor(obj) {
        this.tabs = {};
        _.each(obj.tabs, (stims, tab) => {
            if (tab) {
                let sub = {};
                _.each(stims, (group, select) => {
                    sub[select] = new TabObject(group);
                });
                this.tabs[tab] = sub;
            }
        });

        this.master_tables = obj.master_tables || [];

        this.popups_models = obj.popups_models || {};

        this.plain_settings = obj.plain_settings || [];

        this._app_cur_view = obj._app_cur_view || null;
        this.__feedback_results = obj.__feedback_results || [];
        this.__selected_feedback = obj.__selected_feedback || null;
    }
}