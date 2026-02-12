<template>
    <div class="transpose_wrapper">
        <div class="form-group flex flex--center-v">
            <label>Type</label>
            <select-block
                :options="[
                    {val: 'direct', show: 'Wide to Long'},
                    {val: 'reverse', show: 'Long to Wide'}
                ]"
                :sel_value="transpose_item.direction"
                style="max-width: 200px;"
                @option-select="(opt) => { propChanged('direction', 'single', opt.val); }"
            ></select-block>
            <label style="margin-left: 15px;">
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="propChanged('skip_empty', 'bool')">
                        <i v-if="transpose_item.skip_empty" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Neglect empty values</span>
            </label>
        </div>
        <div class="form-group flex flex--center-v">
            <label>Source Table</label>
            <select-with-folder-structure
                :cur_val="transpose_item.source_tb_id"
                :empty_val="true"
                :available_tables="$root.settingsMeta.available_tables"
                :user="$root.user"
                @sel-changed="tbChanged"
                class="form-control">
            </select-with-folder-structure>
        </div>
        <div class="form-group flex flex--center-v">
            <label>Row Group</label>
            <select-block
                    :is_disabled="!sourceTable"
                    :options="getRGs()"
                    :sel_value="transpose_item.row_group_id"
                    @option-select="(opt) => { propChanged('row_group_id', 'single', opt.val); }"
            ></select-block>
        </div>
    </div>
</template>

<script>
    import SelectWithFolderStructure from "../CustomCell/InCell/SelectWithFolderStructure.vue";
    import SelectBlock from "./SelectBlock.vue";

    export default {
        name: 'TransposeImportBlock',
        mixins: [
        ],
        components: {
            SelectBlock,
            SelectWithFolderStructure,
        },
        data() {
            return {
            }
        },
        props: {
            transpose_item: Object,
        },
        computed: {
            sourceTable() {
                return _.find(this.$root.settingsMeta.available_tables, {id: this.transpose_item.source_tb_id});
            },
        },
        methods: {
            tbChanged(val) {
                this.transpose_item.source_tb_id = val;
                this.$emit('src-changed');
            },
            propChanged(key, type, value) {
                if (type === 'bool') {
                    this.transpose_item[key] = !this.transpose_item[key];
                } else {
                    this.transpose_item[key] = value;
                }
                this.$emit('prop-changed');
            },
            getRGs() {
                let rgs = _.map(this.sourceTable ? this.sourceTable._row_groups : [], (rg) => {
                    return { val: rg.id, show: rg.name };
                });
                rgs.unshift({val:null, show: ''});
                return rgs;
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
        white-space: normal;
        min-width: 120px;
    }
    .form-control {
        max-width: 380px;
    }
    .transpose_wrapper {
        max-width: 750px;
    }
</style>
<style lang="scss">
    .transpose_wrapper {
        .select2-container {
            max-width: 650px;
            z-index: 1000;
        }
    }
</style>