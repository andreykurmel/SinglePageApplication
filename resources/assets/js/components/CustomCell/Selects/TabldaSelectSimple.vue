<template>
    <div class="select-wrapper border-radius--top" ref="select_wrapper" @click.stop="">
        <div class="select-element" @click="opened = !opened">
            <div class="element-value">{{ showPresent() }}</div>
            <div class="element-triangle">
                <b :class="[opened ? 'b-opened' : '']"></b>
            </div>
        </div>

        <div v-if="opened" class="select-results border-radius--bottom" :style="ItemsListStyle()">
            <div v-if="allowed_tags" class="filter-wrapper">
                <input class="form-control" v-model="search_text" @keyup="filterOptions" placeholder="Search"/>
            </div>

            <div class="result-wrapper">
                <div v-if="can_empty" class="result-item" @click="selectedItem( '' )">&nbsp;</div>

                <div v-if="allowed_tags && search_text.length" class="result-item" @click="selectedItem( search_text )">{{ search_text }}</div>

                <div v-for="opt in filtered_options"
                     class="result-item"
                     :class="[isSelected(opt.val) ? 'result-item--selected' : '']"
                     @click="selectedItem( opt.val, opt.show )"
                >{{ opt.show || opt.val || '&nbsp;' }}</div>
            </div>

            <div v-if="embed_func_txt" class="embed-wrapper">
                <button class="btn btn-xs btn-success full-width" @click="emitEmbed()">{{ embed_func_txt }}</button>
            </div>
        </div>

    </div>
</template>

<script>
    import MixinSmartPosition from './MixinSmartPosition.vue';

    export default {
        name: "TabldaSelectSimple",
        components: {
        },
        mixins: [
            MixinSmartPosition
        ],
        data: function () {
            return {
                search_text: '',
                filtered_options: [],
                multiselect: this.$root.isMSEL(this.fld_input_type),
            }
        },
        props:{
            options: Array,
            tableRow: Object,
            hdr_field: String,
            fld_input_type: String,
            can_empty: Boolean,
            allowed_tags: Boolean,
            embed_func_txt: String,
            fixed_pos: Boolean,
            init_no_open: Boolean,
            refilter_options: Number,
            is_disabled: Boolean,
        },
        computed: {
            selValue() {
                let elem = _.find(this.options, (opt) => {
                    return opt.val == this.tableRow[this.hdr_field]
                });
                return elem ? elem.show : this.tableRow[this.hdr_field];
            },
        },
        watch: {
            refilter_options(val) {
                this.filterOptions();
            }
        },
        methods: {
            //Standard DDL
            showPresent() {
                let arr = [];
                _.each(this.filtered_options, (opt) => {
                    if (this.isSelected(opt.val)) {
                        arr.push(opt.show || opt.val || '');
                    }
                });
                return arr.join(', ');
            },
            isSelected(key) {
                return this.multiselect
                    ? String(this.tableRow[this.hdr_field] || '').indexOf(String(key)) > -1
                    : this.tableRow[this.hdr_field] == key;
            },
            selectedItem(key, show) {
                show = !isNaN(show) ? String(show) : show;
                this.$emit('selected-item', key, show);
                if (!this.multiselect) {
                    this.hideSelect();
                }
            },
            filterOptions() {
                this.filtered_options = _.filter(this.options, (opt) => {
                    let res = true;
                    if (this.search_text) {
                        //case insensitive
                        res = String(opt.show).toLowerCase().indexOf( String(this.search_text).toLowerCase() ) > -1;
                    }
                    return res;
                });

                if (!this.init_no_open) {
                    this.showItemsList();
                }
            },
            emitEmbed() {
                this.$emit('embed-func');
                this.hideSelect();
            },
        },
        mounted() {
            this.filterOptions();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabldaSelect";
</style>