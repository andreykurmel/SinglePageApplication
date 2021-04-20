<template>
    <div class="full-height flex flex--col filters--block">
        <div v-for="filter in input_filters"
             v-if="canChangeFilter(filter)"
             :class="(filter.field === currentField ? 'flex__elem-remain flex flex--col' : '')"
        >
            <a @click.prevent="showFilter(filter.field)" class="btn-filter">
                <div class="flex flex--center-v flex--space">
                    <span>{{filterName(filter)}}</span>
                    <div style="white-space: nowrap">
                        <label v-if="filter.filter_type !== 'range'" class="single-wrapper">
                            <input type="checkbox" @click.stop="" @change="filterSingleEnabled(filter)" v-model="filter._is_single">
                            <span>Single</span>
                        </label>
                        <span class="state-shower">{{ filter.field === currentField ? '-' : '+' }}</span>
                    </div>
                </div>
            </a>
            <div v-show="filter.field === currentField" class="filter-content">
                <ul v-if="typeof filter.values !== 'object'">
                    <li><label>{{ filter.values }}</label></li>
                </ul>
                <ul v-else-if="filter.filter_type === 'value'">
                    <li>
                        <label class="checkbox-container">
                            <span>Check All</span>
                            <input type="checkbox"
                                   :checked="allIsChecked(filter)"
                                   @change="filter._is_single ? applyRadio(filter, undefined) : toggleAll(filter, allIsChecked(filter))">

                            <span v-if="filter._is_single" class="checkmark marktype--radio">
                                <span v-if="filter._single_val === undefined" class="marktype--radio__checked"></span>
                            </span>
                            <span v-else="" class="indeterm_check flex flex--center-h">
                                <i v-if="allIsChecked(filter) == 2" class="glyphicon glyphicon-ok group__icon"></i>
                                <i v-if="allIsChecked(filter) == 1" class="glyphicon glyphicon-minus group__icon"></i>
                            </span>

                        </label>
                    </li>
                    <li v-for="group in get_values(filter)" v-if="group.length">
                        <label class="checkbox-container"
                               :class="group[0].rowgroup_disabled ? 'disabled' : ''"
                               :title="group[0].rowgroup_disabled ? 'Disabled by RowGroups' : ''"
                               :style="{opacity: (group[0].rowgroup_disabled ? '0.5' : '')}"
                        >
                            <span v-html="group[0].show"></span>&nbsp;
                            <input type="checkbox"
                                   :checked="group[0].checked"
                                   :disabled="group[0].rowgroup_disabled"
                                   @click="filter._is_single ? applyRadio(filter, group[0].show) : applyCheck(filter, group[0])"
                            >
                            <span class="checkmark" :class="{'marktype--radio': filter._is_single}">
                                <span v-if="filter._is_single && filter._single_val === group[0].show" class="marktype--radio__checked"></span>
                            </span>
                        </label>
                    </li>
                </ul>
                <div v-else-if="filter.filter_type === 'range' && filter.values.min && filter.values.max && filter.field === currentField">
                    <slider-filter-elem
                            :filter_values="filter.values"
                            :f_type="filter.f_type"
                            @changed-range="applyCheckedFilters(filter)"
                    ></slider-filter-elem>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import SliderFilterElem from "../MainApp/LeftMenu/SliderFilterElem";

    export default {
        name: 'FiltersBlock',
        components: {
            SliderFilterElem,
        },
        data() {
            return {
                currentField: null,
            }
        },
        props: {
            tableMeta: Object,
            input_filters: Array,
            availableColumns: Array,
        },
        computed: {
        },
        methods:{
            get_values(filter) {
                return _.groupBy(filter.values, 'show');
            },
            canChangeFilter(filter) {
                //can view filter if ( Owner OR has view permissions ) AND can see this Column
                return this.tableMeta
                    && (
                        this.tableMeta._is_owner
                        ||
                        $.inArray(filter.field, this.tableMeta._current_right.view_fields) > -1
                    )
                    &&
                    (!this.availableColumns || this.availableColumns.indexOf(filter.field) > -1)
                    &&
                    _.find(this.tableMeta._fields, {field: filter.field, is_showed: 1})
                    &&
                    filter.values;
            },
            showFilter(field) {
                this.currentField = field !== this.currentField ? field : null;
            },
            filterName(filter) {
                let name = filter.name + (filter.applied_index ? ' ('+filter.applied_index+')' : '');
                return this.$root.uniqName(name);
            },
            filterSingleEnabled(filter) {
                if (filter._is_single && !this.allIsChecked(filter)) {
                    this.applyRadio(filter, undefined);
                }
            },
            allIsChecked(filter) {
                let unchecked = _.filter(filter.values, function(el) {
                    return !el.checked;
                });
                return !unchecked.length
                    ? 2
                    : unchecked.length < filter.values.length ? 1 : 0;
            },
            toggleAll(filter, status) {
                for (let i in filter.values) {
                    filter.values[i].checked = !status;
                }
                this.applyCheckedFilters(filter);
            },
            applyRadio(filter, selected) {
                filter._single_val = selected;
                for (let i in filter.values) {
                    filter.values[i].checked = selected === undefined || filter.values[i].show === selected;
                }
                this.applyCheckedFilters(filter);
            },
            applyCheck(filter, item) {
                let stat = !item.checked;
                for (let i in filter.values) {
                    if (filter.values[i].show === item.show) {
                        filter.values[i].checked = stat;
                    }
                }
                this.applyCheckedFilters(filter);
            },
            applyCheckedFilters(filter) {
                if (filter.applied_index) {
                    //if absent unchecked in applied filter_type = 'value' -> unset applied index
                    if (
                        (!!filter.filter_type || filter.filter_type === 'value')
                        && !_.find(filter.values, (el) => {return !el.checked})
                    ) {
                        filter.applied_index = 0;
                    }
                    //if absent range in applied filter_type = 'range' -> unset applied index
                    if (
                        filter.filter_type === 'range'
                        && filter.values.min.val == filter.values.min.selected
                        && filter.values.max.val == filter.values.max.selected
                    ) {
                        filter.applied_index = 0;
                    }
                } else {
                    //if unchecked present in not applied filter -> apply it
                    let max_filter = _.maxBy(this.input_filters, 'applied_index');
                    filter.applied_index = max_filter.applied_index+1;
                }

                this.$emit('changed-filter');
            },
        },
        created() {
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped="">
    .filters--block {
        font-size: 13px;
        color: #6d6d6d;
        position: relative;

        .indeterm_check {
            position: absolute;
            left: 0;
            top: 0;
            background-color: #EEE;
            height: 15px;
            width: 15px;
            font-size: 12px;

            &:hover {
                background-color: #DDD;
            }
        }

        .single-wrapper {
            position: relative;
            top: -3px;
            margin: 0;

            input {
                position: relative;
                top: 2px;
            }
        }

        .state-shower {
            font-size: 2em;
            line-height: 0.7em;
        }

        .btn-filter {
            display: block;
            cursor: pointer;
            padding: 10px 11px;
            color: #555555;
            background-size: 100% 100%;
            background: linear-gradient(to top, #efeff4, #d6dadf);
            border: 1px solid #cccccc;
            text-decoration: none;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75), 0 1px 1px rgba(0, 0, 0, 0.15);

            &:not(.active):hover {
                color: black;
            }
        }

        .filter-content{
            overflow: auto;
            ul {
                padding-left: 5px;
                margin-top: 5px;
                margin-bottom: 5px;

                li {
                    list-style-type: none;
                }
            }
        }
    }
</style>