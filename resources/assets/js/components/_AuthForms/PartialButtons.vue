<template>
    <div class="flex flex--space flex--center-v" v-if="settings.social_provider">
        <a class="btn btn-block btn-social btn-facebook autn-btn" :href="settings.root_url + '/auth/facebook/login'">
            <i class="fab fa-facebook fa-2x"></i>
            Facebook
        </a>
        <a class="btn btn-block btn-social btn-linkedin autn-btn" :href="settings.root_url + '/auth/linkedin/login'">
            <i class="fab fa-linkedin fa-2x"></i>
            LinkedIn
        </a>
        <a class="autn-btn auth-google" :href="settings.root_url + '/auth/google/login'"></a>
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

<style lang="scss" scoped>
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