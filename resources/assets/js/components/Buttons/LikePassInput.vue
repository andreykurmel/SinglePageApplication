<template>
    <input type="text" class="form-control" ref="input" :value="visible" @input="keyDown"/>
</template>

<script>
    export default {
        name: "LikePassInput",
        data: function () {
            return {
                hidden_num: 0,
                visible: '',
                real: ''
            }
        },
        props:{
            field: String
        },
        methods: {
            keyDown(e) {
                let val = this.$refs.input.value;
                if (val.length && val.charAt(0) !== '*') {
                    this.real = val;
                } else
                if (val.length <= this.real.length) {
                    this.real = this.real.substr(0, val.length);
                } else {
                    this.real += String( val.substr(this.real.length) );
                }
                this.visible = '*'.repeat(val.length);
                this.$emit('inputed', this.real);
            }
        }
    }
</script>

<style scoped>
</style>