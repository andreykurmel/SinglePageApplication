<template>
    <div class="container">
        <h1>All Apps</h1>
        <div class="divider"></div>

        <template v-for="groups in all_apps">
            <h2>@{{ groups[0].subdomain }}</h2>
            <div class="divider"></div>
            <template v-for="app in groups">
                <app-element :app="app" :subs_ids="subs_ids"></app-element>
            </template>
            <div class="app-row__after"></div>
        </template>
    </div>
</template>

<script>
    import AppElement from "./AppElement";

    export default {
        name: 'ListTabldaApps',
        components: {
            AppElement
        },
        data() {
            return {
            }
        },
        props: {
            subs_ids: Array,
            all_apps: Array,
        },
        methods: {
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped="">
    @import "appsList";
</style>