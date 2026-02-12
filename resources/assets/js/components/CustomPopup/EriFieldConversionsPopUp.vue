<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div :style="getPopupStyle()" class="popup">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @drag="dragPopup()" @dragstart="dragPopSt()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Conversion.</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn"
                                  @click="$emit('popup-close', false)"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div class="popup-overflow">
                            <custom-table
                                v-if="tableMeta && eriFieldRow && eriFieldRow._conversions"
                                :cell_component_name="'custom-cell-display-links'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_field_link_eri_field_conversions']"
                                :all-rows="eriFieldRow._conversions || []"
                                :rows-count="eriFieldRow._conversions.length"
                                :parent-row="eriFieldRow"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :behavior="'settings_display_links'"
                                :user="$root.user"
                                :adding-row="{
                                    active: true,
                                    position: 'bottom'
                                }"
                                :use_theme="true"
                                :headers-changer="{
                                    eri_convers: eriFieldRow.eri_variable,
                                    tablda_convers: getTabldaField(),
                                }"
                                @added-row="addEriFieldConversion"
                                @updated-row="updateEriFieldConversion"
                                @delete-row="deleteEriFieldConversion"
                            ></custom-table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CustomTable from './../CustomTable/CustomTable';

import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

export default {
    name: "EriFieldConversionsPopUp",
    mixins: [
        PopupAnimationMixin,
    ],
    components: {
        CustomTable
    },
    data: function () {
        return {
            tabldaField: '',
            //PopupAnimationMixin
            getPopupWidth: 500,
            idx: 0,
        };
    },
    props: {
        tableMeta: Object,
        eriFieldRow: Object,
        eriTableRow: Object,
    },
    methods: {
        getTabldaField() {
            if (!this.tabldaField) {
                let meta = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.eriTableRow.eri_table_id)});
                let fld = _.find(meta._fields, {id: Number(this.eriFieldRow.eri_field_id)}) || {};
                this.tabldaField = fld.name || this.eriFieldRow.eri_field_id;
            }
            return this.tabldaField;
        },
        //Link Eri Field Conversion Functions
        addEriFieldConversion(tableRow) {
            this.$root.sm_msg_type = 1;

            let fields = _.cloneDeep(tableRow);//copy object
            this.$root.deleteSystemFields(fields);

            axios.post('/ajax/settings/data/link/eri-field/conversion', {
                link_eri_field_id: this.eriFieldRow.id,
                fields: fields,
            }).then(({ data }) => {
                this.eriFieldRow._conversions.push(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                this.$root.sm_msg_type = 0;
            });
        },
        updateEriFieldConversion(tableRow) {
            this.$root.sm_msg_type = 1;

            let group_id = tableRow.id;
            let fields = _.cloneDeep(tableRow);//copy object
            this.$root.deleteSystemFields(fields);

            axios.put('/ajax/settings/data/link/eri-field/conversion', {
                link_eri_field_conv_id: group_id,
                fields: fields
            }).then(({ data }) => {
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                this.$root.sm_msg_type = 0;
            });
        },
        deleteEriFieldConversion(tableRow) {
            this.$root.sm_msg_type = 1;
            axios.delete('/ajax/settings/data/link/eri-field/conversion', {
                params: {
                    link_eri_field_conv_id: tableRow.id
                }
            }).then(({ data }) => {
                let idx = _.findIndex(this.eriFieldRow._conversions, {id: Number(tableRow.id)});
                if (idx > -1) {
                    this.eriFieldRow._conversions.splice(idx, 1);
                }
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                this.$root.sm_msg_type = 0;
            });
        },
    },
    mounted() {
        this.runAnimation();
    }
}
</script>

<style lang="scss" scoped>
@import "CustomEditPopUp";
</style>