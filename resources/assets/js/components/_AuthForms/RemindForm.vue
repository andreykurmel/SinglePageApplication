<template>
    <div class="modal-form modal-sm">
        <div class="form-wrap">
            <h1 class="remind">Forgot Your Password?</h1>

            <form role="form" :action="settings.root_url+'/password/remind'" method="POST" id="remind-password-form" autocomplete="off">
                <input type="hidden" :value="settings.csrf_token" name="_token">

                <div class="form-group password-field input-icon">
                    <label class="sr-only">Email</label>
                    <i class="fa fa-at"></i>
                    <input type="email" name="email" class="form-control" placeholder="Your E-Mail">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-lg btn-block" id="btn-reset-password">
                        Reset Password
                    </button>
                    <a href="javascript:void(0)" @click="$emit('show_login')" class="btn btn-default btn-lg btn-block">
                        Cancel
                    </a>
                </div>
            </form>

        </div>
        <div class="row">
            <div class="col-xs-12 footer">
                <p>Copyright © - {{ settings.app_name }} {{ settings.year }}</p>
            </div>
        </div>
    </div>
</template>

<script>
    import PartialMessages from "./PartialMessages";

    export default {
        name: 'RemindForm',
        components: {
            PartialMessages,
        },
        data: function () {
            return {
            }
        },
        props: {
            settings: Object,
        },
        computed: {
        },
        methods: {
        },
        created() {
        }
    }
</script>

<style scoped="" lang="scss">
    @import "ModalForm";

    .modal-sm {
        width: 375px;
    }
</style>