<template>
    <div class="secpos__wrap"
         :style="wrapStyle"
         @click="settings.selSecPos(secpos, 'P0'+idx)"
         @contextmenu.prevent="$emit('right-click', secpos)"
    >
        <span>{{ 'P0'+idx }}</span>
    </div>
</template>

<script>
    import {Secpos} from "./Secpos";
    import {Settings} from "./Settings";

    export default {
        name: 'CanvGroupSecpos',
        mixins: [
        ],
        components: {
        },
        data() {
            return {
            }
        },
        computed: {
            wrapStyle() {
                let is_sel = this.settings.sel_secpos
                    && this.settings.sel_secpos._id === this.secpos._id
                    && this.settings.sel_secpos.posidx === 'P0'+this.idx;
                return {
                    border: is_sel ? '2px solid #F00' : (this.is_primary ? '2' : '1')+'px solid #444',
                    padding: '0 3px',
                };
            },
        },
        props: {
            secpos: Secpos,
            settings: Settings,
            is_primary: Boolean,
            idx: Number,
        },
        watch: {
        },
        methods: {
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .secpos__wrap {
        cursor: pointer;
    }
</style>