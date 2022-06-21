<template>
    <div class="container-wrapper" v-if="errors_present.length">
        <div class="flexer">
            <div>
                <label>Errors:</label>
                <br>
                <label v-html="errors_present.join('<br>')"></label>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'Risa3dRemoverForm',
        mixins: [
        ],
        components: {
        },
        data() {
            return {
                errors_present: [],
            }
        },
        props: {
            usergroup: String,
            mg_name: String,
            tables_delete: String,
        },
        methods: {
            userString() {
                let ugr = JSON.parse(this.usergroup);
                return this.$root.getUserSimple(ugr, {
                    user_fld_show_first: true,
                    user_fld_show_last: true,
                });
            },
        },
        mounted() {
            if (!this.mg_name) {
                this.errors_present.push('Param "MG Name" is not present!');
            } else {
                Swal({
                    html: '* Usergroup = ' + this.userString()
                        + '<br>* Mount Geometry (MG) Name = ' + this.mg_name
                        + '<br><b>Confirm</b> to delete following associated model data:<br>- ' + this.tables_delete,
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        $.LoadingOverlay('show');
                        axios.post('/apps/risa3d/deleter', {
                            mg_name: this.mg_name,
                        }).then(({ data }) => {
                            if (data.length) {
                                Swal({ html: data.join('<br>') });
                            } else {
                                Swal('Successfully Deleted!').then(() => {
                                    let data = {
                                        event_name: 'close-application',
                                        app_code: 'risa3d_deleter',
                                    };
                                    window.parent.postMessage(data, '*');
                                });
                            }
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => $.LoadingOverlay('hide'));
                    }
                });
            }
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
            display: flex;
            background-color: #005fa4;
            color: #FFF;
            padding: 25px;
            border-radius: 20px;

            .m-right {
                margin-right: 25px;
            }
        }
    }
</style>
<style>
    .swal2-popup #swal2-content {
        text-align: left !important;
    }
</style>