<script>
    import {eventBus} from './../../app';

    import Tables from './Object/Table/Tables';
    import Folders from './Object/Folder/Folders';
    import LeftMenu from './LeftMenu/LeftMenu';
    import RightMenu from './RightMenu/RightMenu';
    import TosChecker from './../TosChecker';

    export default {
        components: {
            Tables,
            Folders,
            LeftMenu,
            RightMenu,
            TosChecker,
        },
        data() {
            return {
                filters: null,
                isPagination: true,
                object_id:  this.init_object_id,
                object_type:  this.init_object_type,
                auto_logout: null,
            }
        },
        props: {
            init_object_id: Number|null,
            init_object_type: String|null,
            user: Object,
            settingsMeta: Object,
            panels_preset: Object,
        },
        methods: {
            updateObjectId(type, value){
                this.object_id = value;
                this.object_type = type;
            },
            globalKeyHandler(e) {
                if (['INPUT', 'TEXTAREA'].indexOf(e.target.nodeName) === -1) {//target not in Input

                    if (e.ctrlKey) {
                        if (e.keyCode === 37) {//ctrl + left arrow
                            this.$root.toggleLeftMenu();
                        }
                        if (e.keyCode === 38) {//ctrl + up arrow
                            this.$root.toggleTopMenu();
                        }
                        if (e.keyCode === 39) {//ctrl + right arrow
                            this.$root.toggleRightMenu();
                        }
                        if ([40,34].indexOf(e.keyCode) > -1) {//ctrl + down arrow or pgdn
                            this.isPagination = !this.isPagination;
                        }
                    }
                    if (e.shiftKey) {
                        if (e.keyCode === 79) {//shift + 'O'
                            this.$root.toggleLeftMenu();
                            this.$root.toggleTopMenu();
                            this.$root.toggleRightMenu();
                            this.isPagination = !this.isPagination;
                        }
                    }

                }
            }
        },
        created: function() {
            eventBus.$on('global-keydown', this.globalKeyHandler);
        },
        mounted() {
            if (this.panels_preset.top !== undefined) {
                (this.panels_preset.top ? $('#main_navbar').show() : $('#main_navbar').hide());
            }
            if (this.panels_preset.right !== undefined) {
                this.$root.isRightMenu = this.panels_preset.right;
            }
            if (this.panels_preset.left !== undefined) {
                this.$root.isLeftMenu = this.panels_preset.left;
            }

            let pnls = this.$root.user.view_all;
            if (pnls) {
                if (pnls.side_top) {
                    (pnls.side_top == 'show' ? $('#main_navbar').show() : $('#main_navbar').hide());
                }
                if (pnls.side_left_menu || pnls.side_left_filter) {
                    this.$root.isLeftMenu = pnls.side_left_menu == 'show' || pnls.side_left_filter == 'show';
                }
                if (pnls.side_right) {
                    this.$root.isRightMenu = pnls.side_right == 'show';
                }
            }
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.globalKeyHandler);
        }
    }
</script>