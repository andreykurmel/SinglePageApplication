<template>
    <span v-if="canSRV(tableMeta)">
        <a v-if="availSRV()" :href="linkSRV()" target="_blank" @click="linkClick">
            <span class="srv" title="To open the Single-Record View (SRV) for this record.">S</span>
        </a>
        <span v-else class="srv" style="cursor: not-allowed;color: #aaa;">S</span>

        <span v-if="withDelimiter"> | </span>
    </span>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import SrvMixin from "../_Mixins/SrvMixin.vue";

    export default {
        name: "SrvBlock",
        mixins: [
            SrvMixin,
        ],
        components: {
        },
        data: function () {
            return {
            };
        },
        computed: {
        },
        props:{
            tableRow: Object,
            tableMeta: Object,
            withDelimiter: Boolean,
        },
        methods: {
            //Single record view
            availSRV() {
                let fld = _.find(this.tableMeta._fields, {id: Number(this.tableMeta.single_view_status_id)});
                return this.canSRV(this.tableMeta) && (!fld || !!this.tableRow[fld.field]);
            },
            linkSRV() {
                return this.$root.clear_url + '/srv/' + this.tableMeta.hash + '#' + this.tableRow['static_hash'];
            },
            linkClick(e) {
                if (e.ctrlKey) {
                    e.preventDefault();
                    SpecialFuncs.strToClipboard(this.linkSRV());
                    Swal('SRV URL Copied to Clipboard!');
                }
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .srv {
        font-weight: bold;
        color: #039;
    }
</style>