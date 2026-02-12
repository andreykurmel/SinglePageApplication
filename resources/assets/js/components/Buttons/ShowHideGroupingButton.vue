<template>
    <div ref="show_button" class="show_hide_button" title="Hide/Show Grouping Columns">
        <button class="btn btn-default blue-gradient flex flex--center"
                style="padding: 0 5px;"
                :style="$root.themeButtonStyle"
                @click="menu_opened = !menu_opened"
        >
            <img src="/assets/img/show_icon1.png" style="height: 28px;"/>
        </button>

        <div v-show="menu_opened" class="show_hide_menu">
            <div class="menu_part">
                <div class="title-elem">
                    <label v-if="selColGroup">
                        Column Group:
                        <a @click.prevent="clickCol()" style="cursor: pointer;">{{ selColGroup.name }}</a>
                    </label>
                    <label v-else>Columns:</label>
                </div>
                <fields-checker
                        :table-meta="tableMeta"
                        :all-checked="allChecked"
                        :check-func="checkFunc"
                        :only_columns="onlyColumns()"
                        @toggle-all="toggleAll"
                        @toggle-column="visibleChanged"
                ></fields-checker>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    import IsShowFieldMixin from "../_Mixins/IsShowFieldMixin.vue";

    import FieldsChecker from "../CommonBlocks/FieldsChecker";

    export default {
        components: {
            FieldsChecker
        },
        mixins: [
            IsShowFieldMixin
        ],
        name: "ShowHideGroupingButton",
        data: function () {
            return {
                menu_opened: false
            }
        },
        props:{
            tableMeta: Object,
            selectedGrouping: Object,
        },
        computed: {
            allChecked() {
                let fld_hidden = _.findIndex(this.selectedGrouping._gr_related_fields, (el) => {
                        return !el._grs.fld_visible;
                    }) > -1;
                let fld_showed = _.findIndex(this.selectedGrouping._gr_related_fields, (el) => {
                        return el._grs.fld_visible;
                    }) > -1;
                return !fld_hidden ? 2 : (fld_showed ? 1 : 0);
            },
            selColGroup() {
                return _.find(this.tableMeta._column_groups, {id: Number(this.selectedGrouping.rg_colgroup_id)});
            },
        },
        methods: {
            clickCol() {
                let id = this.selColGroup ? this.selColGroup.id : null;
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, 'col', id);
            },
            onlyColumns() {
                return _.map(this.selectedGrouping._gr_related_fields, 'field');
            },
            visibleChanged(el) {
                let status = this.checkFunc(el) ? 0 : 1;
                this.setStatus(el, status);
                this.emitShowChanged([el.id], status);
            },
            toggleAll() {
                let set_status = this.allChecked ? 0 : 1;
                let ids = [];
                _.each(this.selectedGrouping._gr_related_fields, (el) => {
                    this.setStatus(el, set_status);
                    ids.push(el.id);
                });
                this.emitShowChanged(ids, set_status);
            },
            emitShowChanged(ids, status) {
                this.$emit('show-changed', ids, status);

                axios.put('/ajax/addon-grouping/related-fields', {
                    table_grouping_id: this.selectedGrouping.id,
                    related_ids: ids,
                    visibility: status,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            setStatus(el, status) {
                let filter_el = _.find(this.selectedGrouping._gr_related_fields, {id: el.id});
                if (filter_el) {
                    filter_el._grs.fld_visible = status ? 1 : 0;
                }
            },

            //systems
            checkFunc(el) {
                let filter_el = _.find(this.selectedGrouping._gr_related_fields, {id: el.id});
                return filter_el && filter_el._grs.fld_visible;
            },
            hideMenu(e) {
                let container = $(this.$refs.show_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
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

<style scoped lang="scss">
    @import "ShowHide";
</style>