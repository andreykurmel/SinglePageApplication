<template>
    <div id="top-calculating" class="formulas-calculating" v-show="progressBarWidth > -1">
        <div class="bar-wrapper">
            <span v-if="job_type == 'SmartAutoselect'">Smart Autoselect working...</span>
            <span v-else>Calculating formulas...</span>
            <div class="progress-wrapper">
                <div class="progress-bar" :style="{width: progressBarWidth+'%'}"></div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "FormulasCalculating",
        data: function () {
            return {
                interval: null,
                progressBarWidth: 0
            }
        },
        props: {
            job_id: String|Number,
            job_type: String,
        },
        mounted() {
            this.interval = setInterval(() => {
                if (this.job_id) {
                    axios.get('/ajax/import/status', {
                        params: {
                            import_jobs: [this.job_id]
                        }
                    }).then(({ data }) => {
                        let firstData = _.first(data);
                        this.progressBarWidth = firstData.complete;
                        if (firstData.status === 'done') {
                            this.progressBarWidth = -1;
                            clearInterval(this.interval);
                            //Swal('Info','Formulas are calculated');
                        }
                    });
                }
            }, 2000);
        },
        beforeDestroy() {
            clearInterval(this.interval);
        }
    }
</script>

<style lang="scss" scoped>
    .formulas-calculating {
        z-index: 1000;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        width: 200px;

        .bar-wrapper {
            width: 100%;
            text-align: center;

            .progress-wrapper {
                height: 10px;
                border-radius: 5px;
                border: 1px solid #CCC;
            }
        }
    }
</style>