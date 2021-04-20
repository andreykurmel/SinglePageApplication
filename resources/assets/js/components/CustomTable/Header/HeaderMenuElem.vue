<template>
    <div ref="header_menu_button" class="header_menu_button" title="Header Menu">
        <i :class="[specIcon || 'glyphicon glyphicon-triangle-bottom']" @click="showMenu()"></i>
        <div v-show="menu_opened" ref="header_opened_menu" class="header_menu_actions" :style="{right: r_pos}">
            <a v-if="!specIcon" class="action_elem" @click="showHeaderSettings()">
                <i class="fa fa-cog"></i> Settings
            </a>
            <a class="action_elem" @click="$emit('field-sort-asc');menu_opened=false;">
                <i class="glyphicon glyphicon-sort-by-attributes"></i> Sort A -> Z
            </a>
            <a class="action_elem" @click="$emit('field-sort-desc');menu_opened=false;">
                <i class="glyphicon glyphicon-sort-by-attributes-alt"></i> Sort Z -> A
            </a>
            <a v-if="!specIcon && isOwner" class="action_elem" @click="showLinks()">
                <i class="glyphicon glyphicon-link"></i> Add Links
            </a>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../app';

    export default {
        name: "HeaderMenuElem",
        data: function () {
            return {
                menu_opened: false,
                r_pos: ''
            }
        },
        props: {
            specIcon: String,
            tableHeader: Object,
            selectedCol: Number,
            isOwner: Boolean
        },
        methods: {
            showMenu() {
                this.menu_opened = !this.menu_opened;
                let bounds_btn = this.$refs.header_menu_button.getBoundingClientRect();
                let bounds_body = document.body.getBoundingClientRect();
                let should_right = bounds_body.width
                    - bounds_btn.x
                    - 165
                    - (this.$root.isRightMenu ? 250 : 0);
                this.r_pos = should_right < 0 ? '0' : '';
            },
            showLinks() {
                eventBus.$emit('show-vertical-display-links', this.selectedCol);
                this.menu_opened = false;
            },
            showHeaderSettings() {
                this.$emit('show-header-settings', this.tableHeader);
                eventBus.$emit('show-header-settings', this.tableHeader);
                this.menu_opened = false;
            },
            hideMenu(e) {
                let container = $(this.$refs.header_menu_button);
                if (container.has(e.target).length === 0 && this.menu_opened){
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
    .header_menu_button {
        cursor: pointer;
        position: relative;
        display: inline-block;

        .header_menu_actions {
            position: absolute;
            width: auto;
            z-index: 500;
            padding: 5px;
            background-color: #111;
            
            .action_elem {
                color: #FFF;
                text-align: left;
                display: block;
                white-space: nowrap;
            }

            .switch_t--inline {
                display: inline-block;
                top: 3px;
                margin-left: 5px;
            }
        }
    }
</style>