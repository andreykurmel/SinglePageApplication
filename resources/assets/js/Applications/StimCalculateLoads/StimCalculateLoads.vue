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
            <div style="width: 300px;">
                <input type="checkbox" v-model="change"/>
                <label style="font-size: 2em;font-weight: normal;">Update Nodes</label>
            </div>
            <div style="width: 300px;">
                <input type="checkbox" v-model="add_rls"/>
                <label style="font-size: 2em;font-weight: normal;">Add Nodes_RLs, RLs</label>
            </div>
            <div style="text-align: right;margin-top: 20px;">
                <a target="_blank" class="btn btn-success" :href="getlink" @click="closeForm">GO</a>
                <a class="btn btn-default" @click="closeForm">Cancel</a>
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
                change: true,
                add_rls: true,
            }
        },
        props: {
            apppath: String,
            tiapath: String,
            jsonfile: String,
            targetfile: String,
            usergroup: String,
            model: String,
            errors_present: Array,
        },
        computed: {
            getlink() {
                let $url = '';
                if (this.usergroup && this.model) {
                    $url = this.apppath+'?usergroup=' + this.usergroup + '&model=' + this.model;
                } else {
                    $url = this.tiapath + '?inp_type=stim_page&json=' + this.jsonfile + '&r3d=' + this.targetfile;
                }
                return $url + '&noupd=' + (this.change ? 0 : 1) + '&rls=' + (this.add_rls ? 1 : 0) + '&rejson=1';
            },
        },
        methods: {
            closeForm() {
                let data = {
                    event_name: 'close-application',
                    app_code: 'stim_calculate_loads',
                };
                window.parent.postMessage(data, '*');
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