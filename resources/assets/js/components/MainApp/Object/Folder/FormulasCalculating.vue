<template>
    <div class="formulas-calculating" v-show="progressBarWidth > -1">
        <div class="bar-wrapper">
            <span>Calculating...</span>
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
        props:{
            job_id: String
        },
        mounted() {
            this.interval = setInterval(() => {
                if (this.job_id) {
                    axios.get('/ajax/import/status', {
                        params: {
                            import_jobs: [this.job_id]
                        }
                    }).then(({ data }) => {
                        this.progressBarWidth = data.complete;
                        if (data.status === 'done') {
                            this.progressBarWidth = -1;
                            clearInterval(this.interval);
                            //Swal('Formulas are calculated', '', 'info');
                        }
                    });
                }
            }, 1000);
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