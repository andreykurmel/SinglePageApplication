<template>
    <div class="user-wrapper" ref="history_user_info">

        <span class="user-name"
              @click="menu_opened = !menu_opened"
              v-html="$root.getUserSimple(h_user, tableMeta._owner_settings, 'history_user')"
        ></span>
        <span>, {{ $root.convertToLocal(history.created_on, user.timezone) }}:</span>

        <div v-show="menu_opened" class="popup-info">
            <a :href="'mailto:'+h_user.email">
                {{ h_user.email }}
            </a>
        </div>

    </div>
</template>

<script>
    import {eventBus} from './../../app';

    export default {
        name: "HistoryUserInfo",
        data: function () {
            return {
                menu_opened: false,
                h_user: this.history._user || {},
            };
        },
        props:{
            tableMeta: Object,
            user: Object,
            history: Object,
        },
        methods: {
            hideMenu(e) {
                let container = $(this.$refs.history_user_info);
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