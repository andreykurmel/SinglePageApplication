<template>
    <div ref="show_button" class="show_hide_button" title="Hide/Show Single or Group of Tables/RCs">
        <button class="btn btn-default blue-gradient flex flex--center"
                :style="$root.themeButtonStyle"
                @click="openMenu"
        >
            <img src="/assets/img/show_icon1.png"/>
        </button>

        <div v-show="menu_opened" class="show_hide_menu">

            <div class="menu_part" style="width: 200px">
                <div class="title-elem">
                    <label>Tables:</label>
                </div>
                <div v-if="rcmap_tables.length">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAll('rcmap_tables')">
                                <i v-if="allChecked('rcmap_tables') == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                <i v-if="allChecked('rcmap_tables') == 1" class="glyphicon glyphicon-minus group__icon"></i>
                            </span>
                        </span>
                        <span>All</span>
                    </label>
                </div>
                <div v-for="pos in rcmap_tables">
                    <label :style="{backgroundColor: (pos.visible ? '#CCC;' : '')}">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="posToggled(pos)">
                                <i v-if="pos.visible" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>{{ posName(pos) }}</span>
                    </label>
                </div>
            </div>
            <div class="vert_line"></div>
            <div class="menu_part">
                <div class="title-elem">
                    <label>Ref Conditions (THIS table):</label>
                </div>
                <div v-if="rcmap_this_rcs.length">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAll('rcmap_this_rcs')">
                                <i v-if="allChecked('rcmap_this_rcs') == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                <i v-if="allChecked('rcmap_this_rcs') == 1" class="glyphicon glyphicon-minus group__icon"></i>
                            </span>
                        </span>
                        <span>All</span>
                    </label>
                </div>
                <div v-for="pos in rcmap_this_rcs">
                    <label :style="{backgroundColor: (pos.visible ? '#CCC;' : '')}">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="posToggled(pos)">
                                <i v-if="pos.visible" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>{{ posName(pos) }}</span>
                    </label>
                </div>

                <div class="title-elem">
                    <label>Ref Conditions (other tables):</label>
                </div>
                <div v-if="rcmap_other_rcs.length">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAll('rcmap_other_rcs')">
                                <i v-if="allChecked('rcmap_other_rcs') == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                <i v-if="allChecked('rcmap_other_rcs') == 1" class="glyphicon glyphicon-minus group__icon"></i>
                            </span>
                        </span>
                        <span>All</span>
                    </label>
                </div>
                <div v-for="pos in rcmap_other_rcs">
                    <label :style="{backgroundColor: (pos.visible ? '#CCC;' : '')}">
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="posToggled(pos)">
                                <i v-if="pos.visible" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>{{ posName(pos) }}</span>
                    </label>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../../app";

    export default {
        components: {
        },
        mixins: [
        ],
        name: "ShowHideRcMapButton",
        data() {
            return {
                menu_opened: false,
                rcmap_tables: [],
                rcmap_this_rcs: [],
                rcmap_other_rcs: [],
            }
        },
        props: {
            tableMeta: Object,
        },
        computed: {
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            fillMapGroups() {
                let thisRCs = _.map(
                    _.filter(this.tableMeta._ref_conditions, (rc) => rc.table_id == rc.ref_table_id),
                    'id'
                );
                
                this.rcmap_tables = _.filter(this.tableMeta._rcmap_positions, {object_type: 'table'});
                this.rcmap_this_rcs = _.filter(this.tableMeta._rcmap_positions, (pos) => {
                    return pos.object_type == 'ref_cond' && this.inArray(pos.object_id, thisRCs);
                });
                this.rcmap_other_rcs = _.filter(this.tableMeta._rcmap_positions, (pos) => {
                    return pos.object_type == 'ref_cond' && ! this.inArray(pos.object_id, thisRCs);
                });
            },
            openMenu() {
                this.fillMapGroups();
                this.menu_opened = ! this.menu_opened;
            },
            allChecked(key) {
                let fld_hidden = _.findIndex(this[key], (el) => {
                    return !el.visible;
                }) > -1;
                let fld_showed = _.findIndex(this[key], (el) => {
                    return el.visible;
                }) > -1;
                return !fld_hidden ? 2 : (fld_showed ? 1 : 0);
            },
            toggleAll(key) {
                let val = this.allChecked(key);
                _.forEach(this[key], (el) => {
                    el.visible = val != 2;
                });
                this.$emit('updated-elements', this[key]);
            },
            posToggled(pos) {
                pos.visible = ! pos.visible;
                this.$emit('updated-elements', [pos]);
            },
            posName(pos) {
                if (pos.object_type == 'table') {
                    let tb = _.find(this.$root.settingsMeta.available_tables, (rc) => {
                        return rc.id == pos.object_id;
                    }) || {};
                    return tb.name;
                }
                if (pos.object_type == 'ref_cond') {
                    let tb = _.find(this.tableMeta._ref_conditions, (rc) => {
                        return rc.id == pos.object_id;
                    }) || {};
                    return tb.name;
                }
                return '';
            },
            hideMenu(e) {
                let container = $(this.$refs.show_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
        },
        mounted() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style scoped lang="scss">
    @import "../../../../../Buttons/ShowHide";
</style>