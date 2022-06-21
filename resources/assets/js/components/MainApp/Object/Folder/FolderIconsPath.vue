<template>
    <div class="folder-icons-path" :class="route_group ? 'stim-icons' : ''" :style="{zIndex: zi}">
        <template v-if="$root.user.sub_icon">
            <img :src="$root.fileUrl({url:$root.user.sub_icon})" class="icon-img"/>
            <!--<span class="icon-divider"> / </span>-->
        </template>
        <template v-for="folder in reversedIcons" v-if="folder.icon_path">
            <img :src="$root.fileUrl({url:folder.icon_path})" class="icon-img"/>
            <span class="icon-divider"> / </span>
        </template>
    </div>
</template>

<script>
    import {eventBus} from '../../../../app';

    export default {
        name: "FolderIconsPath",
        data: function () {
            return {
                zi: 1000
            }
        },
        props:{
            route_group: String,
            iconsArray: Array,
        },
        computed: {
            reversedIcons() {
                return this.iconsArray && this.iconsArray.length ? this.iconsArray.reverse() : [];
            },
        },
        methods: {
            navbarPopup(val) {
                this.zi = val ? 1 : 1000;
            }
        },
        mounted() {
            eventBus.$on('navbar-popup', this.navbarPopup);
        },
        beforeDestroy() {
            eventBus.$off('navbar-popup', this.navbarPopup);
        }
    }
</script>

<style lang="scss" scoped>
    .folder-icons-path {
        position: fixed;
        top: 0;
        left: 250px;
        height: 65px;
        z-index: 1000;
        display: flex;
        align-items: center;

        .icon-img {
            max-height: 40px;
        }
        .icon-divider {
            font-size: 3.5em;
            font-weight: bold;
            padding: 0 5px;
        }
    }
    .stim-icons {
        left: initial;
        height: 80px;
        right: 33%;
    }

    @media (max-width: 1440px) {
        .stim-icons {
            right: 22%;
        }
    }
</style>