<template>
    <div v-if="$root.user.id" class="saving-message" :style="{left: (left_shift || 0)+'px'}">
        {{ getMsg() }}
    </div>
</template>

<script>
    export default {
        name: "SavingMessage",
        data: function () {
            return {};
        },
        props:{
            left_shift: Number,
            msg_type: Number,
        },
        methods: {
            getMsg() {
                let res = '';
                switch (this.msg_type) {
                    case 0: res = 'All Saved'; break;
                    case 1: res = 'Saving...'; break;
                    case 2: res = 'Loading...'; break;
                    default: res = ''; break;
                }
                if (this.msg_type === 0) {
                    setTimeout(() => {
                        this.$root.sm_msg_type = undefined;
                    }, 2000);
                }
                return res;
            }
        }
    }
</script>

<style lang="scss" scoped>
    .saving-message {
        position: relative;
        display: flex;
        font-style: italic;
        font-size: 14px;
        height: 100%;
        align-items: flex-end;
    }
</style>