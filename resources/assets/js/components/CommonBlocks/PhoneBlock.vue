<template>
    <div :class="[right_drop ? 'intl__drop__right' : '', full_width ? 'full-width' : 'wrap']">
        <input ref="tel_input" class="form-control" :value="show_value" @change="getPhone">
        <i v-if="is_invalid" class="fas fa-times-circle red err"></i>
    </div>
</template>

<script>
    export default {
        name: "PhoneBlock",
        mixins: [
        ],
        components: {
        },
        data() {
            return {
                intl: null,
                is_invalid: false,
                show_value: '',
                internal_value: '',
            };
        },
        computed: {
        },
        props: {
            full_width: Boolean,
            right_drop: Boolean,
            value: String,
        },
        watch: {
            value: {
                handler(v) {
                    if (this.internal_value != v) {
                        this.intl.setNumber(v);
                    }
                },
                immediate: true,
            },
        },
        methods: {
            getPhone() {
                this.is_invalid = !this.intl.isValidNumber();
                if (!this.is_invalid && this.$refs.tel_input) {
                    this.internal_value = this.intl.getNumber();
                    this.$emit('input', this.internal_value);
                    this.show_value = this.intl.getNumber(2);
                }
            },
        },
        mounted() {
            this.intl = IntlTelInput(this.$refs.tel_input, {
                utilsScript: '/assets/js/utils.js',
                /*customPlaceholder: () => {
                    return '(555)-555-5555';
                }*/
            });
        },
        beforeDestroy() {
            if (this.intl && this.intl.destroy) {
                this.intl.destroy();
            }
        }
    }
</script>

<style lang="scss" scoped>
    .wrap {
        position: relative;
        width: fit-content;

        .err {
            position: absolute;
            right: 5px;
            top: 12px;
            z-index: 100;
        }
    }
</style>

<style lang="scss">
    .iti--allow-dropdown {
        width: 100%;
    }
    .intl__drop__right {
        .iti__country-list {
            right: -193px;
        }
    }
</style>