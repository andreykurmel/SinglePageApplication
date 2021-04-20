<template>
    <div class="user-wrapper" ref="message_user_info">

        <span class="user-name"
              @click="menu_opened = !menu_opened"
              v-html="getName()"
        ></span>

        <div v-show="menu_opened" class="popup-info">
            <a :href="'mailto:'+msg_user.email">
                {{ msg_user.email }}
            </a>
        </div>

    </div>
</template>

<script>
    import {eventBus} from './../../../app';

    export default {
        name: "MessageUserInfo",
        data: function () {
            return {
                menu_opened: false,
                msg_user: this.getMsgUser(),
                usr_flds: {
                    user_fld_show_image: 1,
                    user_fld_show_first: 1,
                    user_fld_show_last: 1,
                    user_fld_show_email: 0,
                    user_fld_show_username: 0,
                },
            };
        },
        props:{
            msgObj: Object,
            type: String
        },
        methods: {
            getMsgUser() {
                if (this.type === 'from') {
                    return this.msgObj._from_user || {};
                } else {
                    return this.msgObj._to_user || this.msgObj._to_user_group || {};
                }
            },
            getName() {
                if (this.type === 'from') {
                    return (
                        this.$root.user.id === this.msgObj._from_user.id
                            ? 'Me'
                            : this.$root.getUserSimple(this.msgObj._from_user, this.usr_flds)
                    );
                } else {
                    if (this.msgObj._to_user) {
                        return (
                            this.$root.user.id === this.msgObj._to_user.id
                                ? 'Me'
                                : this.$root.getUserSimple(this.msgObj._to_user, this.usr_flds)
                        );
                    } else {
                        return (
                            this.msgObj._to_user_group
                                ? this.msgObj._to_user_group.name+' (Group)'
                                : 'All'
                        );
                    }
                }
            },
            hideMenu(e) {
                let container = $(this.$refs.message_user_info);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            }
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .user-wrapper {
        position: relative;
        display: inline-block;

        .user-name {
            cursor: pointer;
        }

        .popup-info {
            position: absolute;
            top: 100%;
            left: 0;
            padding: 3px 6px;
            border: 1px solid #CCC;
            background-color: #FFF;
            border-radius: 5px;
        }
    }
</style>