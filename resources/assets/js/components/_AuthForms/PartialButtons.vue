<template>
    <div class="row" v-if="settings.social_provider">
        <div class="col-md-4 col-spaced">
            <a class="btn btn-block btn-social btn-facebook" :href="settings.root_url + '/auth/facebook/login'">
                <i class="fab fa-facebook fa-2x"></i>
                Facebook
            </a>
        </div>
        <div class="col-md-4 col-spaced">
            <a class="btn btn-block btn-social btn-linkedin" :href="settings.root_url + '/auth/linkedin/login'">
                <i class="fab fa-linkedin fa-2x"></i>
                LinkedIn
            </a>
        </div>
        <div class="col-md-4 col-spaced">
            <a class="btn btn-block btn-social btn-google" :href="settings.root_url + '/auth/google/login'">
                <i class="fab fa-google-plus-square fa-2x"></i>
                Google+
            </a>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'PartialButtons',
        props: {
            settings: Object
        },
    }
</script>

<style lang="scss" scoped="">
    .row {
        margin-right: -15px;
        margin-left: -15px;

        .col-md-4 {
            width: 33.33333333%;
            float: left;
            padding: 0 15px;

            .btn-social {
                position: relative;
                padding-left: 38px;
                text-align: left;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                height: 34px;
            }
        }
    }
</style>