<template>
    <div class="container-wrapper">
        <div class="flex flex--center full-height bold">
            <label v-html="messages.join('<br>')"></label>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'SimpleMessager',
        mixins: [
        ],
        components: {
        },
        data() {
            return {
            }
        },
        props: {
            messages: Array,
        },
        methods: {
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .container-wrapper {
        height: 100%;
        width: 100%;
        padding: 10px;
    }
</style>