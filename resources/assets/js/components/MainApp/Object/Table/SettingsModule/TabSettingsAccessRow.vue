<template>
    <table class="spaced-table">
        <tbody v-if="requestFields">

            <tr>
                <td :style="getTdStyle" class="flex flex--center">
                    <div class="td td--100 h-32 flex flex--center-v" :style="getTdStyle">
                        <label class="switch_t" style="display: inline-block;margin-right: 5px;">
                            <input type="checkbox" v-model="requestRow['stored_row_protection']" :disabled="!with_edit" @change="updatedCell">
                            <span class="toggler round" :class="[!with_edit ? 'disabled' : '']"></span>
                        </label>
                        <label>{{ $root.uniqName(requestFields['stored_row_protection'].name) }}:&nbsp;</label>
                    </div>
                </td>
            </tr>
            <tr v-if="requestRow['stored_row_protection']">
                <td :style="getTdStyle">
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div class="flex flex--center-v full-height">
                            <label>{{ $root.uniqName(requestFields['stored_row_pass_id'].name) }}:&nbsp;</label>
                            <select v-model="requestRow['stored_row_pass_id']"
                                    :disabled="!with_edit"
                                    @change="updatedCell"
                                    class="form-control"
                                    :style="textSysStyle"
                            >
                                <option :value="null" style="color: #bbb;">Select a String field</option>
                                <option v-for="field in tableMeta._fields"
                                        v-if="!$root.inArraySys(field.f_type, ['Attachment'])"
                                        :value="field.id" style="color: #444;"
                                >{{ $root.uniqName(field.name) }}</option>
                            </select>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div style="border: 1px solid #ccc; margin: 10px 0;"></div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle" class="flex flex--center">
                    <div class="td td--25 h-32 flex flex--center-v" :style="getTdStyle">
                        <label>Embed:</label>
                    </div>
                    <div class="td td--75 h-32 flex flex--center" :style="getTdStyle">
                        <embed-button class="embed_button btn btn-default embed__btn"
                                      :is-disabled="!requestRow.active"
                                      :is-dcr="true"
                                      :hash="getLinkHash(true)"
                                      :style="textStyle"
                        ></embed-button>
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div style="border: 1px solid #ccc; margin: 10px 0;"></div>
                </td>
            </tr>

            <tr>
                <td class="flex flex--center">
                    <div class="td td--25 flex flex--center-v" :style="getTdStyle">
                        <label>QR Code:</label>
                    </div>
                    <div class="td td--75 flex flex--center-h">
                        <img v-if="requestRow.qr_link" :src="requestRow.qr_link" width="300" height="300">
                        <span v-else>Construction...</span>
                    </div>
                </td>
            </tr>

        </tbody>
    </table>
</template>

<script>
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";
    import ReqRowMixin from "./ReqRowMixin.vue";

    import EmbedButton from "../../../../Buttons/EmbedButton";

    export default {
        components: {
            EmbedButton,
        },
        mixins: [
            CellStyleMixin,
            ReqRowMixin,
        ],
        name: "TabSettingsAccessRow",
        data: function () {
            return {
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
            };
        },
        props:{
            tableMeta: Object,
            tableRequest: Object,
            requestRow: Object,
            with_edit: Boolean,
            //CellStyleMixin
            cellHeight: Number,
            maxCellRows: Number,
        },
        computed: {
            getTdStyle() {
                return {
                    height: this.tdCellHGT+'px',
                    ...this.textSysStyle,
                };
            },
        },
        methods: {
            getLinkHash() {
                return this.requestRow.link_hash || '#';
            },
        },
        mounted() {
            this.setAvailFields();
        }
    }
</script>

<style lang="scss" scoped>
    @import "ReqRowStyle";
</style>