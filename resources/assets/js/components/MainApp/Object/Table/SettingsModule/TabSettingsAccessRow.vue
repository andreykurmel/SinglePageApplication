<template>
    <table class="spaced-table" :style="$root.themeMainBgStyle">
        <tbody v-if="requestFields" :style="textSysStyleSmart">

            <tr>
                <td :style="getTdStyle" class="flex flex--center">
                    <div class="td td--50 h-32 flex flex--center-v" ref="embd_wrapper" :style="getTdStyle">
                        <label>Embed:&nbsp;</label>
                        <embed-button class="embed_button btn btn-default embed__btn"
                                      :is-disabled="!requestRow.active"
                                      :is-dcr="true"
                                      :popup-style="customEmbdStyle"
                                      :hash="requestRow.link_hash || '#'"
                                      :style="textStyle"
                        ></embed-button>
                    </div>
                    <div class="td td--50 h-32" :style="getTdStyle"></div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle" class="flex flex--center">
                    <div class="td td--50 h-32 flex flex--center-v" :style="getTdStyle">
                        <label class="switch_t" style="display: inline-block;margin-right: 5px;">
                            <input type="checkbox" v-model="requestRow.stored_row_protection" :disabled="!with_edit" @change="updatedCell">
                            <span class="toggler round" :class="[!with_edit ? 'disabled' : '']"></span>
                        </label>
                        <label>Password protection for retrieving a saved or submitted form</label>
                    </div>
                    <div class="td td--50 h-32 flex flex--center-h" :style="getTdStyle">
                        <label>QR Code:&nbsp;</label>
                        <label class="switch_t" style="display: inline-block;margin-right: 5px;">
                            <input type="checkbox" v-model="requestRow.dcr_qr_with_name" :disabled="!with_edit" @change="updatedCell">
                            <span class="toggler round" :class="[!with_edit ? 'disabled' : '']"></span>
                        </label>
                        <label>Name:</label>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="flex">
                    <div class="td td--50 h-32" :style="getTdStyle">
                        <div v-show="requestRow.stored_row_protection" class="flex flex--center-v">
                            <label>Fields saving password:&nbsp;</label>
                            <select v-model="requestRow.stored_row_pass_id"
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
                    <div class="td td--50 flex flex--center">
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

    import EmbedButton from "../../../../Buttons/EmbedButton.vue";

    export default {
        components: {
            EmbedButton
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
                embdWi: 500,
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
            customEmbdStyle() {
                return {
                    left: '35px',
                    top: '-1px',
                    width: this.embdWi + 'px',
                };
            },
        },
        methods: {
        },
        mounted() {
            this.setAvailFields();
            setInterval(() => {
                if (this.$refs.embd_wrapper) {
                    let rect = this.$refs.embd_wrapper.getBoundingClientRect();
                    this.embdWi = rect && rect.width ? (rect.width - 105) : 500;
                }
            }, 2000);
        }
    }
</script>

<style lang="scss" scoped>
    @import "ReqRowStyle";
</style>