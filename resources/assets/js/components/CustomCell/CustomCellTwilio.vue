<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
    >
        <div class="td-wrapper" :style="getTdWrappStyle()">
            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content">
                    <span v-html="showField()"></span>
                    <i v-if="parentRow
                            && parentRow.twilio_phone != tableRow.content[tableHeader.field]
                            && $root.inArray(tableHeader.field, ['call_from','call_to'])"
                       class="fas fa-phone green"
                       style="cursor: pointer;"
                       @click="$emit('call-back', tableRow.content[tableHeader.field])"
                    ></i>
                </div>
            </div>
        </div>
    </td>
</template>

<script>
import {SpecialFuncs} from '../../classes/SpecialFuncs';

import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

export default {
    components: {
    },
    name: "CustomCellTwilio",
    mixins: [
        CellStyleMixin,
    ],
    data: function () {
        return {
        }
    },
    props:{
        tableMeta: Object,
        tableHeader: Object,
        tableRow: Object,
        rowIndex: Number,
        cellHeight: Number,
        maxCellRows: Number,
        user: Object,
        parentRow: Object,
        cellValue: String|Number,
    },
    watch: {
    },
    computed: {
        getCustomCellStyle() {
            let obj = this.getCellStyle();
            obj.textAlign = (this.$root.inArray(this.tableHeader.f_type, ['Boolean', 'Radio']) ? 'center' : '');
            return obj;
        },
    },
    methods: {
        showField() {
            let res = this.cellValue;
            if (this.$root.inArray(this.tableHeader.field, ['call_from','call_to','sms_from','sms_to'])) {
                res = this.tableRow.content ? this.tableRow.content[this.tableHeader.field] : res;
                res = this.$root.telFormat(res);
            }
            if (this.$root.inArray(this.tableHeader.field, ['call_duration'])) {
                res = this.tableRow.content ? this.tableRow.content[this.tableHeader.field] : res;
                res = SpecialFuncs.second2duration(parseFloat(res), {f_format:'m, s'}, true);
            }
            if (this.tableHeader.field === 'call_table') {
                res = this.tableMeta.name;
            }
            if (this.$root.inArray(this.tableHeader.field, ['email_from_email','email_to','email_reply_to','email_subject','email_body','call_start','sms_message'])) {
                res = this.tableRow.content ? this.tableRow.content[this.tableHeader.field] : res;
            }
            return this.$root.strip_danger_tags(res);
        },
    },
    mounted() {
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
@import "./CustomCell.scss";
</style>