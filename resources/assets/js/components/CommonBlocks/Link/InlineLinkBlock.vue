<template>
    <div class="full-height" v-if="available" :class="[link.link_type === 'Record' ? '' : 'over-hidden']">
        <link-displayer
            v-if="link.link_type === 'Record'"
            :source-meta="tbMeta"
            :link="link"
            :meta-header="tbHeader"
            :meta-row="tbRow"
            :view_authorizer="{
                mrv_marker: $root.is_mrv_page,
                srv_marker: $root.is_srv_page,
                dcr_marker: $root.is_dcr_page,
                view_hash: $root.user.view_hash,
                is_folder_view: $root.user._is_folder_view,
            }"
            :with_edit="with_edit"
            :inlined="true"
            :external-view-type="view_type"
            :external_align="external_align"
            @show-src-record="showSrcRecord"
            @max-height-changed="maxHeightChanged"
            @loaded-link="loadedLink"
        ></link-displayer>

        <iframe
            v-else-if="link.link_type === 'GMap' || link.link_type === 'GEarth'"
            :src="mapSrc('embed')"
            width="100%"
            :height="Number(link.max_height_in_vert_table - 2)+'px'"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>

        <history-elem
            v-if="link.link_type === 'History'"
            :user="$root.user"
            :table-meta="tbMeta"
            :history-header="historyHeader"
            :table-row="tbRow"
            :redraw_history="redraw_history"
            :can-add="!!link.can_row_add"
            :can-del="!!link.can_row_delete"
            @hist-updated="histUpdated"
        ></history-elem>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../classes/SpecialFuncs";
    import {MapHelper} from "../../../classes/helpers/MapHelper";

    import LinkDisplayer from "./LinkDisplayer";
    import HistoryElem from "../HistoryElem";

    export default {
        name: 'InlineLinkBlock',
        mixins: [
        ],
        components: {
            HistoryElem,
            LinkDisplayer,
        },
        data() {
            return {
                available: false,
            }
        },
        props: {
            link: Object,
            tbRow: Object,
            tbHeader: Object,
            tbMeta: Object,
            view_type: String,
            external_align: String,
            redraw_history: Number,
            with_edit: {
                type: Boolean,
                default: true
            },
        },
        computed: {
            historyHeader() {
                return _.find(this.tbMeta._fields, {id: Number(this.link.history_fld_id)});
            },
        },
        methods: {
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'link');
            },
            maxHeightChanged(height) {
                this.$emit('max-height-changed', height);
            },
            mapSrc(type) {
                return MapHelper.gmapLink(this.tbMeta, this.tbRow, this.link, type);
            },
            histUpdated() {
                this.$emit('hist-updated');
            },
            loadedLink() {
                this.$emit('loaded-link');
            },
        },
        mounted() {
            if (this.link._c_value === undefined) {
                this.link._c_value = SpecialFuncs.getEditValue(this.tbHeader, this.tbRow[this.tbHeader.field]);
                if (typeof this.link._c_value == 'object') {
                    this.link._c_value = _.first(this.link._c_value);
                }
            }
            this.available = true;

            if (this.link.link_type !== 'Record') {
                this.loadedLink();
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
</style>