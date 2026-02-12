<template>
    <ul>

        <li v-if="filter.filter_search">
            <label class="searching_el">
                <input class="form-control" v-model="searching" placeholder="Search">
            </label>
        </li>

        <template v-if="filterValues.length">
            <li>
                <label class="checkbox-container">
                    <span>ALL DISTINCT VALUES</span>
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
            <li v-for="filter_val in filterValues">
                <label class="checkbox-container"
                       :class="filter_val.rowgroup_disabled ? 'disabled' : ''"
                       :title="filter_val.rowgroup_disabled ? 'Disabled by RowGroup(s)' : '('+filter_val.val+')'"
                       :style="{opacity: (filter_val.rowgroup_disabled ? '0.5' : '')}"
                >
                    <span v-html="filter_val.show"></span>&nbsp;
                    <input v-if="filter_val.rowgroup_disabled" type="checkbox">
                    <input v-else type="checkbox"
                           :checked="filter_val.checked"
                           @click="filter._is_single ? applyRadio(filter, filter_val.show) : applyCheck(filter, filter_val)"
                    >
                    <span class="checkmark" :class="{'marktype--radio': filter._is_single}">
                        <span v-if="filter._is_single && filter._single_val === filter_val.show" class="marktype--radio__checked"></span>
                    </span>
                </label>
            </li>
        </template>
        <li v-else-if="filter.error_msg">
            <label>{{ filter.error_msg }}</label>
        </li>

    </ul>
</template>

<script>
    export default {
        name: 'ValuesFilterElem',
        mixins: [
        ],
        data() {
            return {
                searching: '',
                last_search_request: '',
            }
        },
        props: {
            filter: Object,
            table_meta: Object,
        },
        computed: {
            filterValues() {
                let filteredVls = _.filter(this.filter.values, (fval) => {
                    return !this.searching || String(fval.show).toLowerCase().indexOf( String(this.searching).toLowerCase() ) > -1;
                });
                return this.filter.error_msg && filteredVls.length > this.maxEl
                    ? []
                    : filteredVls;
            },
            maxEl() {
                return this.table_meta ? (this.table_meta.max_filter_elements || 1000) : 1000;
            },
        },
        methods: {
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
                this.emitChanged(filter);
            },
            applyRadio(filter, selected) {
                filter._single_val = selected;
                for (let i in filter.values) {
                    filter.values[i].checked = selected === undefined || filter.values[i].show === selected;
                }
                this.emitChanged(filter);
            },
            applyCheck(filter, item) {
                let stat = !item.checked;
                for (let i in filter.values) {
                    if (filter.values[i].show === item.show) {
                        filter.values[i].checked = stat;
                    }
                }
                this.emitChanged(filter);
            },
            emitChanged(filter) {
                this.$emit('apply-filter', filter);
            },
        },
        mounted() {
        },
    }
</script>

<style lang="scss" scoped>
    ul {
        list-style-type: none;

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

        .searching_el {
            display: block;
            input {
                padding: 0 3px;
                height: 24px;
            }
        }
    }
</style>