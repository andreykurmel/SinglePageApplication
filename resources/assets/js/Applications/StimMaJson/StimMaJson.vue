<template>
    <div class="container-wrapper">
        <div class="flexer" v-if="warnings_present.length">
            <div>
                <label>Warnings:</label>
                <br>
                <label v-html="warnings_present.join('<br>')"></label>
            </div>
        </div>

        <div class="flexer" v-if="errors_present.length">
            <div>
                <label>Errors:</label>
                <br>
                <label v-html="errors_present.join('<br>')"></label>
            </div>
        </div>
        <div class="flexer" v-else="">
            <div>
            <label>Results:</label>
                <label>- Json file has been created successfully!</label>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'StimMaJson',
        mixins: [
        ],
        components: {
        },
        data() {
            return {
            }
        },
        props: {
            errors_present: Array,
            warnings_present: Array,
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

        .flexer {
            height: 50%;
        }
    }
</style>