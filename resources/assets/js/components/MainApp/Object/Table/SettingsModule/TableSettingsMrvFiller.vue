<template>
    <div style="background-color: inherit;" :style="textSysStyle">
        <div class="flex flex--center-v mb5">
            <label>The URL for the link to the records is composed of a MRV prefix and a custom or hash portion:</label>
        </div>
        <div class="flex flex--center-v mb5">
            <label class="labelwi">MRV (Data Range will be the linked records):</label>
            <select-block
                :options="mrvOpts()"
                :sel_value="selectedLink.share_mrv_id"
                :style="{ height:'32px', }"
                :with_links="true"
                @link-click="showMrvPopup()"
                @option-select="(obj) => { saveSelect('share_mrv_id', obj) }"
            ></select-block>
        </div>
        <div class="flex flex--center-v mb5">
            <div class="labelwi"></div>
            <input type="checkbox"
                   style="margin: 0 5px 0 0;"
                   v-model="selectedLink.share_can_custom"
                   @change="sendSave()">
            <label>Use custom URL for MRV</label>
        </div>
        <div class="flex flex--center-v mb5">
            <label class="labelwi">MRV {{ selectedLink.share_can_custom ? 'custom' : 'hash' }} prefix for the link URL:</label>
            <input disabled class="form-control" style="height: 32px" :value="calcPrefix()">
        </div>
        <div class="flex flex--center-v mb5">
            <label class="labelwi">Field for custom suffix for the link URL:</label>
            <select-block
                :options="fieldOpts()"
                :sel_value="selectedLink.share_custom_field_id"
                :style="{ height:'32px', }"
                @option-select="(obj) => { saveSelect('share_custom_field_id', obj) }"
            ></select-block>
        </div>
        <div class="flex flex--center-v mb5">
            <label class="labelwi"></label>
            <label style="font-style: italic;">
                Custom suffixes for the links can be manually entered or generated through formulas.
            </label>
        </div>
        <div class="flex flex--center-v mb5">
            <label class="labelwi">Field for hash suffix for the link URL:</label>
            <select-block
                :options="fieldOpts()"
                :sel_value="selectedLink.share_url_field_id"
                :style="{ height:'32px', }"
                @option-select="(obj) => { saveSelect('share_url_field_id', obj) }"
            ></select-block>
        </div>
        <div class="flex flex--center-v mb5">
            <label class="labelwi"></label>
            <label style="font-style: italic;">
                Hash suffixes for the links will be generated upon selecting a “String” type field.
            </label>
        </div>
        <div class="flex flex--center-v mb5 mt5">
            <div class="labelwi"></div>
            <input type="checkbox"
                   style="margin: 0 5px 0 0;"
                   v-model="selectedLink.share_custom_hash"
                   @change="sendSave()">
            <label>Use custom suffix for the link URL.</label>
        </div>
        <div class="flex flex--center-v mb5">
            <label class="labelwi">Associated "Web" type link for sharing:</label>
            <select-block
                :options="webOpts()"
                :sel_value="selectedLink.share_web_link_id"
                :style="{ height:'32px', }"
                @option-select="(obj) => { saveSelect('share_web_link_id', obj) }"
            ></select-block>
        </div>
        <div class="flex flex--center-v mb5">
            <label class="labelwi"></label>
            <label style="font-style: italic;">
                Upon selecting a "Web" type link, the MRV prefix and suffix of the link URL will be auto-populated to the corresponding fields in the “Web” type link’s Details.
            </label>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";

    import SelectBlock from "../../../../CommonBlocks/SelectBlock.vue";

    export default {
        name: "TableSettingsMrvFiller",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            SelectBlock,
        },
        data: function () {
            return {
            }
        },
        props: {
            tableMeta: Object,
            selectedLink: Object,
        },
        computed: {
            linkedMeta() {
                let refCond = _.find(this.tableMeta._ref_conditions, {id: this.selectedLink.table_ref_condition_id}) || {};
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(refCond.ref_table_id)});
            },
        },
        methods: {
            mrvOpts() {
                let views = this.linkedMeta ? this.linkedMeta._views : [];
                return _.map(views, (v) => {
                    return { val:v.id, show:v.name, };
                });
            },
            fieldOpts() {
                let fields = _.filter(this.tableMeta._fields, (fld) => {
                    return this.$root.systemFieldsNoId.indexOf(fld.field) === -1;
                });
                return _.map(fields, (v) => {
                    return { val:v.id, show:v.name, };
                });
            },
            webOpts() {
                let webLinks = [];
                _.each(this.tableMeta._fields, (fld) => {
                    _.each(fld._links, (lnk) => {
                        if (lnk.link_type === 'Web') {
                            webLinks.push({ val:lnk.id, show:lnk.name, })
                        }
                    });
                });
                return webLinks;
            },
            webLink() {
                let webLink = [];
                _.each(this.tableMeta._fields, (fld) => {
                    _.each(fld._links, (lnk) => {
                        if (lnk.id === this.selectedLink.share_web_link_id) {
                            webLink = lnk;
                        }
                    });
                });
                return webLink;
            },
            saveSelect(key, opt) {
                this.selectedLink[key] = opt.val;
                this.sendSave();
            },
            sendSave() {
                this.$emit('updated-row', this.selectedLink);

                window.setTimeout(() => {
                    if (this.selectedLink.share_mrv_id && this.selectedLink.share_url_field_id && this.selectedLink.share_web_link_id) {
                        this.fillByUrl();
                    }
                }, 1000);
            },
            calcPrefix() {
                let views = this.linkedMeta ? this.linkedMeta._views : [];
                let mrv = _.find(views, {id: Number(this.selectedLink.share_mrv_id)}) || {};
                let mrv_hash = this.selectedLink.share_can_custom && mrv.custom_path
                    ? mrv.custom_path
                    : mrv.hash;
                return mrv_hash ? ('/link/' + mrv_hash + '/') : '';
            },
            fillByUrl() {
                $.LoadingOverlay('show');
                axios.post('/ajax/table/fill-mrv-url', {
                    link_id: this.selectedLink.id,
                    mrv_id: this.selectedLink.share_mrv_id,
                    target_field_id: this.selectedLink.share_url_field_id,
                    web_link_id: this.selectedLink.share_web_link_id,
                }).then(({ data }) => {
                    let link = this.webLink();
                    link.share_is_web = 1;
                    link.link_type = data.link_type;
                    link.web_prefix = data.web_prefix;
                    link.address_field_id = data.address_field_id;
                    //Swal('Completed', 'Field for MRV URL was filled by MRV links.');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            showMrvPopup() {
                eventBus.$emit('show-table-views-popup', this.tableMeta.db_name, 'multiple', this.selectedLink.share_mrv_id);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
label {
    margin: 0;
}
.labelwi {
    width: 350px;
    flex-shrink: 0;
}
</style>