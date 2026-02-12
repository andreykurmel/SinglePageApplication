<template>
    <div class="notices">
        <template v-if="independent_cards">
            <div class="flex">
                <div ref="stripe_elements" class="stripe_elements_wrapper"></div>
                <button class="btn btn-success" @click="payByCard()">Pay</button>
            </div>
        </template>
        <template v-else="">
            <!-- cards -->
            <div v-show="$root.user._cards.length">
                <table class="stripe_table">
                    <tr>
                        <th colspan="6" class="txt-left">Select a saved credit card:</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Card Number</th>
                        <th>Name on Card</th>
                        <th>Expires on</th>
                        <th>ZIP</th>
                        <th></th>
                    </tr>
                    <tr v-for="card in $root.user._cards">
                        <td>
                            <div class="card_wr">
                                <input class="sel_card" type="radio" v-model="$root.user.selected_card" :value="card.id" @change="updateData()"/>
                                <img :src="'/assets/img/card'+card.stripe_card_brand+'.png'" width="20">
                            </div>
                        </td>
                        <td>Card: ****{{ card.stripe_card_last }}</td>
                        <td>{{ card.stripe_card_name }}</td>
                        <td>{{ card.stripe_exp_month }}/{{ card.stripe_exp_year }}</td>
                        <td>{{ card.stripe_card_zip }}</td>
                        <td class="txt-right">
                            <button class="btn btn-danger btn-sm" @click="deleteCard('Stripe', card.id)">Delete</button>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- add new card -->
            <div v-show="!$root.user.stripe_card_id">
                <div class="flex">
                    <div ref="stripe_elements" class="stripe_elements_wrapper"></div>
                    <button class="btn btn-primary" @click="addCard()">Add</button>
                </div>
            </div>
            <!-- pay with selected card -->
            <div class="form-group notices" v-if="confirm_pay">
                <button class="btn"
                        :disabled="!$root.user.selected_card"
                        :class="[$root.user.selected_card ? 'btn-success' : '']"
                        @click="$emit('payment-confirmed')"
                >Confirm the Payment</button>
            </div>
        </template>
        <!--stripe notices-->
        <div class="flex flex--space" style="align-items: flex-end;">
            <div>
                <label>Note:</label>
                <span>TablDA does not save user's credit card information.<br>Credit Card details are securely stored at</span>
                <label>&copy;Stripe</label>
            </div>
            <img src="/assets/img/secure-stripe-payment-logo.png" style="height: 75px;"/>
        </div>
    </div>
</template>

<script>
    import {PaymentFunctions} from "../../classes/PaymentFunctions";

    export default {
        name: "StripeBlock",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
                stripe: null,
                stripe_card: null,
            };
        },
        computed: {
        },
        props:{
            stripe_key: String,
            confirm_pay: Boolean,
            independent_cards: Boolean,
        },
        methods: {
            payByCard() {
                $.LoadingOverlay('show');
                this.stripe.createToken(this.stripe_card).then((result) => {
                    $.LoadingOverlay('hide');
                    if (result.error) {
                        Swal('Info', result.error.message);
                    } else {
                        this.$emit('pay-by-card', result.token);
                    }
                });
            },
            addCard() {
                $.LoadingOverlay('show');
                this.stripe.createToken(this.stripe_card).then((result) => {
                    $.LoadingOverlay('hide');
                    if (result.error) {
                        Swal('Info', result.error.message);
                    } else {
                        $.LoadingOverlay('show');
                        axios.post('/ajax/user/link-card', {
                            token: result.token
                        }).then(({ data }) => {
                            if (data.error) {
                                Swal('Info', data.error);
                                return;
                            }
                            this.stripe_card.clear();
                            this.$root.user._cards = data;
                            if (!this.$root.user.selected_card) {
                                this.$root.user.selected_card = data.id;
                            }
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },
            deleteCard(type, id) {
                Swal({
                    title: 'Info',
                    text: 'Unlink Card. Are you sure?',
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
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },
            mountStripeElements() {
                // Create a Stripe client.
                this.stripe = Stripe(this.stripe_key);
                let style = {
                    base: {
                        color: '#32325d',
                        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                            color: '#aab7c4'
                        }
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a'
                    }
                };
                //mount Stripe elements
                this.stripe_card = this.stripe.elements().create('card', {style: style});
                this.stripe_card.mount(this.$refs.stripe_elements);
            },
            updateData() {
                PaymentFunctions.updateUser(this.$root.user);
            },
        },
        mounted() {
            this.mountStripeElements();
        }
    }
</script>

<style lang="scss" scoped>
    .notices {
        position: relative;
        margin-top: 5px;

        label {
            margin: 0;
        }

        table {
            width: 100%;
            td {
                padding: 0 10px 5px 0;
            }
        }

        .txt-right {
            text-align: right;
            padding-right: 15px;
        }

        .stripe_elements_wrapper {
            display: inline-block;
            width: calc(100% - 68px);
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
    }
</style>