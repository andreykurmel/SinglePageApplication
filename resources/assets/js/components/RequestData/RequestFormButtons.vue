<template>
    <div class="new-row flex flex--center-v flex--space"
         :style="{
            backgroundColor: frm_color,
            boxShadow: box_shad,
            borderTopLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
            borderTopRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
            borderBottomLeftRadius: dcrObject.dcr_form_line_radius+'px',
            borderBottomRightRadius: dcrObject.dcr_form_line_radius+'px',
        }"
    >
        <div v-if="hidePart !== 'notes'" class="req-fields">
            <div>
                <span class="required-wildcart">*</span> Input Required
                <input type="checkbox" :checked="clearAfterSubmis" style="margin-left: 15px;" @click="clearSubmisClicked">
                <span>Clear the form after submission.</span>
            </div>
            <div>Never submit passwords through TablDA DCR Forms.</div>
        </div>
        <span v-else></span>

        <div v-if="hidePart !== 'buttons'" class="buttons-block">
            <button class="btn btn-success"
                    v-if="availSave(tableRow, dcrObject.one_per_submission != 1)"
                    :style="$root.themeButtonStyle"
                    :disabled="!canAddRow"
                    @click="btnHandler('Saved')"
            >Save{{ dcrObject.one_per_submission != 1 ? ' (All)' : '' }}</button>
            <button class="btn btn-success"
                    v-if="availSubmit(tableRow, dcrObject.one_per_submission != 1)"
                    :style="$root.themeButtonStyle"
                    :disabled="!canAddRow"
                    @click="btnHandler('Submitted')"
            >Submit{{ dcrObject.one_per_submission != 1 ? ' (All)' : '' }}</button>
            <button class="btn btn-success"
                    v-if="availUpdate(tableRow, dcrObject.one_per_submission != 1)"
                    :disabled="!hasChanges"
                    :style="$root.themeButtonStyle"
                    @click="btnHandler('Updated')"
            >Update{{ dcrObject.one_per_submission != 1 ? ' (All)' : '' }}</button>
            <button class="btn btn-success"
                    :style="$root.themeButtonStyle"
                    :disabled="!availableAdding"
                    v-if="availAdd(tableRow, dcrObject.one_per_submission != 1)"
                    @click="addRow('insert', allowUnfinish ? 'Saved' : 'Submitted')"
            >Add</button>
        </div>
    </div>
</template>

<script>
    import RequestMixin from "./RequestMixin.vue";

    export default {
        name: "RequestFormButtons",
        mixins: [
            RequestMixin,
        ],
        components: {
        },
        data: function () {
            return {
            };
        },
        props:{
            hidePart: String,
            hasChanges: Boolean,
            tableRow: Object|null,
            canAddRow: Boolean|Number,
            dcrObject: Object,
            frm_color: String,
            box_shad: String,
            clearAfterSubmis: Boolean,
            availableAdding: Boolean,
        },
        methods: {
            btnHandler(status) {
                if (this.dcrObject.one_per_submission == 1) {
                    this.addRow('submit', status);
                } else {
                    this.storeRows(status);
                }
            },
            addRow(param, new_status) {
                this.$emit('add-row', param, new_status);
            },
            clearSubmisClicked() {
                this.$emit('clear-submission-changed', !this.clearAfterSubmis);
            },
            storeRows(status) {
                this.$emit('store-rows-click', status);
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .new-row {
        padding: 10px;
        background-color: #fff;

        .req-fields {
            font-size: 14px;
            font-style: italic;
            color: #F00;
        }
    }

    @media all and (max-width: 767px) {
        .new-row {
            display: block;
        }
        .buttons-block {
            text-align: right;
        }
    }
</style>