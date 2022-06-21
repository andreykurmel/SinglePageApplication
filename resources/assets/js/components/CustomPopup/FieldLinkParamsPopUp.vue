<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            [{{ tableMeta.name }}] <span v-html="getPopupHeader()"></span> - Link Params
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close', false)"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">
                    <div class="flex__elem__inner popup-main">
                        <div class="popup-overflow">
                            <custom-table
                                    :cell_component_name="'custom-cell-display-links'"
                                    :global-meta="tableMeta"
                                    :table-meta="settingsMeta['table_field_link_params']"
                                    :all-rows="linkRow._params"
                                    :rows-count="linkRow._params.length"
                                    :cell-height="1"
                                    :is-full-width="true"
                                    :behavior="'settings_display_links'"
                                    :user="user"
                                    :adding-row="addingRow"
                                    @added-row="addLinkParam"
                                    @updated-row="updateLinkParam"
                                    @delete-row="deleteLinkParam"
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
        name: "FieldLinkParamsPopUp",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            CustomTable
        },
        data: function () {
            return {
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                //PopupAnimationMixin
                getPopupWidth: 768,
                idx: 0,
            };
        },
        props:{
            tableMeta: Object,
            linkRow: Object,
            settingsMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            user: Object,
            cellHeight: Number,
        },
        methods: {
            getPopupHeader() {
                let fld = _.find(this.tableMeta._fields, {id: Number(this.linkRow.table_field_id)});
                return this.$root.uniqName( fld ? fld.name : '' );
            },
            //Link Param Functions
            addLinkParam(tableRow) {
                $.LoadingOverlay('show');

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link/param', {
                    table_field_link_id: this.linkRow.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.linkRow._params.push(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateLinkParam(tableRow) {
                $.LoadingOverlay('show');

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link/param', {
                    table_field_link_param_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteLinkParam(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/settings/data/link/param', {
                    params: {
                        table_field_link_param_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.linkRow._params, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.linkRow._params.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
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

    .popup {
        width: 768px;
    }
</style>