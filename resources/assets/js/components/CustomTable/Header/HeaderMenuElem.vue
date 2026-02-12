<template>
    <div ref="header_menu_button" class="header_menu_button" title="Header Menu">
        <i :class="[specIcon || 'glyphicon glyphicon-triangle-bottom']"
           :style="{rotate: rotate ? '-45deg' : ''}"
           @click="showMenu()"
        ></i>
        <div v-show="menu_opened" ref="header_opened_menu" class="header_menu_actions" :style="{right: r_pos}">
            <a v-if="!specIcon && !only_sorting" class="action_elem" @click="showHeaderSettings()">
                <i class="fa fa-cog"></i> Settings
            </a>
            <a class="action_elem" @click="$emit('field-sort-asc');menu_opened=false;">
                <i class="glyphicon glyphicon-sort-by-attributes"></i> Sort A -> Z
            </a>
            <a class="action_elem" @click="$emit('field-sort-desc');menu_opened=false;">
                <i class="glyphicon glyphicon-sort-by-attributes-alt"></i> Sort Z -> A
            </a>
            <a v-if="!specIcon && isOwner && !only_sorting" class="action_elem" @click="showLinks()">
                <i class="glyphicon glyphicon-link"></i> Add Links
            </a>
            <div v-if="!specIcon && !only_sorting"
                 class="action_elem"
                 @mouseenter="align_menu = true"
                 @mouseleave="align_menu = false"
            >
                <i class="fas" :class="[iconAlign]"></i> Alignment
                <div v-show="align_menu" class="alignm-menu">
                    <div @click="setAlign('left')" class="alignm-item">
                        <i class="fas fa-align-left"></i> Left
                    </div>
                    <div @click="setAlign('center')" class="alignm-item">
                        <i class="fas fa-align-center"></i> Center
                    </div>
                    <div @click="setAlign('right')" class="alignm-item">
                        <i class="fas fa-align-right"></i> Right
                    </div>
                </div>
            </div>
            <a class="action_elem" @click="hideColumn">
                <i class="glyphicon glyphicon-eye-close"></i> Hide
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
                align_menu: false,
                menu_opened: false,
                r_pos: ''
            }
        },
        props: {
            specIcon: String,
            tableMeta: Object,
            tableHeader: Object,
            isOwner: Boolean,
            rotate: Boolean,
            only_sorting: Boolean,
        },
        computed: {
            iconAlign() {
                switch (this.tableHeader.col_align) {
                    case 'left' : return 'fa-align-left';
                    case 'right' : return 'fa-align-right';
                    default : return 'fa-align-center';
                }
            },
        },
        methods: {
            hideColumn() {
                eventBus.$emit('hide-table-column', this.tableHeader);
                this.menu_opened = false;
            },
            setAlign(val) {
                this.tableHeader.col_align = val;
                this.tableHeader._changed_field = 'col_align';
                this.$root.updateSettingsColumn(this.tableMeta, this.tableHeader);

                this.align_menu = false;
            },
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
                eventBus.$emit('show-vertical-display-links', this.tableHeader);
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
            padding: 5px 0;
            background-color: #111;
            right: 0;
            
            .action_elem {
                color: #FFF;
                text-align: left;
                display: block;
                white-space: nowrap;
                position: relative;
                padding: 0 5px;
            }

            .switch_t--inline {
                display: inline-block;
                top: 3px;
                margin-left: 5px;
            }

            .alignm-menu {
                position: absolute;
                top: calc(100% - 18px);
                left: 100%;
                border: 1px solid #CCC;
                padding: 5px;
                background-color: black;
                color: white;
                white-space: nowrap;

                .alignm-item {
                    cursor: pointer;

                    &:hover {
                        background-color: #707070;
                    }
                }
            }
        }
    }
</style>