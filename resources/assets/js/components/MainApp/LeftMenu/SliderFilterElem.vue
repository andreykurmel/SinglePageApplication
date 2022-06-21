<template>
    <div class="full-frame">
        <div ref="jquery_slider" class="slider-wrapper"></div>
        <div class="form-group">
            <label>{{ filter_values.min.val }}</label>
            <label class="right">{{ filter_values.max.val }}</label>
        </div>

        <div v-if="isDate" class="form-group">
            <table>
                <tr>
                    <td>
                        <label>Range:</label>
                    </td>
                    <td>
                        <input class="form-control" v-model="filter_values.min.selected" @change="changedInput()"/>
                    </td>
                    <td rowspan="2" v-show="filter_values.min.selected != filter_values.min.val || filter_values.max.selected != filter_values.max.val">
                        <button class="btn btn-sm btn-danger" title="Clear filter" @click="clearFilter()">&times;</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>To</label>
                    </td>
                    <td>
                        <input class="form-control" v-model="filter_values.max.selected" @change="changedInput()"/>
                    </td>
                </tr>
            </table>
        </div>
        <div v-else="" class="form-group flex flex--space flex--center-v">
            <label>Range: </label>
            <input class="form-control" v-model="filter_values.min.selected" @change="changedInput()"/>
            <label> To </label>
            <input class="form-control" v-model="filter_values.max.selected" @change="changedInput()"/>
            <button
                    v-show="filter_values.min.selected != filter_values.min.val || filter_values.max.selected != filter_values.max.val"
                    class="btn btn-sm btn-default blue-gradient"
                    title="Clear filter"
                    :style="$root.themeButtonStyle"
                    @click="clearFilter()"
            >&times;</button>
        </div>

    </div>
</template>

<script>
    export default {
        name: 'SliderFilterElem',
        mixins: [
        ],
        data() {
            return {
            }
        },
        props: {
            filter_values: Object,
            f_type: String,
        },
        computed: {
            isDate() {
                return this.f_type === 'Date' || this.f_type === 'Date Time';
            }
        },
        methods: {
            changedInput() {
                $( this.$refs.jquery_slider ).slider( 'values', [
                    this.getVal(this.filter_values.min.selected),
                    this.getVal(this.filter_values.max.selected)
                ]);
                this.$emit('changed-range');
            },
            clearFilter() {
                this.filter_values.min.selected = this.filter_values.min.val;
                this.filter_values.max.selected = this.filter_values.max.val;
                this.changedInput();
            },
            getVal(val) {
                if (this.isDate && typeof val === 'string') {
                    return new Date(val).getTime() / 1000;
                } else
                if (this.isDate && typeof val === 'number') {
                    return moment(val*1000).format('YYYY-MM-DD');
                } else {
                    return Number(val);
                }
            }
        },
        mounted() {
            $( this.$refs.jquery_slider ).slider({
                range: true,
                min: this.getVal(this.filter_values.min.val),
                max: this.getVal(this.filter_values.max.val),
                values: [ this.getVal(this.filter_values.min.selected), this.getVal(this.filter_values.max.selected) ],
                slide: ( event, ui ) => {
                    this.filter_values.min.selected = this.getVal(ui.values[0]);
                    this.filter_values.max.selected = this.getVal(ui.values[1]);
                },
                stop: ( event, ui ) => {
                    this.changedInput();
                    $(this.$refs.jquery_slider).find('.ui-slider-handle').css(this.$root.themeButtonStyle);
                }
            });
            $(this.$refs.jquery_slider).find('.ui-slider-handle').css(this.$root.themeButtonStyle);
        },
    }
</script>

<style lang="scss" scoped>
    .right {
        float: right;
    }
    .slider-wrapper {
        margin: 15px 15px 5px 15px;
    }

    .flex--space {
        flex-wrap: wrap;

        .form-control {
            display: inline-block;
            width: 70px;
            height: 28px;
            padding: 3px 6px;
        }
        .btn-sm {
            padding: 0 7px;
            height: 28px;
        }
    }
    table {
        tr {
            td {
                padding: 3px;

                .btn-sm {
                    height: 75px;
                    font-size: 3em;
                    padding: 0;
                }
            }
        }
    }

    label {
        margin: 0;
    }
</style>