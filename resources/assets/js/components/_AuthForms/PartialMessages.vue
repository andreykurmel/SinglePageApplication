<template>
    <div>
        <div v-if="settings.errors && settings.errors.length" class="alert alert-danger alert-notif">
            <ul class="list-unstyled mb-0">
                <li v-for="err in settings.errors">{{ err }}</li>
            </ul>
        </div>

        <template v-if="typeof settings.session_success === 'string'">
            <div class="alert alert-success alert-notif" v-if="settings.session_success">
                <i class="fa fa-check"></i>
                {{ settings.session_success }}
            </div>
        </template>
        <template v-else="">
            <div v-for="msg in settings.session_success" v-if="msg" class="alert alert-success alert-notif">
                <i class="fa fa-check"></i>
                {{ msg }}
            </div>
        </template>
    </div>
</template>

<script>
    export default {
        name: 'PartialMessages',
        props: {
            settings: Object,
        },
        mounted() {
            setTimeout(() => {
                $(".alert-notif").slideUp(600, function () {
                    $(this).remove();
                });
            }, 5000);
        },
    }
</script>