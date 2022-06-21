<template>
    <div class="full-height">
        <div style="height: calc(100% - 40px); overflow: auto;">
            <table class="tablda-like full-width" v-if="parameters">
                <tr>
                    <th style="width: 50%">Columns</th>
                    <th style="width: 50%">Options</th>
                </tr>
                <tr v-for="fld in tableMeta._fields" v-if="$root.systemFields.indexOf(fld.field) === -1">
                    <td>
                        {{ fld.name }}
                    </td>
                    <td>
                        <select-block
                            :options="[
                                { val: 'first', show: 'Empty' },
                                { val: 'all', show: 'All' },
                                { val: 'unique', show: 'Comb Unique' }
                            ]"
                            :sel_value="parameters[fld.field]"
                            @option-select="(opt) => { parameters[fld.field] = opt.val; }"
                        ></select-block>
                    </td>
                </tr>
            </table>
        </div>

        <div style="height: 40px;">
            <button class="btn btn-success pull-right"
                    :style="$root.themeButtonStyle"
                    @click="runTask()"
            >Go</button>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import SelectBlock from "./SelectBlock";

    export default {
        name: "RemoveDuplicatesBlock",
        mixins: [
        ],
        components: {
            SelectBlock,
        },
        data: function () {
            return {
                parameters: null,
            }
        },
        props:{
            tableMeta: Object,
        },
        computed: {
        },
        methods: {
            prepareParams() {
                let obj = {};
                _.each(this.tableMeta._fields, (fld) => {
                    if (this.$root.systemFields.indexOf(fld.field) === -1) {
                        obj[fld.field] = 'first';
                    }
                });
                this.parameters = obj;
            },
            runTask() {
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data/remove-duplicates', {
                    table_id: this.tableMeta.id,
                    parameters: this.parameters,
                    request_params: this.$root.request_params,
                }).then(({data}) => {
                    eventBus.$emit('reload-page');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            },
        },
        created() {
            this.prepareParams();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import './TabldaLike';
</style>