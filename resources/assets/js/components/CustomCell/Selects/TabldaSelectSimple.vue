<template>
    <div class="select-wrapper border-radius--top" ref="select_wrapper" @click.stop="">
        <div class="select-element" @click="chngOpened()">
            <div class="element-value">{{ showPresent() }}</div>
            <div class="element-triangle">
                <b :class="[opened ? 'b-opened' : '']"></b>
            </div>
        </div>

        <div v-if="opened" class="select-results border-radius--bottom" :style="ItemsListStyle()">
            <div v-if="allowed_tags || allowed_search" class="filter-wrapper">
                <input class="form-control" v-model="search_text" @keyup="filterOptions" placeholder="Search"/>
            </div>

            <div class="result-wrapper" :style="{maxHeight: this.$root.ddlHeight+'px'}">
                <div v-if="can_empty"
                     class="result-item"
                     @click="selectedItem( '' )"
                >&nbsp;</div>

                <div v-if="allowed_tags && search_text.length"
                     class="result-item"
                     @click="selectedItem( search_text )"
                >{{ search_text }}</div>

                <div v-for="opt in filtered_options"
                     class="result-item"
                     :title="init_no_open ? opt.val : opt.hover"
                     :class="{'result-item--selected': isSelected(opt), 'result-item--disabled': opt.disabled}"
                     :style="opt.style"
                     @click="!opt.isTitle && !opt.disabled ? selectedItem( opt.val, opt.show ) : null"
                >
                    <img v-if="opt.img" :title="opt.hover" :src="$root.fileUrl({url:opt.img}, 'sm')" height="14">

                    <span v-if="opt.html" :title="init_no_open ? opt.val : opt.hover" v-html="opt.html"></span>
                    <span v-else :title="opt.hover">{{ opt.show || opt.val || '&nbsp;' }}</span>
                </div>
            </div>

            <div v-if="embed_func_txt" class="embed-wrapper">
                <button class="btn btn-xs btn-success full-width" @click="emitEmbed()">{{ embed_func_txt }}</button>
            </div>
        </div>

    </div>
</template>

<script>
    import MixinSmartPosition from '../../_Mixins/MixinSmartPosition.vue';

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
            options: Array, // available: { val:'id', show:string, html:string, style:object, isTitle:bool, disabled:bool }
            tableRow: Object,
            hdr_field: String,
            fld_input_type: String,
            can_empty: Boolean,
            allowed_tags: Boolean,
            allowed_search: Boolean,
            embed_func_txt: String,
            fixed_pos: Boolean,
            init_no_open: Boolean,
            refilter_options: Number,
            is_disabled: Boolean,
        },
        watch: {
            refilter_options(val) {
                this.filterOptions();
            }
        },
        methods: {
            chngOpened() {
                this.opened = !this.opened;
                if (!this.opened) {
                    this.$emit('hide-select');
                }
            },
            //Standard DDL
            showPresent() {
                let arr = [];
                _.each(this.filtered_options, (opt) => {
                    if (this.isSelected(opt)) {
                        arr.push(opt.show || opt.val || '');
                    }
                });
                return arr.join(', ');
            },
            isSelected(option) {
                if (!option || option.isTitle) {
                    return false;
                }
                let field_val = this.tableRow[this.hdr_field] || '';
                field_val = typeof field_val == 'object' ? JSON.stringify(field_val) : field_val;
                return this.multiselect
                    ? field_val.indexOf(isNaN(option.val) ? '"'+String(option.val)+'"' : Number(option.val)) > -1
                    : field_val == option.val;
            },
            selectedItem(key, show) {
                show = isNumber(show) ? String(show) : show;
                this.$emit('selected-item', key, show);
                this.search_text = '';
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