<template>
    <div class="container-wrapper flex flex--col flex--center">
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
                <label>Json file created!</label>
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

<style lang="scss" scoped="">
    .container-wrapper {
        height: 100%;

        .flexer {
            display: flex;
            background-color: #005fa4;
            color: #FFF;
            padding: 25px;
            border-radius: 20px;

            .m-right {
                margin-right: 25px;
            }
        }
    }
</style>