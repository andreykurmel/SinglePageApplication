<template>
    <div class="full-width">
        <span v-if="!editing" @click="enableEdit">{{ inner_type }}</span>
        <input v-else
               class="form-control"
               ref="typeinput"
               v-model="inner_type"
               @blur="changeType"
        >
    </div>
</template>

<script>

    export default {
        name: 'TypeHeader',
        components: {
        },
        data() {
            return {
                editing: false,
                inner_type: this.type,
            }
        },
        props: {
            type: String,
        },
        methods: {
            enableEdit() {
                if (this.$root.user.is_admin || this.$root.user.role_id == 3) {
                    this.editing = true;
                    this.$nextTick(() => {
                        if (this.$refs.typeinput) {
                            this.$refs.typeinput.focus();
                        }
                    });
                }
            },
            changeType() {
                this.editing = false;
                if (this.inner_type && this.type !== this.inner_type) {
                    axios.put('/ajax/static-page/tab', {
                        old_type: this.type,
                        new_type: this.inner_type,
                    }).then(({data}) => {
                        window.location.reload();
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            }
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    span {
        cursor: pointer;
    }
</style>