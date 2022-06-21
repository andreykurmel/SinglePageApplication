<template>
    <div class="container-app">
        <a :href="link">
            <span>{{ app.name }}</span>
        </a>
        <i v-if="subs_ids && $root.user.id"
           :class="[subscribed ? 'fas' : 'far']"
           class="fa-heart"
           @click="toggleSubscription()"
        ></i>
    </div>
</template>

<script>
    export default {
        name: 'AppElement',
        data() {
            return {
                link: '',
                subscribed: false,
            }
        },
        props: {
            app: Object,
            subs_ids: Array
        },
        methods: {
            toggleSubscription() {
                this.subscribed = !this.subscribed;
                $.LoadingOverlay('show');
                axios.post('/ajax/apps/toggle', {
                    app_id: this.app.id,
                    status: this.subscribed ? 1 : 0,
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            }
        },
        mounted() {
            this.link = this.$root.clear_url.replace('://', '://'+this.app.subdomain+'.');
            this.link += '/apps'+this.app.app_path;

            if (this.subs_ids) {
                this.subscribed = this.subs_ids.indexOf(this.app.id) > -1;
            }
        }
    }
</script>

<style lang="scss" scoped>
    .container-app {
        width: 150px;
        height: 100px;
        margin: 15px;
        float: left;
        display: flex;
        border: 2px solid #777;
        border-radius: 15px;
        cursor: pointer;
        position: relative;

        a {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;

            &:hover {
                opacity: 0.7;
            }
        }

        .fa-heart {
            position: absolute;
            right: 10px;
            bottom: 5px;
            z-index: 1;
            color: #700;
            font-size: 24px;
            padding: 5px;
            opacity: 0.6;

            &:hover {
                opacity: 1;
            }
        }
    }
</style>