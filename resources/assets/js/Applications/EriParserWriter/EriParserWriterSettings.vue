<template>
    <div class="container-wrapper">
        <div class="flex flex--center full-height bold">
            <label v-if="message">{{ message }}</label>
            <div v-else>
                <label v-if="page_code == 'eri_parser'">Check items to be parsed from the ERI file:</label>
                <label v-if="page_code == 'eri_writer'">Check items to be exported to the ERI file:</label>
                <div style="max-height: calc(100vh - 110px);overflow-y: auto;margin-bottom: 20px;">
                    <div v-for="part in parts" :key="part.id">
                        <label style="font-size: 0.75em">
                            <input type="checkbox" :value="part.id" v-model="part.checked">
                            {{ part.name }}
                        </label>
                    </div>
                </div>
                <button class="btn btn-default btn-success" @click="runCalculation()">
                    <span v-if="page_code == 'eri_parser'">Parse</span>
                    <span v-if="page_code == 'eri_writer'">Export</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'StimCalculateLoads',
        mixins: [
        ],
        components: {
        },
        data() {
            return {
            }
        },
        props: {
            message: String,
            page_code: String,
            table_id: String,
            link_id: String,
            row_id: String,
            parts: Array,
        },
        methods: {
            runCalculation() {
                $.LoadingOverlay('show');
                axios.post('', {
                    page_code: this.page_code,
                    table_id: this.table_id,
                    link_id: this.link_id,
                    row_id: this.row_id,
                    active_parts: this.parts.filter(part => part.checked).map(part => part.id),
                }).then(({data}) => {
                    Swal('Info', data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .container-wrapper {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;

        .flexer {
            background-color: #005fa4;
            color: #FFF;
            padding: 25px;
            border-radius: 20px;

            .btn {
                font-weight: bold;
            }
            .m-right {
                margin-right: 25px;
            }
            input {
                width: 20px;
                height: 20px;
            }
        }
    }
</style>