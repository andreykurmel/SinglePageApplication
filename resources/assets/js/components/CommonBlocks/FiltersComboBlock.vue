<template>
    <div v-if="tableMeta"
         :class="{'wrap': fixedPos, 'full-height': placed !== 'top_filters'}"
         @mouseenter="() => { visibleMain = true; }"
         @mouseleave="() => { visibleMain = !fixedPos; }"
    >
        <div v-if="fixedPos" class="fixed-btn">Combos</div>

        <div v-show="visibleMain" :class="{'fixed-pos': fixedPos}">
            <label>Combinations of multiple filters:</label>
            <div v-for="(filter, idx) in tableMeta._saved_filters">
                <div class="flex mb5">
                    <div class="full-width pr3">
                        <input v-if="idx === currentCombo"
                               class="form-control"
                               style="background: #FFD;"
                               placeholder="Enter a custom name"
                               @change="$emit('curr-updated', true)"
                               v-model="filter.name"
                        >
                        <div v-else class="form-control pointer" @click="getSavedFilter(filter, idx)">{{ filter.name }}</div>
                    </div>
                    <button v-if="currChanged && idx === currentCombo"
                            class="btn btn-primary btn-sm w75 blue-gradient"
                            :style="$root.themeButtonStyle"
                            @click="updateSavedFilter(filter)"
                    >Update</button>
                    <button v-else class="btn btn-danger btn-sm w75" @click="deleteSavedFilter(filter)">Remove</button>
                </div>
            </div>
            <div class="flex">
                <div class="full-width" style="padding-right: 3px;">
                    <input class="form-control" placeholder="Enter a custom name" v-model="newCombo">
                </div>
                <button class="btn btn-primary btn-sm w75" @click="addSavedFilter">Save</button>
            </div>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../_Mixins/CellStyleMixin";

    export default {
        name: 'FiltersComboBlock',
        components: {
        },
        mixins: [
            CellStyleMixin,
        ],
        data() {
            return {
                currentCombo: -1,
                newCombo: '',
                newColGroup: null,
                visibleMain: !this.fixedPos,
            }
        },
        props: {
            tableMeta: Object,
            input_filters: Array,
            currChanged: Boolean,
            fixedPos: Boolean,
            placed: String,
        },
        computed: {
        },
        methods: {
            activateCombo(idx) {
                this.currentCombo = idx;
                this.$emit('curr-updated', false);
            },
            getSavedFilter(filter, idx) {
                if (idx === this.currentCombo) {
                    this.currentCombo = -1;
                    return;
                }
                axios.get('/ajax/saved-filter', {
                    params: {
                        model_id: filter.id,
                    }
                }).then(({data}) => {
                    this.$emit('applied-saved-filter', data.filters_object);
                    this.activateCombo(idx);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            addSavedFilter() {
                axios.post('/ajax/saved-filter', {
                    table_id: this.tableMeta.id,
                    fields: {
                        name: this.newCombo,
                        filters_object: this.input_filters,
                        related_colgroup_id: this.newColGroup,
                    },
                }).then(({data}) => {
                    this.tableMeta._saved_filters = data;
                    this.newCombo = '';
                    this.newColGroup = null;
                    this.activateCombo(this.tableMeta._saved_filters.length - 1);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            updateSavedFilter(filter) {
                if (!filter.name) {
                    Swal('Info', 'Empty name!');
                    return;
                }
                axios.put('/ajax/saved-filter', {
                    model_id: filter.id,
                    fields: {
                        name: filter.name,
                        related_colgroup_id: filter.related_colgroup_id,
                        filters_object: this.input_filters,
                    },
                }).then(({data}) => {
                    this.tableMeta._saved_filters = data;
                    this.activateCombo(this.currentCombo);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            deleteSavedFilter(filter) {
                axios.delete('/ajax/saved-filter', {
                    params: {
                        model_id: filter.id,
                    }
                }).then(({data}) => {
                    this.tableMeta._saved_filters = data;
                    this.currentCombo = -1;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
        },
    }
</script>

<style lang="scss" scoped>
    .wrap {
        position: relative;
        width: min-content;
    }
    .fixed-btn {
        margin-right: 3px;
        height: 32px;
        display: block;
        cursor: pointer;
        padding: 3px 10px;
        font-size: 14px;
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
    .fixed-pos {
        position: fixed;
        width: 250px;
        max-height: 80%;
        z-index: 1000;
        background: white;
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        overflow: auto;
    }

    .form-control {
        height: 30px;
        font-size: 12px;
        line-height: 1.5;
    }
    .w75 {
        width: 75px;
        flex-shrink: 0;
    }
</style>