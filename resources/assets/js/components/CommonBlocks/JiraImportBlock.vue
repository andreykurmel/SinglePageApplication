<template>
    <div class="transpose_wrapper">
        <template v-if="jira_item.action === 'import'">
            <div class="form-group flex flex--center-v">
                <label>Cloud:&nbsp;</label>
                <select-block
                        :options="userClouds()"
                        :sel_value="jira_item.cloud_id"
                        @option-select="cloudChanged"
                ></select-block>
            </div>
            <!--<div class="form-group flex flex--center-v">
                <label>Projects:&nbsp;</label>
                <select-block
                        :is_disabled="!jira_item.cloud_id || !projects.length"
                        :is_multiselect="true"
                        :options="availProjects()"
                        :sel_value="jira_item.project_names"
                        @option-select="projectChanged"
                        @hide-select="loadFields"
                ></select-block>
            </div>
            <div class="form-group flex flex--center-v">
                <label>OR</label>
            </div>-->
            <div class="form-group flex">
                <label style="margin-top: 5px;">JQL Query:&nbsp;</label>
                <textarea class="form-control"
                          v-model="jira_item.jql_query"
                          :disabled="!jira_item.cloud_id"
                          @change="jqlQueryChanged"
                          rows="5"
                ></textarea>
            </div>
        </template>

        <template v-if="jira_item.action === 'sync'">
            <div class="form-group flex flex--center-v">
                <label>Cloud:&nbsp;{{ cloudString }}</label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>JQL Query:&nbsp;{{ jira_item.jql_query }}</label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>Last Sync:&nbsp;<span v-html="dateString"></span></label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>
                    <input type="checkbox" v-model="jira_item.remove_not_found" @change="setNum('remove_not_found')">
                    <span>Remove records not found in Jira.</span>
                </label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>
                    <input type="checkbox" v-model="jira_item.add_new_records" @change="setNum('add_new_records')">
                    <span>Add records found in Jira.</span>
                </label>
            </div>
            <div class="form-group flex flex--center-v">
                <label>
                    <input type="checkbox" v-model="jira_item.update_changed" @change="setNum('update_changed')">
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
        name: 'JiraImportBlock',
        mixins: [
        ],
        components: {
            SelectBlock,
        },
        data() {
            return {
                projects: [],
            }
        },
        props: {
            table_meta: Object,
            jira_item: Object,
        },
        computed: {
            cloudString() {
                let cloud = _.find(this.$root.settingsMeta.user_clouds_data, {id: Number(this.jira_item.cloud_id)});
                return cloud ? cloud.name : this.jira_item.cloud_id;
            },
            projectsString() {
                let projects = this.jira_item.project_names || [];
                return projects.join(', ');
            },
            dateString() {
                return this.table_meta.import_last_jira_action || '<span class="red">You need to make import first.</span>';
            },
        },
        methods: {
            setNum(key) {
                this.jira_item[key] = this.jira_item[key] ? 1 : 0;
                this.$emit('jira-item-changed');
            },
            userClouds() {
                let clouds = _.filter(this.$root.settingsMeta.user_clouds_data, {cloud: 'Jira'});
                return _.map(clouds, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            availProjects() {
                return _.map(this.projects, (pr) => {
                    return { val: pr.name, show: pr.name, }
                });
            },
            cloudChanged(opt) {
                this.jira_item.cloud_id = opt.val;
                this.loadProjects();
            },
            loadProjects() {
                if (this.jira_item.cloud_id) {
                    axios.post('/ajax/jira/projects', {
                        cloud_id: this.jira_item.cloud_id,
                    }).then(({data}) => {
                        this.projects = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },
            projectChanged(opt) {
                if (this.jira_item.project_names.indexOf(opt.val) > -1) {
                    this.jira_item.project_names.splice( this.jira_item.project_names.indexOf(opt.val), 1 );
                } else {
                    this.jira_item.project_names.push(opt.val);
                }
                this.jira_item.jql_query = '';
            },
            jqlQueryChanged() {
                this.jira_item.project_names = [];
                this.loadFields();
            },
            loadFields() {
                this.$emit('project-changed');
            },
        },
        mounted() {
            eventBus.$on('jira-load-projects', this.loadProjects);
        },
        beforeDestroy() {
            eventBus.$off('jira-load-projects', this.loadProjects);
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