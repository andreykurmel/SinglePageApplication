<template>
    <div class="container-wrapper">
        <div class="flexer" v-if="errors_present.length">
            <div>
                <label>Errors:</label>
                <br>
                <label v-html="errors_present.join('<br>')"></label>
            </div>
        </div>
        <div class="flexer" v-else="">
            <div style="width: 500px;">
                <label style="font-size: 1em;font-weight: normal;">Write calculation results to the RISA file with options:</label>
            </div>
            <div style="width: 500px;">
                <input type="checkbox" v-model="change"/>
                <label style="font-size: 1em;font-weight: normal;">Update nodes for any changes made here.</label>
            </div>
            <div style="width: 500px;">
                <input type="checkbox" v-model="add_rls"/>
                <label style="font-size: 1em;font-weight: normal;">Add nodes for RLs "Nodes_RLs", and add RL members "RLs".</label>
            </div>
            <div style="width: 500px;margin-top: 20px;">
                <!-- a target="_blank"
                   class="btn btn-default"
                   style="float: left"
                   :disabled="disabld"
                   :href="getlink()"
                   @click="closeForm"
                >Review</a -->

                <a class="btn btn-success"
                   :disabled="disabld"
                   @click="runCalculation"
                >GO</a>

                <!-- a class="btn btn-default"
                   :disabled="disabld"
                   @click="closeForm"
                >Close</a -->
            </div>
            <div v-if="disabld" style="font-weight: bold">Calculating...</div>
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
                targetfile: '',
                disabld: false,
                change: true,
                add_rls: true,
            }
        },
        props: {
            apppath: String,
            tiapath: String,
            jsonfile: String,
            usergroup: String,
            model: String,
            errors_present: Array,
            tbid: String,
            fldid: String,
            rwid: String,
            fname: String,
        },
        methods: {
            getlink(asupdate) {
                let $url = '';
                if (this.usergroup && this.model) {
                    $url = this.apppath+'?usergroup=' + this.usergroup + '&model=' + this.model;
                } else {
                    $url = this.tiapath + '?inp_type=stim_page&json=' + this.jsonfile + '&r3d=' + this.targetfile;
                }
                return asupdate
                    ? $url + '&noupd=' + (this.change ? 0 : 1) + '&rls=' + (this.add_rls ? 1 : 0) + '&rejson=1'
                    : $url + '&noupd=1';
            },
            closeForm() {
                let data = {
                    event_name: 'close-application',
                    app_code: 'stim_calculate_loads',
                };
                window.parent.postMessage(data, '*');
            },
            runCalculation() {
                this.disabld = true;
                if (this.usergroup && this.model) {
                    axios.post('', {
                        tbid: this.tbid,
                        fldid: this.fldid,
                        rwid: this.rwid,
                        fname: this.fname,
                    }).then(({data}) => {
                        this.targetfile = data;
                        this.linkForCalc();
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                } else {
                    this.linkForCalc();
                }
            },
            linkForCalc() {
                axios.get(this.getlink(true)).then(({data}) => {
                    Swal('Info','Calculation is finished!');
                    this.disabld = false;
                });
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