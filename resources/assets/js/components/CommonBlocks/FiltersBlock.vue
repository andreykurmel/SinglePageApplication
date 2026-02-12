<template>
    <div class="flex filters--block"
         :class="{
            'flex--col': !fixedPos,
            'full-height': placed !== 'top_filters',
            'flex--wrap': placed === 'top_filters'
        }"
         @contextmenu.prevent.self="contextFilterOnEmpty()"
    >
        <div v-for="filter in input_filters"
             v-if="canChangeFilter(filter) && availableInView(filter)"
             :class="(!fixedPos && filter.field === currentField ? 'flex__elem-remain flex flex--col' : '')"
             :style="{overflow: absoluteState ? 'visible' : 'hidden'}"
        >
            <div :class="{'wrap': fixedPos}"
                 class="full-height flex flex--col"
                 @mouseenter="hoverFilter(filter.field)"
                 @mouseleave="() => { visibleMain = !fixedPos; }"
            >
                <a @click.prevent="showFilter(filter.field)"
                   class="btn-filter"
                   :class="{'fixed-btn': fixedPos}"
                   :style="textSysStyle"
                >
                    <div class="flex flex--center-v flex--space no-wrap">
                        <span v-html="filterName(filter)"></span>
                        <div style="white-space: nowrap">
                            <label v-if="filter.filter_type !== 'range'" class="single-wrapper">
                                <input type="checkbox" @click.stop="" @change="filterSingleEnabled(filter)" v-model="filter._is_single">
                                <span>Single</span>
                            </label>
                            <span v-if="!fixedPos" class="state-shower">{{ filter.field === currentField ? '-' : '+' }}</span>
                        </div>
                    </div>
                </a>
                <div v-show="visibleMain && filter.field === currentField"
                     class="filter-content"
                     :class="{'fixed-pos': fixedPos && ! absoluteState, 'absolute-pos': fixedPos && absoluteState}"
                     :style="textSysStyle"
                >
                    <values-filter-elem
                        v-if="filter.filter_type === 'value'"
                        :filter="filter"
                        :table_meta="tableMeta"
                        @apply-filter="applyCheckedFilters(filter)"
                    ></values-filter-elem>
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

        <!--Context Filter On Empty Place-->
        <ul v-show="context_filter.active"
            v-if="tableMeta && tableMeta._fields"
            class="my_context_filter"
            :style="{left: context_filter.x+'px', top: context_filter.y+'px'}"
            @mouseleave="context_filter.active = false"
        >
            <li v-for="fld in tableMeta._fields" v-if="isShowField(fld)">
                <a href="#" class="flex flex--center-v flex--space">
                    <span>{{ $root.uniqName( fld.name ) }}</span>
                    <span class="vakata-contextmenu-sep">&nbsp;</span>
                    <label class="switch_t">
                        <input type="checkbox" v-model="fld.filter" :disabled="forbiddenFilter" @change="activateFilter(fld)">
                        <span class="toggler round" :class="[forbiddenFilter ? 'disabled' : '']"></span>
                    </label>
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    import {eventBus} from '../../app';

    import IsShowFieldMixin from './../_Mixins/IsShowFieldMixin.vue';
    import CellStyleMixin from "../_Mixins/CellStyleMixin";

    import SliderFilterElem from "../MainApp/LeftMenu/SliderFilterElem";
    import ValuesFilterElem from "../MainApp/LeftMenu/ValuesFilterElem";

    export default {
        name: 'FiltersBlock',
        components: {
            ValuesFilterElem,
            SliderFilterElem,
        },
        mixins: [
            IsShowFieldMixin,
            CellStyleMixin,
        ],
        data() {
            return {
                visibleMain: !this.fixedPos,
                currentField: null,
                context_filter: { active: false, x:10, y:10, },
            }
        },
        props: {
            tableMeta: Object,
            input_filters: Array,
            availableColumns: Array,
            no_right_click: Boolean,
            fixedPos: Boolean,
            absoluteState: Boolean,
            ignoreCanChange: Boolean,
            placed: String,
        },
        computed: {
            forbiddenFilter() {
                let array = SpecialFuncs.forbiddenCustomizables(this.tableMeta);
                return array.indexOf('filter') > -1;
            },
        },
        methods:{
            availableInView(filter) {
                let fld = _.find(this.tableMeta._fields, {field: filter.field});
                return fld && !fld._permis_hidden;
            },
            canChangeFilter(filter) {
                if (this.ignoreCanChange) {
                    return true;
                }
                //can view filter if ( Owner OR has view permissions ) AND can see this Column
                return this.tableMeta
                    && (
                        this.tableMeta._is_owner
                        ||
                        $.inArray(filter.field, this.tableMeta._current_right.view_fields) > -1
                    )
                    &&
                    (!this.availableColumns || this.availableColumns.indexOf(filter.field) > -1)
                    //&&
                    //_.find(this.tableMeta._fields, {field: filter.field, is_showed: 1})
                    &&
                    filter.values;
            },
            showFilter(field) {
                if (!this.fixedPos) {
                    this.currentField = field !== this.currentField ? field : null;
                }
            },
            hoverFilter(field) {
                if (this.fixedPos) {
                    this.visibleMain = true;
                    this.currentField = field;
                }
            },
            filterName(filter) {
                let name = filter.name + (filter.applied_index ? ' ('+filter.applied_index+')' : '&nbsp;&nbsp;&nbsp;&nbsp;');
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
            applyRadio(filter, selected) {
                filter._single_val = selected;
                for (let i in filter.values) {
                    filter.values[i].checked = selected === undefined || filter.values[i].show === selected;
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

            //context filter
            contextFilterOnEmpty() {
                if (!this.no_right_click) {
                    this.context_filter.active = true;
                    this.context_filter.x = window.event.clientX - 10;
                    this.context_filter.y = window.event.clientY - 160;
                }
            },
            activateFilter(fld) {
                if (this.forbiddenFilter) {
                    return;
                }
                fld._changed_field = 'filter';
                this.$root.updateSettingsColumn(this.tableMeta, fld);
                this.$emit('filter-status-toggle', fld);
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

<style lang="scss" scoped>
    .filters--block {
        font-size: 13px;
        color: #6d6d6d;
        position: relative;

        .wrap {
            position: relative;
            width: min-content;
        }
        .fixed-btn {
            margin-right: 3px;
            height: 32px;
            font-size: 14px !important;
            padding: 4px 10px !important;
        }
        .fixed-pos, .absolute-pos {
            z-index: 1000;
            background: white;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: auto;
        }
        .fixed-pos {
            transform: translateY(32px);
            position: fixed;
            width: 250px;
            max-height: 80%;
        }
        .absolute-pos {
            position: absolute;
            top: 100%;
            width: 250px;
            max-height: 80vh;
        }

        .flex__elem-remain {
            min-height: 150px;
        }

        .single-wrapper {
            position: relative;
            top: -3px;
            margin: 0 0 0 10px;
            font-weight: normal;

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

        .filter-content {
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

        .my_context_filter {
            max-height: 200px;
            overflow: auto;
            display: block;
            margin: 0;
            padding: 2px;
            position: absolute;
            background: #f5f5f5;
            border: 1px solid #979797;
            box-shadow: 2px 2px 2px #999;
            z-index: 1000;

            li {
                list-style: none;

                a {
                    position: relative;
                    padding: 0 3px;
                    text-decoration: none;
                    width: auto;
                    color: #000;
                    white-space: nowrap;
                    line-height: 2.4em;
                    text-shadow: 1px 1px 0 #fff;
                    border-radius: 1px;

                    &:hover {
                        background-color: #e8eff7;
                        box-shadow: 0 0 2px #0a6aa1;
                    }

                    .switch_t {
                        margin: 0;
                    }

                }
            }
        }
    }
</style>