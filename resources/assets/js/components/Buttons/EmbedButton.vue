<template>
    <div ref="embed_button"
         class="embed_button flex flex--center"
         title="Embed code"
         :style="{cursor: isDisabled ? 'not-allowed' : 'pointer'}"
    >
        <span @click="menu_opened = !menu_opened && !isDisabled"><></span>
        <div v-show="menu_opened" class="embed_button_menu" :class="[isFixed ? 'fixed-pos' : '']">
            <label>Use the following code for embedding the {{ (isDcr ? 'form' : 'view') }} on your site.</label>
            <div class="embed_code" @click="copyCode()">
                {{ embed_code }}
                <div v-if="msgshow" class="msg_copied">Code Copied!</div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    export default {
        name: "EmbedButton",
        data: function () {
            return {
                msgshow: false,
                menu_opened: false,
                embed_code: '<iframe src="'
                    + this.$root.clear_url
                    + (this.isDcr ? '/embed-dcr/' : (this.isFolder ? '/embed/' : '/mrv/'))
                    + this.hash
                    + '" width="100%" height="100%"></iframe>'
            }
        },
        props:{
            isDisabled: Boolean,
            hash: String,
            isFolder: Boolean,
            isDcr: Boolean,
            isFixed: Boolean,
        },
        methods: {
            copyCode() {
                SpecialFuncs.strToClipboard(this.embed_code);
                this.msgshow = true;
                setTimeout(() => {
                    this.msgshow = false;
                }, 1500);
            },
            hideMenu(e) {
                let container = $(this.$refs.embed_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
        }
    }
</script>

<style scoped lang="scss">

    .embed_button_menu {
        position: absolute;
        right: 100%;
        top: 100%;
        min-width: 250px;
        width: min-content;
        z-index: 500;
        font-size: 14px;
        background-color: #FFF;
        border: 1px solid #CCC;
        padding: 10px;
        cursor: auto;
        white-space: normal;
        text-align: left;

        .embed_code {
            padding: 5px;
            border: 1px solid #CCC;
            border-radius: 5px;
        }
    }

    .embed_button {
        cursor: pointer;
        width: 35px;
        height: 30px;
        padding: 0;
        font-size: 22px;
        background-color: #FFF;
        position: relative;
        outline: none;

        span {
            font-weight: bold;
            position: relative;
        }

        .fixed-pos {
            position: fixed;
            top: initial;
            right: initial;
            transform: translate(calc(-50% - 15px), calc(50% + 15px));
        }

        .msg_copied {
            position: absolute;
            right: 0;
            bottom: 0;
            color: #00f;
        }
    }
</style>