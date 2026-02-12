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
            <label v-if="['stripe','paypal'].indexOf(payment_method) > -1"
                   class="payment_title"
            >You are making a payment of ${{ payment_amount }}:</label>
            <label v-else="" class="">Payment Method not found in the Row!</label>

            <stripe-block
                    v-if="payment_method === 'stripe' && input_link._stripe_user_key"
                    :stripe_key="input_link._stripe_user_key.public_key"
                    :confirm_pay="true"
                    :independent_cards="true"
                    @pay-by-card="StripeByPay"
            ></stripe-block>
            <paypal-block
                    v-if="payment_method === 'paypal' && input_link._paypal_user_key"
                    :paypal_amount="payment_amount"
                    :paypal_description="paypal_description"
                    @payment-confirmed="PaypalByPay"
            ></paypal-block>
        </div>
    </div>
</template>

<script>
    import StripeBlock from "../../components/CommonBlocks/StripeBlock";
    import PaypalBlock from "../../components/CommonBlocks/PaypalBlock";

    export default {
        name: 'PaymentProcessingPage',
        mixins: [
        ],
        components: {
            PaypalBlock,
            StripeBlock,
        },
        data() {
            return {
            }
        },
        props: {
            input_row: Object,
            input_link: Object,
            errors_present: Array,
        },
        computed: {
            payment_amount() {
                let amofld = this.input_link._payment_amount_fld ? this.input_link._payment_amount_fld.field : '';
                return this.input_row[amofld];
            },
            paypal_description() {
                let descfld = this.input_link._payment_description_fld ? this.input_link._payment_description_fld.field : '';
                return this.input_row[descfld];
            },
            payment_method() {
                let methodfld = this.input_link._payment_method_fld ? this.input_link._payment_method_fld.field : '';
                let strmethod = String(this.input_row[methodfld]).toLowerCase();
                if (/paypal/.test(strmethod)) { return 'paypal'; }
                if (/stripe|card/.test(strmethod)) { return 'stripe'; }
                return '';
            },
        },
        methods: {
            StripeByPay($token) {
                if ($token) {
                    this.sendPaying('StripeCard', 0, $token);
                } else {
                    Swal('Info', 'You should add card before you can confirm payment.');
                }
            },
            PaypalByPay(paypal) {
                this.sendPaying('PayPalCard', paypal.orderID);
            },
            sendPaying(url, order_id, $token) {
                order_id = order_id || '';
                $.LoadingOverlay('show');
                axios.post('/ajax/payment/via'+url, {
                    row_id: this.input_row.id,
                    link_id: this.input_link.id,
                    order_id: order_id,
                    token: $token,
                }).then(({ data }) => {
                    if (data.error) {
                        Swal('Info', data.error);
                    } else {
                        Swal({
                            title: 'Info',
                            text: 'Payment successful!',
                            timer: 1500,
                        }).then(() => {
                            window.close();
                        });
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
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
            background-color: #FFF;
            color: #222;
            padding: 25px;
            border-radius: 20px;

            .payment_title {
                font-size: 2em;
                text-align: center;
                display: block;
                margin-bottom: 30px;
            }
        }
    }
</style>