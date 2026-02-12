<template>
    <div class="transpose_wrapper">
        <template v-if="salesforce_item.action === 'import'">
            <div class="form-group flex flex--center-v">
                <label>Cloud:&nbsp;</label>
                <select-block
                        :options="userClouds()"
                        :sel_value="salesforce_item.cloud_id"
                        @option-select="cloudChanged"
                ></select-block>
            </div>
            <div class="form-group flex flex--center-v">
                <label>Objects:&nbsp;</label>
                <select-block
                        :is_disabled="!salesforce_item.cloud_id || !objects.length"
                        :options="availObjects()"
                        :sel_value="salesforce_item.object_id"
                        @option-select="objectChanged"
                        @hide-select="loadFields"
                ></select-block>
            </div>
        </template>

        <template v-if="salesforce_item.action === 'sync'">
            <div class="form-group flex flex--center-v">
                <label>Cloud:&nbsp;{{ cloudString }}</label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>Object:&nbsp;{{ salesforce_item.object_name }}</label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>Last Sync:&nbsp;<span v-html="dateString"></span></label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>
                    <input type="checkbox" v-model="salesforce_item.remove_not_found" @change="setNum('remove_not_found')">
                    <span>Remove records not found in Salesforce.</span>
                </label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>
                    <input type="checkbox" v-model="salesforce_item.add_new_records" @change="setNum('add_new_records')">
                    <span>Add records found in Salesforce.</span>
                </label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>
                    <input type="checkbox" v-model="salesforce_item.update_changed" @change="setNum('update_changed')">
                    <span>Update changes for records found in both.</span>
                </label>
            </div>
        </template>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import SelectBlock from "./SelectBlock.vue";

    export default {
        name: 'SalesforceImportBlock',
        mixins: [
        ],
        components: {
            SelectBlock,
        },
        data() {
            return {
                objects: [],
            }
        },
        props: {
            table_meta: Object,
            salesforce_item: Object,
        },
        computed: {
            cloudString() {
                let cloud = _.find(this.$root.settingsMeta.user_clouds_data, {id: Number(this.salesforce_item.cloud_id)});
                return cloud ? cloud.name : this.salesforce_item.cloud_id;
            },
            dateString() {
                return this.table_meta.import_last_salesforce_action || '<span class="red">You need to make import first.</span>';
            },
        },
        methods: {
            setNum(key) {
                this.salesforce_item[key] = this.salesforce_item[key] ? 1 : 0;
                this.$emit('salesforce-item-changed');
            },
            userClouds() {
                let clouds = _.filter(this.$root.settingsMeta.user_clouds_data, {cloud: 'Salesforce'});
                return _.map(clouds, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            availObjects() {
                return _.map(this.objects, (pr) => {
                    return { val: pr.name, show: pr.label, }
                });
            },
            cloudChanged(opt) {
                this.salesforce_item.cloud_id = opt.val;
                this.loadObjects();
            },
            loadObjects() {
                if (this.salesforce_item.cloud_id) {
                    axios.post('/ajax/salesforce/objects', {
                        cloud_id: this.salesforce_item.cloud_id,
                    }).then(({data}) => {
                        this.objects = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },
            objectChanged(opt) {
                this.salesforce_item.object_name = opt.show;
                this.salesforce_item.object_id = opt.val;
            },
            loadFields() {
                this.$emit('object-changed');
            },
        },
        mounted() {
            eventBus.$on('salesforce-load-objects', this.loadObjects);
        },
        beforeDestroy() {
            eventBus.$off('salesforce-load-objects', this.loadObjects);
        }
    }
</script>

<style lang="scss" scoped>
    label {
        margin: 0;
        white-space: normal;
        min-width: 120px;
    }
    .transpose_wrapper {
        max-width: 750px;
    }
</style>