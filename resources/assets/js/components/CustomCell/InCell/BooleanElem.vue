<template>
    <div v-if="!tableHeader._links || !tableHeader._links.length"
         class="flex flex--center-h no-wrap">
        <label v-if="String(tableHeader.f_format).startsWith('Slider')"
               :style="{height: '17px', margin: 0}"
               class="switch_t">
            <input :checked="getVal()"
                   :disabled="!canCellEdit"
                   type="checkbox"
                   @click="updateCheckBox()">
            <span :class="[!canCellEdit ? 'disabled' : '']" class="toggler round"></span>
        </label>
        <span v-else class="indeterm_check__wrap">
            <span :class="{'disabled': !canCellEdit}"
                  class="indeterm_check checkbox-input"
                  @click="updateCheckBox()"
            >
                <i v-if="getVal()" class="glyphicon glyphicon-ok group__icon"></i>
            </span>
        </span>

        <span v-html="booleanLabel()"></span>
    </div>
    <i v-else class="glyphicon glyphicon-play" @click="updateCheckBox()"></i>
</template>

<script>
    export default {
        name: "BooleanElem",
        data: function () {
            return {
            }
        },
        props: {
            tableHeader: Object,
            editValue: String|Number,
            canCellEdit: Boolean,
        },
        computed: {
        },
        watch: {
        },
        methods: {
            getVal() {
                return to_float(this.editValue);
            },
            updateCheckBox() {
                this.$emit('update-checkbox', this.getVal() ? 0 : 1);
            },
            booleanLabel() {
                let formatR = String(this.tableHeader.f_format).split('/')[1] || '';
                let labels = String(formatR).split(',');
                let result = !!this.editValue ? (labels[0] || '') : (labels[1] || '');
                return result ? '&nbsp;' + result : '';
            },
        },
    }
</script>

<style lang="scss" scoped>
.checkbox-input {
    margin: 0 !important;
    width: 14px;
    height: 14px;
}
</style>