<template>
    <div>
        <div class="popup-wrapper" @click.self="$emit('popup-close')"></div>
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">
                            <span v-if="!folderMeta">Folder loading...</span>
                            <span v-else="">Copy folder and children nodes (sub-folders and tables) to others.</span>
                        </div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="$emit('popup-close')"></span>
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">

                        <div class="flex flex--col">
                            <div class=" sel-user-block elem-group">
                                <div class="flex ">
                                    <div class="" style="padding-bottom: 4px;">
                                        <label>Copy to User:</label>
                                    </div>
                                    <div class="flex__elem-remain">
                                        <div class="flex__elem__inner select-height">
                                            <select ref="search_user"></select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex__elem-remain">
                                <div class="flex__elem__inner">
                                    <div class="flex  full-height">
                                        <div class="left-col">
                                            <div class="flex flex--col elem-group">
                                                <div class="">
                                                    <div class="section-text">
                                                        <span v-if="!folderMeta">Folder loading...</span>
                                                        <span v-else="">Nodes under '{{ folderMeta.name }}'</span>
                                                    </div>
                                                </div>
                                                <div class="flex__elem-remain">
                                                    <div class="flex__elem__inner">
                                                        <div class="popup-overflow">
                                                            <div ref="jstree"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex__elem-remain">
                                            <div class="flex__elem__inner">
                                                <div class="flex flex--col elem-group">
                                                    <div class="">
                                                        <div class="section-text">
                                                            <span v-if="!selectedTable">Click a table node to see details.</span>
                                                            <span v-else="">Copy settings for '{{ selectedTable.text }}'</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex__elem-remain">
                                                        <div class="flex__elem__inner">
                                                            <div class="popup-overflow">
                                                                <copy-table-settings-block
                                                                        v-if="selectedSettings"
                                                                        :selected-settings="selectedSettings"
                                                                        @send-settings="getSettingsWith"
                                                                ></copy-table-settings-block>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="">
                                <button class="btn btn-success pull-right" @click="copyFolder()">Send</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';

    import CopyTableSettingsBlock from "../CommonBlocks/CopyTableSettingsBlock";

    export default {
        name: "CopyFolderToOthersPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
            CopyTableSettingsBlock,
        },
        data: function () {
            return {
                selectedTable: null,
                selectedSettings: null,
                settings: {},
                //PopupAnimationMixin
                getPopupWidth: 800,
                idx: 0,
            }
        },
        props:{
            folderMeta: Object,
        },
        methods: {
            getContextMenu() {
                let plugins = ['checkbox'];
                let context_menu = {
                    'core' : {
                        'data' : this.folderMeta._sub_tree
                    }
                };

                context_menu.plugins = plugins;
                return context_menu;
            },
            createTreeMenu() {
                $(this.$refs.jstree).jstree( this.getContextMenu() )
                    .on('select_node.jstree', (e, data) => {
                        this.jstree_select_node(e, data);
                    })
                    .on('deselect_node.jstree', (e, data) => {
                        this.jstree_deselect_node(e, data);
                    });
            },
            jstree_select_node(e, data) {
                //only for left click
                let evt =  window.event || e;
                let button = evt.which || evt.button;
                if( button !== 1 && ( typeof button !== 'undefined')) return false;
                //
                let type = data.node.li_attr['data-type'];

                if (type === 'table') {
                    this.selectedTable = data.node;
                    this.selectedSettings = null;
                    //settings components should be remounted every time
                    this.$nextTick(() => {
                        this.selectedSettings = data.node.li_attr['data-copy-settings'];
                    });
                } else {
                    this.selectedTable = null;
                    this.selectedSettings = null;
                }
            },
            jstree_deselect_node(e, data) {
                //only for left click
                let evt =  window.event || e;
                let button = evt.which || evt.button;
                if( button !== 1 && ( typeof button !== 'undefined')) return false;
                //
                if (data.event.target.nodeName.toUpperCase() === 'A') {
                    $(this.$refs.jstree).jstree().select_node(data.node);
                }
            },

            //copy functions
            copyFolder() {
                if (! $(this.$refs.search_user).val()) {
                    Swal('Info', '"Copy to User" is empty!');
                    return;
                }

                let copy_arr = $(this.$refs.jstree).jstree("get_json", '#');

                $.LoadingOverlay('show');
                axios.post('/ajax/folder/copy', {
                    id: this.folderMeta.id,
                    new_user_id: $(this.$refs.search_user).val(),
                    folder_json: copy_arr.shift()
                }).then(({ data }) => {
                    if (this.$root.user.id == $(this.$refs.search_user).val()) {
                        this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)
                    }
                    this.$emit('popup-close');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            
            getSettingsWith(sett) {
                this.selectedTable.li_attr['data-copy-settings'] = sett;
            },
        },
        mounted() {
            $(this.$refs.search_user).select2({
                ajax: {
                    url: '/ajax/user/search',
                    dataType: 'json',
                    delay: 250
                },
                minimumInputLength: {val:3},
                width: '100%',
                height: '100%'
            });
            $(this.$refs.search_user).next().css('height', '26px');

            this.createTreeMenu();

            this.$root.tablesZidxIncrease();
            this.zIdx = this.$root.tablesZidx;
            this.runAnimation({anim_transform:'none'});
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomEditPopUp";

    .popup {
        button {
            margin-top: 10px;
        }
        .sel-user-block {
            padding: 5px;

            label {
                margin-top: 3px;
                margin-right: 5px;
            }
        }
        .elem-group {
            border: 2px #BBB solid;
        }
        .section-text {
            padding: 5px 10px;
            font-size: 16px;
            font-weight: bold;
            background-color: #CCC;
        }
        .left-col {
            flex-basis: 220px;
        }
    }
</style>