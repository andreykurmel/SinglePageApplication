<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div class="popup">
            <div class="flex flex--col">
                <div class="popup-header">
                    Referral &amp; Credit
                    <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close')"></span>
                </div>
                <div class="popup-content flex__elem-remain">

                    <div class="flex__elem__inner popup-main">
                        <div class="full-height">
                            <div>
                                <p>Get $15 in credit for each account signed up through your referral.</p>
                                <p>You will receive the credit when the user you invited signs up.
                                You can use the awarded credit for upgrading and/or renewing your paid subscriptions.</p>
                            </div>
                            <div>
                                <label>Your designated referral link:</label>
                            </div>
                            <div class="form-group">
                                <input class="form-control" :value="invitLink"/>
                            </div>
                            <div class="main--top">
                                <input class="form-control sizes--area" placeholder="Enter email address" v-model="emails_string"/>
                                <button class="btn btn-primary sizes--btn" @click="addInvitations()">Send email invite</button>
                            </div>
                            <div class="main--center" :style="{height: 'calc(100% - '+(job_id === null ? 250 : 270)+'px)'}">
                                <custom-table
                                        :cell_component_name="'custom-cell-invitations'"
                                        :global-meta="$root.settingsMeta['user_invitations']"
                                        :table-meta="$root.settingsMeta['user_invitations']"
                                        :all-rows="$root.user._invitations"
                                        :rows-count="$root.user._invitations.length"
                                        :cell-height="$root.cellHeight"
                                        :max-cell-rows="$root.maxCellRows"
                                        :is-full-width="true"
                                        :user="$root.user"
                                        :behavior="'invite_module'"
                                        :forbidden-columns="$root.systemFields"
                                        @delete-row="deleteInvitation"
                                        @resend-action="sendInvitations"
                                ></custom-table>
                            </div>
                            <div class="main--bottom">
                                <label class="pull-right">Total rewarded: ${{ totReward }}</label>
                            </div>
                            <div class="progress-wrapper" v-show="progressBarWidth > -1">
                                <div class="progress-bar" :style="{width: progressBarWidth+'%'}"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    import CustomTable from './../CustomTable/CustomTable';

    export default {
        name: "InviteModule",
        components: {
            CustomTable
        },
        data: function () {
            return {
                emails_string: '',
                job_id: null,
                progressBarWidth: -1
            }
        },
        props:{
            app_url: String,
            is_vis: Boolean,
        },
        computed: {
            invitLink() {
                return this.app_url + '/register?invitation=' + this.$root.user.personal_hash;
            },
            totReward() {
                return Number(this.$root.user.invitations_reward).toFixed(2);
            }
        },
        methods: {
            reloadInvitations() {
                axios.get('/ajax/user/invitation', {}).then(({data}) => {
                    this.$root.user._invitations = data
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            addInvitations() {
                if (this.emails_string) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/user/invitation', {
                        emails: this.emails_string.split(/\r\n|\r|\n/gi)
                    }).then(({data}) => {
                        if (data.errors.length) {
                            Swal('', data.errors.join('<br>'));
                        } else {
                            this.$root.user._invitations = data.invits;
                            this.emails_string = '';
                            this.sendInvitations();
                        }
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
            deleteInvitation(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/user/invitation', {
                    params: {
                        invitation_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.$root.user._invitations = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            sendInvitations(tableRow) {
                this.job_id = 0;
                let data = tableRow ? {invit_ids: [tableRow.id]} : {};
                axios.post('/ajax/user/send-invitation', data).then(({ data }) => {
                    if (data.job_id) {
                        this.progressBarWidth = 0;
                        this.job_id = data.job_id;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            hideMenu(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.$emit('popup-close');
                    this.$root.set_e__used(this);
                }
            },
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);

            setInterval(() => {
                if (this.job_id) {
                    axios.get('/ajax/import/status', {
                        params: {
                            import_id: this.job_id
                        }
                    }).then(({ data }) => {
                        this.progressBarWidth = data.complete;
                        if (data.status === 'done') {
                            this.progressBarWidth = -1;
                            this.job_id = null;
                            this.reloadInvitations();
                        }
                    });
                }
            }, 1000);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    @import "../CustomPopup/CustomEditPopUp";

    .popup {
        width: 750px;
        height: 97%;
        top: 1%;
        left: calc(50% - 375px);

        .popup-main {
            overflow: auto;
            bottom: 20px !important;

            .main--top {
                padding-bottom: 10px;
                display: flex;
                justify-content: space-between;

                .sizes--area {
                    flex-grow: 1;
                }
                .sizes--btn {
                    margin-left: 10px;
                }
            }
            .main--center {
                height: calc(100% - 180px);
            }
            .main--bottom {
                height: 30px;
                padding-top: 10px;
            }

            .progress-wrapper {
                margin-top: 10px;
                height: 10px;
                border-radius: 5px;
                border: 1px solid #CCC;
            }
        }
    }
</style>