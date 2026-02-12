<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header full-width">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Referral &amp; Credit</div>
                        <div class="" style="position: absolute;right: 5px;z-index: 15;">
                            <span class="glyphicon glyphicon-remove pull-right close-btn" @click="$emit('popup-close')"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">

                    <div class="flex__elem__inner popup-main" :style="textSysStyle">
                        <div class="full-height">
                            <div>
                                <p>
                                    <span>Get</span>
                                    <input v-if="$root.user.id == 1"
                                           class="form-control input-sm"
                                           style="width: 40px; display: inline-block; font-weight: bold;"
                                           :style="textSysStyle"
                                           v-model="reward_amount"
                                           @change="saveReward"/>
                                    <span v-else="">${{ reward_amount }}</span>
                                    <span>in credit for each account signed up through your referral.</span>
                                </p>
                                <p>You will receive the credit when the user you invited signs up.
                                You can use the awarded credit for upgrading and/or renewing your paid subscriptions.</p>
                            </div>
                            <div>
                                <label>Your designated referral link:</label>
                                <button class="btn btn-primary btn-sm sizes--btn" @click="copyInvite()">Copy</button>
                            </div>
                            <div class="form-group" style="margin-top: 5px;">
                                <input class="form-control" :style="textSysStyle" :value="invitLink"/>
                            </div>
                            <div class="main--top">
                                <input class="form-control sizes--area" :style="textSysStyle" placeholder="Enter email addresses separated by space, comma or semicolon." v-model="emails_string"/>
                                <button class="btn btn-primary sizes--btn" @click="addInvitations()">Send invite</button>
                            </div>
                            <div class="main--center" :style="{height: 'calc(100% - '+(job_id === null ? 250 : 270)+'px)'}">
                                <custom-table
                                        :cell_component_name="'custom-cell-invitations'"
                                        :global-meta="$root.settingsMeta['user_invitations']"
                                        :table-meta="$root.settingsMeta['user_invitations']"
                                        :all-rows="$root.user._invitations"
                                        :rows-count="$root.user._invitations.length"
                                        :cell-height="1"
                                        :max-cell-rows="0"
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
    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    import {eventBus} from '../../app';

    import CustomTable from './../CustomTable/CustomTable';

    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';
    import CellStyleMixin from "../_Mixins/CellStyleMixin";

    export default {
        name: "InviteModule",
        components: {
            CustomTable
        },
        mixins: [
            PopupAnimationMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                reward_amount: 10,
                emails_string: '',
                job_id: null,
                progressBarWidth: -1,
                //PopupAnimationMixin
                getPopupWidth: 900,
                idx: 0,
            }
        },
        props:{
            app_url: String,
            is_visible: Boolean,
        },
        computed: {
            invitLink() {
                return this.app_url + '/register?invite=' + this.$root.user.personal_hash;
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
                    Swal('Info', getErrors(errors));
                });
            },
            addInvitations() {
                if (this.emails_string) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/user/invitation', {
                        emails: this.emails_string
                            .replaceAll(/[\s\r\n,;]/gi, ' ')
                            .replaceAll(/\s+/gi, ' ')
                            .split(' ')
                    }).then(({data}) => {
                        if (data.errors.length) {
                            Swal('Info', data.errors.join('<br>'));
                        }
                        this.$root.user._invitations = data.invits;
                        this.emails_string = '';
                        this.sendInvitations();
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
                });
            },
            hideMenu(e) {
                if (this.is_visible && e.keyCode === 27 && !this.$root.e__used) {
                    this.$emit('popup-close');
                    this.$root.set_e__used(this);
                }
            },
            copyInvite() {
                SpecialFuncs.strToClipboard(this.invitLink);
            },
            saveReward() {
                let vv = Number(this.reward_amount) || 10;
                axios.put('/ajax/app/settings', {
                    app_key: 'invite_reward_amount',
                    app_val: vv,
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
        },
        mounted() {
            this.$root.tablesZidxIncrease();
            this.zIdx = this.$root.tablesZidx;
            this.runAnimation({anim_transform:'none'});

            eventBus.$on('global-keydown', this.hideMenu);

            let el = _.find(this.$root.settingsMeta.meta_app_settings, {key: 'invite_reward_amount'});
            if (el) {
                this.reward_amount = Number(el.val) || 10;
            }

            setInterval(() => {
                if (this.job_id) {
                    axios.get('/ajax/import/status', {
                        params: {
                            import_jobs: [this.job_id]
                        }
                    }).then(({ data }) => {
                        let jb = _.first(data);
                        this.progressBarWidth = jb.complete;
                        if (jb.status === 'done') {
                            this.progressBarWidth = -1;
                            this.job_id = null;
                            this.reloadInvitations();
                        }
                    });
                }
            }, 1000);

            setInterval(() => {
                if (this.is_visible) {
                    this.reloadInvitations();
                }
            }, 5000);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    @import "../CustomPopup/CustomEditPopUp";

    .popup {
        width: 900px;
        height: 90%;
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