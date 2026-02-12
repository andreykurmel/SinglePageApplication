<template>
    <div v-if="$root.user.id && !$root.user.tos_accepted" class="full-frame tos_checker">
        <div class="tos_elem">
            <div>
                <label>
                    Please read Terms of Service carefully and<br>
                    accept to proceed.
                </label>
            </div>
            <div>
                <input type="checkbox" :disabled="!tos_doc_opened" v-model="tos_checked"/>
                <label>I accept
                    <a href="/tos" target="_blank" v-on:click="tos_doc_opened = true">Terms of Service</a>
                </label>
            </div>
            <div>
                <button class="btn btn-success btn-lg" :disabled="!tos_doc_opened || !tos_checked" @click="saveTos()">
                    Submit
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'TosChecker',
        data() {
            return {
                tos_checked: false,
                tos_doc_opened: false,
            }
        },
        props: {
        },
        methods: {
            saveTos() {
                $.LoadingOverlay('show');
                axios.post('/ajax/user/tos-accepted').then(({ data }) => {
                    this.$root.user.tos_accepted = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            }
        }
    }
</script>

<style lang="scss" scoped>
    .tos_checker {
        background-color: #EEE;
        position: fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        top: 0;
        left: 0;

        .tos_elem {
            font-size: 1.5em;
        }
    }
</style>