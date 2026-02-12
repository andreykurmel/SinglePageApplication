<template>
    <div class="select-wrapper border-radius--top"
         ref="select_wrapper"
         @click.stop=""
         :style="{
            backgroundColor: is_disabled ? '#ddd' : '',
            height: multi_lines ? 'auto' : '36px',
            minHeight: multi_lines ? '36px' : 'auto',
        }">

        <!--For using in Forms-->
        <input v-if="hidden_name" type="hidden" :name="hidden_name" :value="String(sel_value) || ''"/>

        <!--Select-->
        <div class="select-element" @click="openMenu()" :style="{ minHeight: multi_lines ? '22px' : 'auto', }">
            <div v-if="placeholder && !String(sel_value)" class="element-value" style="color: #aaa;">{{ placeholder }}</div>
            <div v-else class="element-value">
                <img v-if="sel_image && showPresent()" :src="sel_image" height="14">

                <a v-if="with_links" @click.stop="$emit('link-click')" v-html="showPresent()"></a>
                <a v-else-if="link_path" target="_blank" :href="link_path" @click.stop="" v-html="showPresent()"></a>
                <span v-else="" v-html="showPresent()"></span>
            </div>
            <div class="element-triangle">
                <b :class="[opened ? 'b-opened' : '']"></b>
            </div>
        </div>

        <!--Options-->
        <div v-if="opened" class="select-results border-radius--bottom" :style="ItemsListStyle()">
            <div v-if="can_search" class="filter-wrapper">
                <input class="form-control" v-model="search_text" @keyup="filterOptions" placeholder="Search"/>
            </div>

            <div class="result-wrapper" :style="{maxHeight: this.$root.ddlHeight+'px'}">
                <template v-for="opt in filtered_options">

                    <div v-if="opt.isButton" class="embed-wrapper">
                        <button class="btn btn-xs btn-success full-width" @click="emitButton(opt.isButton)">{{ opt.show || opt.val || '&nbsp;' }}</button>
                    </div>

                    <div v-else=""
                         class="result-item"
                         :class="{'result-item--selected': isSelected(opt), 'result-item--disabled': opt.disabled}"
                         :style="opt.style"
                         @click="itemSelect(opt)"
                    >
                        <img v-if="opt.img" :src="opt.img" height="14">

                        <span v-if="opt.html" v-html="opt.html"></span>
                        <span v-else>{{ opt.show || opt.val || '&nbsp;' }}</span>
                    </div>

                </template>
            </div>

            <div v-if="button_txt" class="embed-wrapper">
                <button class="btn btn-xs btn-success full-width" @click="emitButton()">{{ button_txt }}</button>
            </div>
        </div>

    </div>
</template>

<script>
    import MixinSmartPosition from './../_Mixins/MixinSmartPosition.vue';

    export default {
        name: "SelectBlock",
        components: {
        },
        mixins: [
            MixinSmartPosition
        ],
        data: function () {
            return {
                search_text: '',
                filtered_options: [],
            }
        },
        props:{
            options: Array, // available: { val:'id', show:string, html:string, style:object, isTitle:bool, isButton:mixed, hasGroup:array, disabled:bool }
            sel_value: Array|String|Number,
            sel_image: String,
            can_search: Boolean,
            fixed_pos: Boolean,
            is_disabled: Boolean,
            is_multiselect: Boolean,
            multi_lines: Boolean,
            button_txt: String,
            hidden_name: String,
            with_links: Boolean,
            link_path: String,
            auto_open: Boolean,
            placeholder: String,
        },
        methods: {
            openMenu() {
                if (!this.is_disabled && !this.opened) {
                    this.filterOptions();
                } else {
                    this.opened = false;
                }
            },
            showPresent() {
                let arr = [];
                let from_group = [];
                _.each(this.options, (opt) => {
                    if (this.isSelected(opt)) {
                        if (opt.hasGroup) {
                            from_group = from_group.concat(opt.hasGroup);
                        }
                        arr.push(opt.show || opt.val || opt.html || '');
                    }
                });
                return arr.filter((el) => {
                    return from_group.indexOf(el) === -1;
                }).join(this.multi_lines ? ',<br>' : ', ');
            },
            isSelected(option) {
                if (option.isTitle || option.isButton) {
                    return false;
                }

                let result = true;
                let field_val = typeof this.sel_value == 'object' ? JSON.stringify(this.sel_value) : this.sel_value;
                _.each(option.hasGroup || [option.val], (optval) => {
                    let part = this.is_multiselect
                        ? String(field_val || '').indexOf(isNaN(optval) ? '"'+String(optval)+'"' : Number(optval)) > -1
                        : field_val == optval;
                    result = result && part;
                });
                return result;
            },
            itemSelect(option) {
                if (option.disabled || option.isTitle) {
                    return null;
                }

                this.$emit('option-select', option);
                this.search_text = '';
                if (!this.is_multiselect) {
                    this.hideSelect();
                }
            },
            filterOptions(just_filter) {
                this.filtered_options = _.filter(this.options, (opt) => {
                    let res = true;
                    if (this.search_text) {
                        //case insensitive
                        res = String(opt.show).toLowerCase().indexOf( String(this.search_text).toLowerCase() ) > -1;
                    }
                    return res;
                });
                if (!just_filter) {
                    this.showItemsList();
                }
            },
            emitButton(key) {
                this.$emit('button-click', key);
                this.hideSelect();
            },
        },
        mounted() {
            this.filterOptions( !this.auto_open );
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "../CustomCell/Selects/TabldaSelect";
    //to standard block
    .select-wrapper {
        position: relative;
        z-index: auto;
        display: block;
        width: 100%;
        height: 36px;
        padding: 6px 12px !important;
        font-size: 14px;
        line-height: 1.6;
        color: #555;
        background-color: #fff;
        background-image: none;

        .select-results {

            .result-wrapper {
                max-height: 150px;

                .result-item {
                    padding: 3px;
                    cursor: pointer;
                    line-height: 1em;

                    &:hover {
                        background-color: inherit;
                        color: inherit;
                        text-decoration: underline;
                    }
                }

                .result-item--selected {
                    background-color: #ddd !important;
                }

                .result-item--disabled {
                    background-color: #ccc !important;
                    color: #777;
                    cursor: not-allowed;

                    &:hover {
                        text-decoration: none;
                    }
                }
            }
        }
    }
</style>