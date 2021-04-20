<template>
    <div class="notices">
        You are currently paying with your PayPal account
        (but you can switch to using a credit card at any time).<br>
        Click the button to proceed: <div ref="paypal_button_card" class="paypal-button-card"></div>

        <!--<div style="margin-top: 15px;">-->
        <!--<button class="btn btn-success" @click="sendPaying('PayPalAccount')">Go To PayPal Page</button>-->
        <!--</div>-->

    </div>
</template>

<script>
    export default {
        name: "PaypalBlock",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
            };
        },
        computed: {
        },
        props:{
            paypal_amount: Number|String,
        },
        methods: {
            mountPaypal() {
                paypal.Buttons({
                    style: {
                        layout: 'horizontal',
                        label: 'checkout',
                        color: 'gold',
                        tagline: false,
                    },
                    createOrder: (data, actions) => {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: Number(this.paypal_amount)
                                }
                            }]
                        });
                    },
                    onApprove: (data, actions) => {
                        return actions.order.capture().then((details) => {
                            this.$emit('payment-confirmed', data);
                        });
                    },
                }).render(this.$refs.paypal_button_card);
            },
        },
        mounted() {
            this.mountPaypal();
        }
    }
</script>

<style lang="scss" scoped>
    .notices {
        position: relative;
        margin-top: 5px;

        .paypal-button-card, .paypal-button-credit {
            display: inline-block;
            width: 200px;
            position: relative;
            top: 12px;
            margin-bottom: 15px;
        }
    }
</style>