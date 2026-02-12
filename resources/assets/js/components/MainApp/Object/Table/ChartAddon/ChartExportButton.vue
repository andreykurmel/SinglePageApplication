<template>
    <div ref="export_button" class="chart_export_button" title="Export Data">
        <button class="btn btn-default blue-gradient"
                @click="menu_opened = !menu_opened"
                :class="[can_action ? '' : 'disabled']"
                :style="$root.themeButtonStyle"
        >
            <i class="glyphicon glyphicon-download-alt"></i>
        </button>
        <div v-show="can_action && menu_opened" class="download_menu">
            <button type="button"
                    class="btn btn-default download_btn active"
                    @click="sendSignal('copy')"
                    style="background-color: #0a0;">Copy</button>
            
            <button type="button"
                    class="btn btn-default download_btn active"
                    @click="sendSignal('print')"
                    style="background-color: #a00;">Print</button>

            <button type="button"
                    class="btn btn-default download_btn active"
                    @click="sendSignal('png')"
                    style="background-color: #0aa;">PNG</button>

            <button type="button"
                    class="btn btn-default download_btn active"
                    @click="sendSignal('pdf')"
                    style="background-color: #aa0;">PDF</button>

            <button type="button"
                    class="btn btn-default download_btn active"
                    @click="sendSignal('csv')"
                    style="background-color: #00a;">CSV</button>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    export default {
        name: "ChartExportButton",
        mixins: [
        ],
        data: function () {
            return {
                menu_opened: false
            }
        },
        props: {
            chart_uuid: String,
            export_prefix: String,
            can_action: Boolean,
        },
        methods: {
            sendSignal(key) {
                eventBus.$emit('chart-export-button-click', this.chart_uuid, key, this.export_prefix);
            },
            hideMenu(e) {
                let container = $(this.$refs.export_button);
                if (container.has(e.target).length === 0){
                    this.menu_opened = false;
                }
            },
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .chart_export_button {
        cursor: pointer;
        width: 35px;
        height: 30px;
        padding: 0;
        background-color: transparent;
        position: relative;
        outline: none;
        display: inline-block;
        margin-right: 15px;

        button {
            width: 100%;
            height: 100%;
            padding: 3px 1px 0 0;
        }

        .download_menu {
            position: absolute;
            top: 100%;
            right: 100%;
            white-space: nowrap;
            z-index: 500;
            padding: 5px;
            background-color: #FFF;
            border: 1px solid #CCC;

            .download_btn {
                border-radius: 50%;
                width: 40px;
                height: 40px;
                opacity: 0.1;
                color: #FFF;
                font-weight: bold;
                font-size: 0.8em;
                padding: 1px 0 0 0;
            }
            .active {
                opacity: 0.4;

                &:hover {
                    opacity: 0.9;
                }
            }
        }
    }
</style>