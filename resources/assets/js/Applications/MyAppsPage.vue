<template>
    <div class="full-frame">
        <div v-if="$root.user.subdomain" class="container">
            <div>
                <h1>My Apps (@{{ $root.user.subdomain }})</h1>
                <div class="divider"></div>

                <div v-if="!my_apps.length">
                    <span>You do not have any App.</span>
                </div>
                <div v-else="" class="app-row">
                    <template v-for="app in my_apps">
                        <app-element :app="app"></app-element>
                    </template>
                    <div class="app-row__after"></div>
                </div>
            </div>
            <div>
                <h1>Subscribed Apps</h1>
                <div class="divider"></div>

                <div v-if="!subscribed_apps.length">
                    <span>You have not subscribed to any App.</span>
                </div>
                <div v-else="" class="app-row">
                    <template v-for="groups in subscribed_apps">
                        <h2>@{{ groups[0].subdomain }}</h2>
                        <div class="divider"></div>
                        <template v-for="app in groups">
                            <app-element :app="app" :subs_ids="subs_ids"></app-element>
                        </template>
                        <div class="app-row__after"></div>
                    </template>
                </div>

                <div>
                    <a :href="browse_link">Browse</a> Apps?
                </div>
            </div>
        </div>
        <div v-else="" class="container">
            <h1>Please specify the subdomain in your settings!</h1>
        </div>
    </div>
</template>

<script>
    import AppElement from "./AppElement";

    export default {
        components: {
            AppElement
        },
        name: 'MyAppsPage',
        data() {
            return {
                browse_link: '',
            }
        },
        props: {
            my_apps: Array,
            subscribed_apps: Array,
            subs_ids: Array,
        },
        methods: {
        },
        mounted() {
            this.browse_link = this.$root.clear_url.replace('://', '://apps.') + '/list';
        }
    }
</script>

<style lang="scss" scoped="">
    @import "appsList";
</style>