<template>
    <div class="keys">
        <div class="key-row">
            <button class="btn btn-circle btn-default" @click="sendDigit('1')">1</button>
            <button class="btn btn-circle btn-default" @click="sendDigit('2')">2
                <span>A B C</span>
            </button>
            <button class="btn btn-circle btn-default" @click="sendDigit('3')">3
                <span>D E F</span>
            </button>
        </div>
        <div class="key-row">
            <button class="btn btn-circle btn-default" @click="sendDigit('4')">4
                <span>G H I</span>
            </button>
            <button class="btn btn-circle btn-default" @click="sendDigit('5')">5
                <span>J K L</span>
            </button>
            <button class="btn btn-circle btn-default" @click="sendDigit('6')">6
                <span>M N O</span>
            </button>
        </div>
        <div class="key-row">
            <button class="btn btn-circle btn-default" @click="sendDigit('7')">7
                <span>P Q R S</span>
            </button>
            <button class="btn btn-circle btn-default" @click="sendDigit('8')">8
                <span>T U V</span>
            </button>
            <button class="btn btn-circle btn-default" @click="sendDigit('9')">9
                <span>W X Y Z</span>
            </button>
        </div>
        <div class="">
            <button class="btn btn-circle btn-default" @click="sendDigit('*')">*</button>
            <button class="btn btn-circle btn-default" @click="sendDigit('0')">0</button>
            <button class="btn btn-circle btn-default" @click="sendDigit('#')">#</button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "TwilioDialPad",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
            }
        },
        props: {
        },
        computed: {
        },
        methods: {
            sendDigit(key) {
                this.$emit('digit-click', key);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    textarea:hover, input:hover, textarea:active, input:active, textarea:focus,
    button:focus, button:active, button:hover, label:focus, .btn:active,
    .btn.active {
        outline: 0 !important;
        -webkit-appearance: none;
    }

    .btn-circle {
        width: 49px;
        height: 49px;
        text-align: center;
        padding: 5px 0;
        font-size: 20px;
        line-height: 2.00;
        border-radius: 30px;
    }

    .keys {
        text-align: center;
        color: #fff;
    }

    .key-row {
        margin-bottom: 20px;
    }

    .key-row .btn {
        font-weight: 100;
    }

    .key-row .btn span {
        display: block;
        padding-top: 2px;
        font-size: 10px;
        color: #787878;
    }
</style>