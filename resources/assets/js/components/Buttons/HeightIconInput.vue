<template>
    <div class="input-wrapper" @click="show()">
        <input v-if="edit"
               type="number"
               ref="height_input"
               class="form-control"
               v-model="col.height"
               @change="$emit('height-changed')"
               @blur="edit = false;"
        >
        <span v-else="">{{ col.height }}</span>
    </div>
</template>

<script>
    export default {
        name: "HeightIconInput",
        data: function () {
            return {
                edit: false
            }
        },
        props:{
            col: Object,
        },
        methods: {
            show() {
                this.edit = true;
                this.$nextTick(function () {
                    this.$refs.height_input.focus();
                })
            }
        },
    }
</script>

<style scoped>
    .input-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        padding: 5px;
        display: flex;
        align-items: center;
    }
</style>