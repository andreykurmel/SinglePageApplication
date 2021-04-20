<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div class="popup">
            <div class="flex flex--col">
                <div class="popup-header full-width">
                    Subscription
                    <span class="glyphicon glyphicon-remove pull-right close-btn" @click="$emit('popup-close')"></span>
                </div>
                <div class="popup-content flex__elem-remain full-width">

                    <div class="flex__elem__inner">
                        <div class="flex flex--col">
                            <div class="flex__elem-remain full-width">
                                <div class="flex__elem__inner popup-main">
                                    <div class="popup-overflow">
                                        <div class="container-fluid cost-block">
                                            <div class="row row-margin full-width row-container">
                                                <div class="col-xs-3">
                                                    <label>Contract Cycle:</label>
                                                </div>
                                                <div class="col-xs-3">
                                                    <select class="form-control" v-model="$root.user.renew">
                                                        <option>Monthly</option>
                                                        <option>Yearly</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="plans-wrapper">
                                            <div class="plan-block">
                                                <div>
                                                    <label>
                                                        Your current subscription plan:
                                                        <span v-if="selectedPlanCost !== ($root.user._next_subscription ? $root.user._next_subscription.cost : $root.user._subscription.cost)" class="red">(Unsaved)</span>
                                                    </label>
                                                </div>
                                                <div v-for="pln in $root.settingsMeta.all_plans" class="check-container">
                                                    <label>
                                                        <input name="plan" :disabled="pln.code === 'enterprise'" type="radio" v-model="tmp_plan_code" :value="pln.code"/>
                                                        <span>{{ pln.name }}</span>
                                                        <span v-if="pln.code !== 'enterprise'">(${{ $root.user.renew === 'Monthly' ? pln.per_month : pln.per_year }})</span>
                                                        <span v-else="">(<a target="_blank" :href="eprs_link">Reach out for details</a>)</span>

                                                        <span
                                                            v-if="$root.user._subscription && $root.user._subscription.plan_code === pln.code"
                                                            :class="[newRenew ? 'red' : 'green']"
                                                        >
                                                            {{ getActiveText() }}
                                                        </span>

                                                        <span
                                                            v-if="$root.user._next_subscription && $root.user._next_subscription.plan_code === pln.code"
                                                            class="red"
                                                        >
                                                            {{ getNextText() }}
                                                        </span>

                                                    </label>
                                                </div>
                                            </div>

                                            <div class="addon-block">
                                                <div>
                                                    <label>
                                                        Add-on (<span :style="{color: tmp_plan_code === 'basic' ? '#F00' : ''}">not available to Basic plan</span>):
                                                    </label>
                                                </div>
                                                <div v-for="group in getLocAllAddons()" class="check-container">
                                                    <div v-for="adn in group" class="cont-col-width" :class="[adn.code != 'all' ? 'check-container' : '']">
                                                        <label>
                                                            <span v-show="tmp_plan_code === 'basic'" class="indeterm_check__wrap">
                                                                <span class="indeterm_check disabled"></span>
                                                            </span>

                                                            <span v-show="tmp_plan_code !== 'basic'" class="indeterm_check__wrap">
                                                                <span class="indeterm_check"
                                                                      :class="[!adn.is_special && all_adn._checked ? 'disabled' : '']"
                                                                      @click="!adn.is_special && all_adn._checked ? null : toggleAdn(adn)">
                                                                    <i v-if="adn._checked" class="glyphicon glyphicon-ok group__icon"></i>
                                                                </span>
                                                            </span>

                                                            <span>{{ adn.name }}</span>
                                                            <span v-if="adn.is_special || !all_adn._checked">(+${{ $root.user.renew === 'Monthly' ? adn.per_month : adn.per_year }})</span>
                                                            <span v-else="">(+$0)</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pay-block">
                                            <div class="container-fluid">
                                                <div class="row row-margin">
                                                    <div class="col-md-3">
                                                        <label>Subscription Total:</label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>${{ Number(selectedPlanCost).toFixed(2) }}</label>
                                                    </div>
                                                    <div class="col-md-5"></div>
                                                </div>
                                                <div class="row row-margin">
                                                    <div class="col-md-3">
                                                        <label>Credit:</label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>${{ Number($root.user.avail_credit).toFixed(2) }}</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <button class="btn btn-sm"
                                                                :class="[pay_target === 'avail_credit' ? 'btn-success' : 'btn-primary']"
                                                                @click.prevent="pay_target === 'avail_credit' ? pay_target = '' : pay_target = 'avail_credit'"
                                                        >Add</button>
                                                        <button v-if="$root.user.is_admin"
                                                                class="btn btn-sm"
                                                                :class="[pay_target === 'transfer' ? 'btn-success' : 'btn-primary']"
                                                                @click.prevent="pay_target === 'transfer' ? pay_target = '' : pay_target = 'transfer'"
                                                        >Transfer</button>
                                                    </div>
                                                </div>
                                                <div class="row row-margin">
                                                    <div class="col-md-3">
                                                        <label>Account Due:</label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label v-if="$root.user.use_credit && accountDue > $root.user.avail_credit">${{ Number(accountDueWithCredit).toFixed(2) }}</label>
                                                        <label v-else="">${{ Number(accountDue).toFixed(2) }}</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <button class="btn btn-primary btn-sm"
                                                                v-if="planUpdateStatus === 'downgrade' || $root.user._next_subscription"
                                                                @click.prevent="prepareNextSubscription()"
                                                        >Apply</button>
                                                        <button class="btn btn-sm little_input"
                                                                :class="[pay_target === 'balance' ? 'btn-success' : 'btn-primary']"
                                                                v-else=""
                                                                :disabled="($root.user.selected_card && $root.user.recurrent_pay)
                                                                    || (planUpdateStatus === 'same' && $root.user._subscription.left_days >= periodNotifDays)"
                                                                @click.prevent="pay_target === 'balance' ? pay_target = '' : pay_target = 'balance'"
                                                        >Pay</button>
                                                        <span>
                                                            <span class="indeterm_check__wrap">
                                                                <span class="indeterm_check"
                                                                      :class="{'disabled': !$root.user.use_credit && $root.user.avail_credit == 0}"
                                                                      @click="!$root.user.use_credit && $root.user.avail_credit == 0 ? null : updateData('use_credit')"
                                                                >
                                                                    <i v-if="$root.user.use_credit" class="glyphicon glyphicon-ok group__icon"></i>
                                                                </span>
                                                            </span>
                                                            <span>Use Credit</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="row row-margin">
                                                    <div class="col-md-6 renew-chk">
                                                        <label v-if="$root.user.use_credit">
                                                            <span class="indeterm_check__wrap">
                                                                <span class="indeterm_check"
                                                                      :class="{'disabled': !$root.user.recurrent_pay && selectedPlanCost > $root.user.avail_credit}"
                                                                      @click="!$root.user.recurrent_pay && selectedPlanCost > $root.user.avail_credit ? null : updateData('recurrent_pay')"
                                                                >
                                                                    <i v-if="$root.user.recurrent_pay" class="glyphicon glyphicon-ok group__icon"></i>
                                                                </span>
                                                            </span>
                                                            Auto Renew <span v-if="selectedPlanCost > $root.user.avail_credit" class="red">Credit balance not adequate.</span>
                                                        </label>
                                                        <label v-else="">
                                                            <span class="indeterm_check__wrap">
                                                                <span class="indeterm_check" @click="updateData('recurrent_pay')">
                                                                    <i v-if="$root.user.recurrent_pay" class="glyphicon glyphicon-ok group__icon"></i>
                                                                </span>
                                                            </span>
                                                            Auto Renew <span v-if="!$root.user.selected_card" class="red">No valid credit card.</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row row-margin" v-if="$root.user.recurrent_pay && selectedPlanCost > 0">
                                                    <div class="col-md-12">
                                                        Your next charge of ${{ Number(selectedPlanCost).toFixed(2) }} will be on {{ getNextCycleDate() }} from
                                                        <span v-if="$root.user.use_credit">
                                                            credit.
                                                        </span>
                                                        <span v-else="">
                                                            selected payment method.
                                                            <button class="btn btn-sm little_input"
                                                                    :class="[pay_target === 'change' ? 'btn-success' : 'btn-primary']"
                                                                    @click.prevent="pay_target === 'change' ? pay_target = '' : pay_target = 'change'"
                                                            >Change</button>
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- PAYMENT BLOCK -->
                                                <div class="row" v-show="pay_target === 'transfer'">
                                                    <hr class="divider">
                                                    <transfer-credit :user="$root.user"></transfer-credit>
                                                </div>
                                                <div class="row" v-show="pay_target === 'change' || pay_target === 'balance' || pay_target === 'avail_credit'">
                                                    <hr class="divider">
                                                    <div class="col-12">
                                                        <div class="notices" v-show="pay_target === 'change' || accountDueWithCredit > 0 || pay_target === 'avail_credit'">
                                                            <label>Select a pay method: </label>
                                                            <div class="inline-radio">
                                                                <label>
                                                                    <input type="radio" value="stripe" v-model="$root.user.pay_method" @change="updateData()"/>
                                                                    Credit Card
                                                                </label>
                                                            </div>
                                                            <div class="inline-radio">
                                                                <label>
                                                                    <input type="radio" value="paypal" v-model="$root.user.pay_method" @change="updateData()"/>
                                                                    PayPal Account
                                                                    <!--<img src="/assets/img/paypal.svg" height="20"/>-->
                                                                </label>
                                                            </div>

                                                            <div class="pull-right" v-show="pay_target === 'avail_credit'">
                                                                <label>Credit to Add: </label>
                                                                <input v-model="wrap_credit" class="form-control little_input" placeholder="$" @keyup="addToCreditKey()"/>
                                                            </div>
                                                        </div>

                                                        <template v-if="pay_target === 'balance'">
                                                            <div class="notices">
                                                                You
                                                                <span v-if="$root.user.use_credit">chosed</span>
                                                                <span v-else="">did not choose</span>
                                                                to use account credit for making payment.
                                                            </div>

                                                            <div class="notices" v-show="$root.user.use_credit">
                                                                You are currently paying with your account credit.
                                                                <span v-show="accountDueWithCredit === 0">
                                                                    But you can switch to using a PayPal account or credit card at any time by unchecking “Use Credit”).<br>
                                                                    <button class="btn btn-primary" @click="sendPaying('AvailCredit', 0)">Confirm</button>
                                                                </span>
                                                            </div>
                                                        </template>

                                                        <stripe-block
                                                                v-show="$root.user.pay_method === 'stripe'"
                                                                :stripe_key="stripe_key"
                                                                :confirm_pay="pay_target !== 'change' && (accountDueWithCredit > 0 || add_to_credit > 0)"
                                                                @payment-confirmed="prepareStripe()"
                                                        ></stripe-block>

                                                        <div class="notices" v-show="pay_target === 'change' && $root.user.pay_method === 'paypal'">
                                                            <div v-show="!$root.user.paypal_card_id">
                                                                <label>Add Credit Card:</label>
                                                                <table>
                                                                    <tr>
                                                                        <td width="165">
                                                                            <label>Card Number</label>
                                                                            <input class="form-control" v-model="paypal_card.number" placeholder="Card Number"/>
                                                                        </td>
                                                                        <td width="140">
                                                                            <label>Expiration Date</label>
                                                                            <select class="form-control inline-select" v-model="paypal_card.expire_month" style="width: 50px;">
                                                                                <option v-for="i in 12">{{ i }}</option>
                                                                            </select>
                                                                            /
                                                                            <select class="form-control inline-select" v-model="paypal_card.expire_year" style="width: 65px;">
                                                                            <option v-for="i in 20">{{ 2020+i }}</option>
                                                                        </select>
                                                                        </td>
                                                                        <td width="70">
                                                                            <label>CVV</label>
                                                                            <input class="form-control" v-model="paypal_card.cvv2" placeholder="CVV"/>
                                                                        </td>
                                                                        <td style="vertical-align: bottom;">
                                                                            <button class="btn btn-success" @click="sendPaying('PayPalCard')">Submit</button>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div v-show="$root.user.paypal_card_id" class="flex">
                                                                <label>Linked Card: ****{{ $root.user.paypal_card_last }} </label>
                                                                <!--<button class="btn btn-success btn-sm margin-left" @click="sendPaying('PayPalCard')">Submit Payment</button>-->
                                                                <button class="btn btn-danger btn-sm margin-left" @click="deleteCard('PayPal')">Delete</button>
                                                            </div>
                                                        </div>
                                                        <paypal-block
                                                                v-show="pay_target !== 'change' && $root.user.pay_method === 'paypal' && (accountDueWithCredit > 0 || add_to_credit > 0)"
                                                                :paypal_amount="paypalAmount"
                                                                @payment-confirmed="payByPal"
                                                        ></paypal-block>

                                                        <div class="notices" v-show="pay_target === 'balance'">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="full-width">
                                <!--<span v-if="planUpdateStatus === 'upgrade' && leftPlanCost"
                                      class="green bottom-text"
                                >Price is recalculated for upgrading your subscription</span>
                                <span v-if="planUpdateStatus === 'downgrade'"
                                      class="red bottom-text"
                                >(Downgrade) Your new subscription will be activated after ending of current subscription</span>-->
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {PaymentFunctions} from './../../classes/PaymentFunctions';

    import {eventBus} from './../../app';

    import TransferCredit from "./TransferCredit";
    import StripeBlock from "../CommonBlocks/StripeBlock";
    import PaypalBlock from "../CommonBlocks/PaypalBlock";

    export default {
        components: {
            PaypalBlock,
            StripeBlock,
            TransferCredit,
        },
        name: "UserPlans",
        data: function () {
            return {
                tmp_plan_code: this.$root.user._next_subscription ? this.$root.user._next_subscription.plan_code : this.$root.user._subscription.plan_code,
                loc_all_addons: [],
                wrap_credit: null,
                add_to_credit: null,
                pay_target: '',
                paypal_card: {
                    number: null,
                    expire_month: 1,
                    expire_year: 2021,
                    cvv2: null
                },
                periodNotifDays: 2,
            }
        },
        props:{
            stripe_key: String,
            is_vis: Boolean,
        },
        computed: {
            all_adn() {
                return _.find(this.loc_all_addons, {is_special: 1}) || {};
            },
            eprs_link() {
                return this.$root.settingsMeta.app_settings && this.$root.settingsMeta.app_settings['app_enterprise_dcr'] ?
                    this.$root.settingsMeta.app_settings['app_enterprise_dcr']['val']
                    : '';
            },
            leftPlanCost() {
                let sub = this.$root.user._subscription;
                return sub.plan_code !== 'basic' ?
                    sub.cost * (sub.left_days / sub.total_days) :
                    0;
            },
            selectedPlanCost() {
                let key = (this.$root.user.renew === 'Monthly' ? 'per_month' : 'per_year');
                let plan = _.find(this.$root.settingsMeta.all_plans, {code: this.tmp_plan_code});
                let sum = plan[key];
                if (this.all_adn._checked) {
                    sum += (this.tmp_plan_code !== 'basic' ? this.all_adn[key] : 0);//all addons are enabled
                } else {
                    _.each(this.loc_all_addons, (addon) => {
                        sum += (this.tmp_plan_code !== 'basic' && addon._checked ? addon[key] : 0);
                    });
                }
                return Number(sum);
            },
            upgradePlanCost() {
                let sub = this.$root.user._subscription;
                return this.selectedPlanCost - this.leftPlanCost;
            },
            //user can only upgrade plan immediately (user can downgrade after ending of current plan)
            //return {'downgrade' or 'same' or 'upgrade'}
            planUpdateStatus() {
                let sub = this.$root.user._subscription;
                let old_renew = sub.total_days == 365 ? 'Yearly' : 'Monthly';
                let res = 'same';

                //check downgrade
                if (sub.plan_code !== 'basic') {
                    if (
                        this.planLvL(this.tmp_plan_code) < this.planLvL(sub.plan_code)
                        ||
                        this.$root.user.renew < old_renew
                    ) {
                        res = 'downgrade';
                    }
                    _.each(this.loc_all_addons, (adn) => {
                        if (adn._old_checked && !adn._checked) {
                            res = 'downgrade';
                        }
                    });
                }

                if (res !== 'downgrade') {
                    //check upgrade
                    if (
                        this.planLvL(this.tmp_plan_code) > this.planLvL(sub.plan_code)
                        ||
                        this.$root.user.renew > old_renew
                    ) {
                        res = 'upgrade';
                    }
                    _.each(this.loc_all_addons, (adn) => {
                        if (!adn._old_checked && adn._checked) {
                            res = 'upgrade';
                        }
                    });
                }

                return res;
            },
            accountDue() {
                let res = 0;
                if (this.planUpdateStatus === 'downgrade') {
                    return 0;
                } else
                if (this.planUpdateStatus === 'upgrade') {
                    this.$root.user.recurrent_pay = 0;
                    return this.upgradePlanCost;
                } else {
                    return this.$root.user._subscription.left_days < this.periodNotifDays ? this.selectedPlanCost : 0;
                }
            },
            accountDueWithCredit() {
                let res = this.accountDue;
                if (this.$root.user.use_credit) {
                    res = Math.max(0, res - this.$root.user.avail_credit);
                }
                return Math.max(0, res);
            },
            newRenew() {
                let old_renew = this.$root.user._subscription.total_days == 365 ? 'Yearly' : 'Monthly';
                return old_renew < this.$root.user.renew;
            },
            paypalAmount() {
                return this.pay_target === 'avail_credit'
                    ? Number(this.add_to_credit).toFixed(2)
                    : Number(this.accountDueWithCredit).toFixed(2);
            },
        },
        methods: {
            getLocAllAddons() {
                return _.groupBy(this.loc_all_addons, 'rowpos');
            },
            planLvL(code) {
                switch (code) {
                    case 'basic': return 1;
                    case 'advanced': return 2;
                    case 'enterprise': return 3;
                }
            },
            presentInUserAddons(addon) {
                return _.findIndex(this.$root.user._subscription._addons, {code: addon.code}) > -1;
            },
            presentInNextSubscriptionAddons(addon) {
                if (this.$root.user._next_subscription) {
                    return _.findIndex(this.$root.user._next_subscription._addons, {code: addon.code}) > -1;
                } else {
                    return false;
                }
            },
            updateData(key) {
                if (key) {
                    this.$root.user[key] = !this.$root.user[key];
                }

                if (this.$root.user.use_credit && this.selectedPlanCost > this.$root.user.avail_credit) {
                    this.$root.user.recurrent_pay = 0;
                }
                PaymentFunctions.updateUser(this.$root.user);
            },
            prepareStripe() {
                if (this.$root.user._cards.length) {
                    this.sendPaying('StripeCard', 0);
                } else {
                    Swal('', 'You should link card before you can confirm payment.');
                }
            },
            sendPaying(url, order_id) {
                order_id = order_id || '';

                let type = this.pay_target;
                if (type !== 'balance' && type !== 'avail_credit') {
                    type = 'avail_credit';
                }

                let amount = type === 'avail_credit' ? Number(this.add_to_credit*100).toFixed(0) : Number(this.accountDueWithCredit*100).toFixed(0);
                amount = Number(amount);
                let used_credit = Number(this.accountDue*100).toFixed(0) - Number(this.accountDueWithCredit*100).toFixed(0);

                $.LoadingOverlay('show');
                axios.post('/ajax/user/pay'+url, {
                    order_id: order_id,
                    paypal_card: this.paypal_card,
                    amount: amount,
                    used_credit: used_credit,
                    type: type,
                    renew: this.$root.user.renew,
                    plan_code: this.tmp_plan_code,
                    all_addons: this.loc_all_addons
                }).then(({ data }) => {
                    if (data.error) {
                        Swal('', data.error);
                        return;
                    }
                    if (data.approve_url) {
                        window.location = data.approve_url;
                        return;
                    }
                    this.$root.user._available_features = data.user._available_features;
                    this.$root.user.avail_credit = data.user.avail_credit;
                    this.$root.user.stripe_card_id = data.user.stripe_card_id;
                    this.$root.user.stripe_card_last = data.user.stripe_card_last;
                    this.$root.user.paypal_card_id = data.user.paypal_card_id;
                    this.$root.user.paypal_card_last = data.user.paypal_card_last;
                    this.pay_target = '';
                    if (type === 'balance') {
                        this.$root.user._subscription = data.user._subscription;
                        _.each(this.loc_all_addons, (addon) => {
                            addon._old_checked = addon._checked;
                        });
                        if (!amount) {
                            Swal('Payment made. $'+Number(used_credit/100).toFixed(2)+' is deduced from the account credit.');
                        }
                    } else {
                        this.add_to_addon = null;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            prepareNextSubscription() {
                $.LoadingOverlay('show');
                axios.post('/ajax/user/next-subscription', {
                    renew: this.$root.user.renew,
                    plan_code: this.tmp_plan_code,
                    all_addons: this.loc_all_addons
                }).then(({ data }) => {
                    this.$root.user._next_subscription = data.subscription;
                    _.each(this.loc_all_addons, (addon) => {
                        addon._checked = this.$root.user._next_subscription ? this.presentInNextSubscriptionAddons(addon) : this.presentInUserAddons(addon);
                        addon._old_checked = addon._checked;
                    });
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteCard(type, id) {
                Swal({
                    title: 'Unlink Card',
                    text: 'Are you sure?',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        $.LoadingOverlay('show');
                        axios.delete('/ajax/user/unlink-card', {
                            params: {
                                type: type,
                                id: id
                            }
                        }).then(({ data }) => {
                            this.$root.user._cards = data._cards;
                            this.$root.user.paypal_card_id = data.user.paypal_card_id;
                            this.$root.user.paypal_card_last = data.user.paypal_card_last;
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },
            getActiveText() {
                let res = '';
                if (this.$root.user._next_subscription) {
                    res = '(Ending on '
                        + moment().add(this.$root.user._subscription.left_days-1, 'days').format("YYYY-MM-DD")
                        + ')'
                } else {
                    let add_days = this.newRenew ? 365 : this.$root.user._subscription.left_days - 1;
                    res = '(Active'
                        + (
                            this.$root.user._subscription.left_days > 0 && this.$root.user._subscription.plan_code !== 'basic' ?
                                ' through ' + moment().add(add_days, 'days').format("YYYY-MM-DD") :
                                ''
                        )
                        + ')'
                }
                return res;
            },
            getNextText() {
                return '(Starting on '
                    + moment().add(this.$root.user._subscription.left_days, 'days').format("YYYY-MM-DD")
                    + ')'
            },
            getNextCycleDate() {
                let add_days = (this.$root.user._subscription.plan_code !== 'basic' ? this.$root.user._subscription.left_days : 0)
                    //+ (this.$root.user.renew === 'Yearly' ? 365 : 30)
                    - this.periodNotifDays;
                return moment().add( Math.max(add_days, 0) , 'days').format("YYYY-MM-DD");
            },
            addToCreditKey() {
                let val = String(this.wrap_credit);
                val = val.charAt(0) == '$' ? val.substr(1) : val;
                this.add_to_credit = to_float(val);
                this.wrap_credit = '$' + val;
            },
            hideMenu(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.$emit('popup-close');
                    this.$root.set_e__used(this);
                }
            },
            toggleAdn(adn) {
                if (adn.code === 'all') {
                    let stat = !adn._checked;
                    _.each(this.loc_all_addons, (el) => {
                        el._checked = stat;
                    });
                } else {
                    adn._checked = !adn._checked;
                }
            },
            payByPal(paypal) {
                this.sendPaying('PayPalCard', paypal.orderID);
            },
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);

            _.each(this.$root.settingsMeta.all_addons, (addon) => {
                let adn = Object.assign({}, addon);
                adn._checked = this.$root.user._next_subscription ? this.presentInNextSubscriptionAddons(addon) : this.presentInUserAddons(addon);
                adn._old_checked = adn._checked;
                this.loc_all_addons.push(adn);
            });
            this.loc_all_addons = _.orderBy(this.loc_all_addons, 'is_special', 'asc');
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

        .header-block {
            h1 {
                text-align: center;
                margin-bottom: 25px;
                margin-top: 0;
            }
        }

        .cost-block {
            .renew-chk {
                height: 40px;
                display: flex;
                align-items: center;
            }
        }

        .check-container {
            padding-left: 30px;

            .cont-col-width {
                width: 50%;
                display: inline-block;
            }
        }

        .plan-block {}

        .addon-block {}

        .pay-block {
            .row {
                height: 35px;

                .col-md-4 {
                    height: 30px;
                    display: flex;
                    align-items: center;
                }
            }
        }

        .plans-wrapper {
            margin: 10px 0;
        }

        .row-container {
            display: flex;
            align-items: center;
        }

        .close-btn {
            padding: 3px;
            cursor: pointer;
        }

        .little_input {
            width: 85px;
            display: inline-block;
            height: 30px;
            position: relative;
            top: 1px;
        }

        .red {
            color: #F00;
            font-weight: bold;
        }

        .green {
            color: #0B0;
            font-weight: bold;
        }

        .bottom-text {
            position: relative;
            left: 10px;
            bottom: 10px;
        }

        .notices {
            position: relative;
            margin-top: 5px;

            label {
                margin: 0;
            }

            table {
                td {
                    padding: 0 10px 5px 0;
                }
            }

            .inline-radio {
                display: inline-block;
                padding: 5px 10px;
                border: 1px solid #ccc;
            }

            .inline-select {
                padding: 6px;
                display: inline-block;
            }
        }

        .credit_check {
            width: 20px;
            height: 20px;
            position: relative;
            top: 5px;
            margin: 0;
        }

        .stripe_elements_wrapper {
            display: inline-block;
            width: calc(100% - 68px);
        }

        .flex {
            display: flex;
            align-items: center;
        }

        .margin-left {
            margin-left: 15px;
        }

        .stripe_table {
            width: 100%;
            td,th {
                text-align: center;
            }
            th {
                font-weight: bold;
            }
            .txt-left {
                text-align: left;
            }
            .txt-right {
                text-align: right;
            }
        }
        .card_wr {
            display: flex;
            height: 20px;
        }

        .sel_card {
            margin: 0 10px;
            height: 20px;
            width: 20px;
        }

        .divider {
            border-top: 3px solid #666;
            margin: 10px 0;
        }

        .row-margin {
            margin-left: -5px;
        }
    }
</style>