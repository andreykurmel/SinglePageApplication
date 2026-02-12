<template>
    <div class="select-wrapper border-radius--top">

        <!--For using in Forms-->
        <input v-if="hidden_name" type="hidden" :name="hidden_name" :value="sel_value || ''"/>

        <!--Select-->
        <div class="select-element" @click="opened = !opened">
            <div class="element-value">
                <span>{{ show_value }}</span>
            </div>
            <div class="element-triangle">
                <b :class="[opened ? 'b-opened' : '']"></b>
            </div>
        </div>

        <!--Options-->
        <div v-if="opened" class="select-results border-radius--bottom">
            <div class="filter-wrapper">
                <input class="form-control" v-model="search_text" @keyup="filterOptions" placeholder="Search"/>
            </div>

            <div class="result-wrapper">
                <template v-for="opt in filtered_options">

                    <div class="result-item"
                         :class="[isSelected(opt.name) ? 'result-item--selected' : '']"
                         @click="tzChanged(opt.name, opt.full)"
                    >{{ opt.full || '&nbsp;' }}</div>

                </template>
            </div>
        </div>

    </div>
</template>

<script>
    import {MomentTzHelper} from "./classes/helpers/MomentTzHelper";

    export default {
        name: "VanguardTimezone",
        data: function () {
            return {
                filtered_options: [],
                timezones: MomentTzHelper.timezones(),
                sel_value: this.cur_tz ? this.cur_tz : moment.tz.guess(),
                show_value: '',
                opened: false,
                search_text: '',
            }
        },
        props:{
            hidden_name: String,
            cur_tz: String,
        },
        methods: {
            tzChanged(val, show) {
                this.sel_value = val;
                this.show_value = show;
                this.$emit('changed-tz', this.sel_value);
                this.opened = false;
            },
            isSelected(val) {
                return String(this.sel_value).toLowerCase() === String(val).toLowerCase();
            },
            filterOptions() {
                this.filtered_options = _.filter(this.timezones, (opt) => {
                    let res = true;
                    if (this.search_text) {
                        //case insensitive
                        res = String(opt.name).toLowerCase().indexOf( String(this.search_text).toLowerCase() ) > -1;
                    }
                    return res;
                });
            },
        },
        mounted() {
            let tz = _.find(this.timezones, {name: this.sel_value});
            this.show_value = tz ? tz.full : this.sel_value;

            this.filterOptions();
        }
    }
</script>

<style scoped lang="scss">
    .border-radius--top {
        border-radius: 5px 5px 0 0;
    }

    .border-radius--bottom {
        border-radius: 0 0 5px 5px;
    }

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
        top: 0;
        right: 0;
        border: 1px solid #CCC !important;
        border-radius: 5px !important;
        box-sizing: border-box;
        user-select: none;

        .select-element {
            height: 100%;
            display: flex;
            align-items: center;

            .element-value {
                max-width: calc(100% - 20px);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .element-triangle {
                position: absolute;
                top: 1px;
                right: 1px;
                width: 20px;
                height: 100%;

                b {
                    border-color: #888 transparent transparent transparent;
                    border-style: solid;
                    border-width: 5px 4px 0 4px;
                    height: 0;
                    left: 50%;
                    margin-left: -4px;
                    margin-top: -2px;
                    position: absolute;
                    top: 50%;
                    width: 0;
                }
                .b-opened {
                    border-color: transparent transparent #888 transparent;
                    border-width: 0 4px 5px 4px;
                }
            }
        }

        .select-results {
            position: absolute;
            width: 100%;
            top: 100%;
            right: 0;
            border: inherit;
            background-color: inherit;
            overflow: hidden;
            z-index: 1500;

            .filter-wrapper {
                padding: 3px;
                border-bottom: inherit;

                .form-control {
                    padding: 2px 3px;
                    line-height: inherit;
                    font-size: inherit;
                    height: auto;
                }
            }

            .result-wrapper {
                max-height: 150px;
                overflow: auto;

                .result-item {
                    padding: 2px 3px;
                    cursor: pointer;
                    line-height: 1em;

                    &:hover {
                        background-color: inherit;
                        color: inherit;
                        text-decoration: underline;
                    }
                }

                .result-item--selected {
                    background-color: #ddd;
                }
            }
        }
    }
</style> 