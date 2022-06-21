<template>
    <div class="fields_checker">
        <div>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="$emit('toggle-all')">
                        <i v-if="allChecked == 2" class="glyphicon glyphicon-ok group__icon"></i>
                        <i v-if="allChecked == 1" class="glyphicon glyphicon-minus group__icon"></i>
                    </span>
                </span>
                <span> Check/Uncheck All</span>
            </label>
        </div>
        <div v-for="fld in tableMeta._fields" v-if="canViewHdr(fld)">
            <label :style="{backgroundColor: (checkFunc(fld) ? '#CCC;' : '')}">
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="$emit('toggle-column', fld)">
                        <i v-if="checkFunc(fld)" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span> {{ $root.uniqName(fld.name) }}</span>
            </label>
        </div>
    </div>
</template>

<script>
    import CanEditMixin from '../_Mixins/CanViewEditMixin.vue';
    import IsShowFieldMixin from '../_Mixins/IsShowFieldMixin.vue';

    export default {
        name: "FieldsChecker",
        mixins: [
            CanEditMixin,
            IsShowFieldMixin,
        ],
        data: function () {
            return {}
        },
        props:{
            tableMeta: Object,
            allChecked: Number|Boolean,
            checkFunc: Function,
            only_edit: Boolean,
            only_columns: Array,
        },
        methods: {
            canViewHdr(col) {
                return (!this.only_columns || in_array(col.field, this.only_columns))
                    && (this.only_edit ? this.canEditHdr(col) : this.isShowField(col, true));
            },
        },
    }
</script>

<style scoped>
    .fields_checker {
        /**/
    }
    .fields_checker > div {
        border-bottom: 1px dashed #CCC;
        display: flex;
    }
    .fields_checker > div > label {
        white-space: nowrap;
        font-size: 0.7em;
        margin: 0;
    }
</style>