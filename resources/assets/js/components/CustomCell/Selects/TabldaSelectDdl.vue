<template>
    <div :class="!ddl_type_panel ? 'select-wrapper border-radius--top' : ''" ref="select_wrapper" @click.stop="">

        <!--SELECT-->
        <template v-if="!ddl_type_panel">
            <div class="select-element" @click="opened = !opened">
                <div class="element-value">{{ showPresent() }}</div>
                <div class="element-triangle">
                    <b :class="[opened ? 'b-opened' : '']"></b>
                </div>
            </div>

            <div v-if="opened" class="select-results border-radius--bottom" :style="ItemsListStyle()">
                <div v-if="show_search" class="filter-wrapper">
                    <input class="form-control" v-model="search_text" @input="loadItemsDebounce()" placeholder="Search"/>
                </div>

                <!--For 'Search' don't show DDL immediately-->
                <div v-if="show_variants" class="result-wrapper">
                    <div v-if="can_empty" class="result-item" @click="selectedItem( {value:''} )">&nbsp;</div>

                    <template v-if="fld_input_type !== 'Search' || search_text.length">
                        <div v-for="option in filtered_options"
                             class="result-item"
                             :title="option.description"
                             :class="[isSelected(option.value) ? 'result-item--selected' : '']"
                             :style="specClr(option)"
                             @click="selectedItem(option)"
                        >
                            <img v-if="option.image" :src="$root.fileUrl({url:option.image})" class="item-image"/>
                            <span>{{ option.show || option.value || '&nbsp;' }}</span>
                        </div>

                        <div v-if="has_too_many_options" class="result-item disabled">Too many options, please use 'search'</div>
                    </template>
                </div>

                <div v-if="has_embed_func && !abstract_values" class="embed-wrapper">
                    <button class="btn btn-xs btn-success full-width" @click="emitEmbed()">Add New</button>
                </div>
            </div>
        </template>

        <!--PANEL-->
        <template v-else="">
            <div v-for="option in filtered_options" class="panel-ddl" :style="specClr(option)">
                <input :type="($root.isMSEL(fld_input_type) ? 'checkbox' : 'radio')"
                       :disabled="disabled"
                       :checked="isSelected(option.value)"
                       :value="option.value"
                       @click="selectedItem(option)"
                />
                <label>
                    <img v-if="option.image" :src="$root.fileUrl({url:option.image})" class="item-image" :height="lineHeight"/>
                    <span>{{ option.show || option.value || '&nbsp;' }}</span>
                </label>
            </div>
        </template>

    </div>
</template>

<script>
    import CellStyleMixin from './../../_Mixins/CellStyleMixin.vue';
    import MixinSmartPosition from './MixinSmartPosition.vue';

    export default {
        name: "TabldaSelectDdl",
        components: {
        },
        mixins: [
            CellStyleMixin,
            MixinSmartPosition,
        ],
        data: function () {
            return {
                show_search: this.$root.hasStype(this.fld_input_type),
                search_text: '',
                debounced: null,

                avail_ddl_items: [],

                filtered_options: [],
                has_too_many_options: false,
                multiselect: this.$root.isMSEL(this.fld_input_type),
            }
        },
        props:{
            ddl_id: Number,
            tableRow: Object,
            hdr_field: String,
            fld_input_type: String,
            has_embed_func: Boolean,
            can_empty: Boolean,
            ddl_type_panel: Boolean,
            disabled: Boolean,
            fixed_pos: Boolean,
            abstract_values: Boolean,
            spec_colors: Object,
        },
        computed: {
            show_variants() {
                let so = this.$root.hasDsearch(this.fld_input_type);
                return !so || (so && this.search_text && !this.debounced);
            },
        },
        methods: {
            specClr(option) {
                return {
                    color: this.spec_colors ? (this.spec_colors[option.value] || this.spec_colors['_all']) : null,
                };
            },
            //Standard DDL
            showPresent() {
                let arr = [];
                _.each(this.filtered_options, (opt) => {
                    if (this.isSelected(opt.value)) {
                        let str = (opt.show && opt.show !== opt.value ? '$id ' : '');
                        str += opt.show || opt.value || '';
                        arr.push(str);
                    }
                });
                return arr.join(', ');
            },
            isSelected(key) {
                return this.multiselect
                    ? String(this.tableRow[this.hdr_field] || '').indexOf(key) > -1
                    : this.tableRow[this.hdr_field] == key;
            },
            selectedItem(option) {
                let value = !isNaN(option.value) ? String(option.value) : option.value;
                this.$emit('selected-item', value, option);
                if (!this.multiselect) {
                    this.hideSelect();
                }
            },
            filterOptions() {
                this.filtered_options = this.avail_ddl_items;
                if (this.filtered_options.length > 100) {
                    this.show_search = true;
                    this.has_too_many_options = true;
                    this.filtered_options = this.filtered_options.slice(0, 100);
                } else {
                    this.has_too_many_options = false;
                }

                this.showItemsList();
            },
            emitEmbed() {
                this.$emit('embed-func');
                this.hideSelect();
            },

            //DDL Data Receiving
            loadDDLitems() {
                if (this.ddl_id) {
                    let tbRow = this.abstract_values ? [] : this.tableRow || [];
                    this.$root.sm_msg_type = 1;
                    axios.post('/ajax/table-data/ddl/get-values', {
                        ddl_id: this.ddl_id,
                        row: tbRow,
                        search: this.search_text,
                    }).then(({data}) => {
                        this.avail_ddl_items = data;
                        this.filterOptions();
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                        this.debounced = null;
                    });
                }
            },
            loadItemsDebounce() {
                if (this.debounced) {
                    clearTimeout(this.debounced);
                }
                this.debounced = setTimeout(this.loadDDLitems, 300);
            },
        },
        mounted() {
            if (this.ddl_type_panel) {
                this.$watch('tableRow._check_sign', function (newVal, oldVal) {
                    this.loadDDLitems();
                }, {
                    deep: true,
                });
            } else {
                this.loadDDLitems();
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabldaSelect";
</style>