<template>
    <div class="right-menu" :style="rightMenuStyle">
        <header-resizer
            :table-header="right_menu_sizes"
            :reversed="true"
            :style="{zIndex: 100}"
            @resize-finished="saveSizes"
        ></header-resizer>

        <div class="menu-header">
            <button class="btn btn-default btn-he"
                    :class="{active : rightType !== 'comm'}"
                    :style="textSysStyle"
                    @click="rightType = 'comm'"
            >Comm.</button>
            <button v-if="hasAI"
                    class="btn btn-default btn-he"
                    :class="{active : rightType !== 'ai_assist'}"
                    :style="textSysStyle"
                    @click="rightType = 'ai_assist'"
            >AI Assist</button>

            <info-sign-link v-if="$root.settingsMeta.is_loaded && rightType === 'comm'"
                            :app_sett_key="'help_link_communication'"
                            :hgt="24"
                            class="flo-right"
                            :txt="'for Communications'"
            ></info-sign-link>
            <info-sign-link v-if="$root.settingsMeta.is_loaded && rightType === 'ai_assist'"
                            :app_sett_key="'help_link_ai_assistance'"
                            :hgt="24"
                            class="flo-right"
                            :txt="'for AI Assist'"
            ></info-sign-link>
        </div>
        <div class="menu-body" ref="menu_body" :style="textSysStyle">
            <template v-if="table_id && $root.tableMeta && rightType === 'comm'">
                <div :style="{height: calcSubHeight('about')}">
                    <a @click.prevent="showSub('about')" class="btn-sub" :style="textSysStyle">
                        About
                        <span class="state-shower">{{ 'about' === currentSub ? '-' : '+' }}</span>
                    </a>
                    <div v-show="'about' === currentSub" :style="{height: 'calc(100% - '+this.subBtnHeight+'px)'}" class="sub-content">
                        <div class="about-notes">
                            <right-menu-cell
                                :can-edit="$root.tableMeta._is_owner"
                                :note_type="'notes'"
                                :table_id="$root.tableMeta.id"
                            ></right-menu-cell>
                        </div>
                        <div class="about-files">
                            <div class="about-files-body">
                                <div v-for="(file, index) in $root.tableMeta._attached_files">
                                    <a target="_blank" :href="$root.fileUrl(file)">{{ file.filename }}</a>
                                    <a href="#" @click.prevent="deleteFile(index)">&times;</a>
                                </div>
                            </div>
                            <button v-if="$root.user.id === $root.tableMeta.user_id"
                                    class="btn btn-sm btn-primary about-upload"
                                    :style="$root.themeButtonStyle"
                                    @click="uploadForm = true"
                            >
                                <i class="fa fa-upload"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="$root.user.id" :style="{height: calcSubHeight('notes')}">
                    <a @click.prevent="showSub('notes')" class="btn-sub" :style="textSysStyle">
                        My Notes
                        <span class="state-shower">{{ 'notes' === currentSub ? '-' : '+' }}</span>
                    </a>
                    <div v-show="'notes' === currentSub" :style="{height: 'calc(100% - '+this.subBtnHeight+'px)'}" class="sub-content">
                        <right-menu-cell
                            :can-edit="!!$root.user.id"
                            :note_type="'user_notes'"
                            :table_id="$root.tableMeta.id"
                        ></right-menu-cell>
                    </div>
                </div>
                <div v-if="$root.user.id" :style="{height: calcSubHeight('messages')}">
                    <a @click.prevent="showSub('messages')" class="btn-sub" :style="textSysStyle">
                        Messages
                        <span class="state-shower">{{ 'messages' === currentSub ? '-' : '+' }}</span>
                    </a>
                    <div v-show="'messages' === currentSub" :style="{height: 'calc(100% - '+this.subBtnHeight+'px)'}" class="sub-content">
                        <right-menu-messages
                            :owner="$root.tableMeta._is_owner"
                            :owner_id="$root.tableMeta.user_id"
                            :table_id="$root.tableMeta.id"
                            :table-messages="$root.tableMeta._communications"
                        ></right-menu-messages>
                    </div>
                </div>
            </template>
            <template v-if="table_id && $root.tableMeta && rightType === 'ai_assist'">
                <div class="full-frame flex flex--col"
                     v-if="selAi"
                     :style="aiModuleStyle()"
                >
                    <ai-module :selected-ai="selAi" :request_params="$root.request_params" @remove-msg="removeMessage"></ai-module>
                </div>
                <div v-else>
                    <label>You can choose an AI at "AI/List/Right Side Panel"</label>
                </div>
            </template>
        </div>

        <!--Upload form-->
        <div v-show="uploadForm" class="modal-wrapper">
            <div class="modal" @click.self="uploadForm = false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <span class="pull-right close-modal" @click="uploadForm = false">&times;</span>
                            <h4 class="modal-title">Upload Attachments</h4>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <file-uploader-block
                                    v-if="table_id && $root.tableMeta"
                                    class="form-group upload-group"
                                    :header-index="0"
                                    :table_id="table_id"
                                    :field_id="0"
                                    :row_id="0"
                                    @uploaded-file="insertedFile"
                                ></file-uploader-block>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import CellStyleMixin from "../../_Mixins/CellStyleMixin.vue";
    import ModuleViewMixin from "../../MainApp/Object/Table/AiAddon/ModuleViewMixin.vue";

    import RightMenuCell from './RightMenuCell.vue';
    import RightMenuMessages from './RightMenuMessages.vue';
    import FileUploaderBlock from '../../CommonBlocks/FileUploaderBlock.vue';
    import InfoSignLink from "../../CustomTable/Specials/InfoSignLink.vue";
    import HeaderResizer from "../../CustomTable/Header/HeaderResizer.vue";
    import AiModule from "../Object/Table/AiAddon/AiModule.vue";

    export default {
        name: "RightMenu",
        components: {
            AiModule,
            HeaderResizer,
            InfoSignLink,
            RightMenuCell,
            RightMenuMessages,
            FileUploaderBlock
        },
        mixins: [
            CellStyleMixin,
            ModuleViewMixin,
        ],
        data: function () {
            return {
                currentSub: readLocalStorage('right_sub') || null,
                numberSubs: this.$root.user.id ? 3 : 1,
                subBtnHeight: 42,
                uploadForm: false,
                uploadStyle: 'file',
                right_menu_sizes:{
                    width: Number(readLocalStorage('local_right_menu_width')) || 250,
                    max_width: 400,
                    min_width: 250,
                },
                rightType: 'comm',
            }
        },
        computed: {
            rightMenuStyle() {
                return {
                    flexShrink: 0,
                    flexGrow: 0,
                    flexBasis: this.right_menu_sizes.width+'px',
                    width: this.right_menu_sizes.width+'px',
                };
            },
            hasAI() {
                return this.$root.settingsMeta.is_loaded
                    && this.$root.tableMeta
                    && this.$root.tableMeta.add_ai
                    && this.$root.AddonAvailableToUser(this.$root.tableMeta, 'ai')
            },
            selAi() {
                return this.hasAI
                    ? _.find(this.$root.tableMeta._table_ais, {is_right_panel: 1})
                    : null;
            },
        },
        watch: {
            "$root.tableMeta.id": { //watch for root to be sure that tableMeta is loaded
                handler(val) {
                    if (this.$root.tableMeta && this.$root.tableMeta._cur_settings) {
                        this.right_menu_sizes.width = Number(this.$root.tableMeta._cur_settings.right_menu_width)
                            || Number(readLocalStorage('local_right_menu_width'))
                            || 250;
                    }
                },
                immediate: true,
            },
        },
        props: {
            table_id: Number,
        },
        methods: {
            saveSizes() {
                this.$root.changeRightMenuWi(this.right_menu_sizes.width, this.$root.tableMeta);
            },
            showSub(field) {
                this.currentSub = field !== this.currentSub ? field : null;
                setLocalStorage('right_sub', this.currentSub);
            },
            calcSubHeight(field) {
                let res = this.subBtnHeight+'px';
                if (this.currentSub === field) {
                    res = 'calc(100% - '+((this.numberSubs-1)*this.subBtnHeight)+'px)';
                }
                return res;
            },

            //work with files
            insertedFile(idx, file) {
                this.$root.tableMeta._attached_files.push(file);
            },
            deleteFile(idx) {
                let file = this.$root.tableMeta._attached_files[idx];
                $.LoadingOverlay('show');
                axios.delete('/ajax/files', {
                    params: {
                        id: file.id,
                        table_id: file.table_id,
                        table_field_id: 0,
                        row_id: 0,
                    }
                }).then(({ data }) => {
                    this.$root.tableMeta._attached_files.splice(idx, 1);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .btn-he {
        height: 36px;
    }
    .right-menu {
        min-width: 250px;
        border: 1px solid #d3e0e9;
        position: relative;

        @media(max-width: 767px) {
            min-width: 150px;
        }

        .menu-header {
            text-align: left;
            padding: 10px 4px 0 5px;
            background-color: #575c62;
            height: 43px;

            label {
                color: #bfbfbf;
                font-size: 1.2em;
                font-weight: bold;
            }

            .flo-right {
                float: right;
                margin-right: 5px;
            }
        }

        .menu-body {
            top: 43px;
            height: calc(100% - 41px);
            width: 100%;
            position: absolute;
            z-index: 10;
            background-color: white;
            padding: 5px;

            .state-shower {
                float: right;
                font-size: 2em;
                line-height: 0.7em;
            }
            .btn-sub {
                display: block;
                cursor: pointer;
                white-space: nowrap;
                padding: 10px 11px;
                color: #555;
                background-size: 100% 100%;
                background: linear-gradient(to top, #efeff4, #d6dadf);
                border: 1px solid #cccccc;
                text-decoration: none;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75), 0 1px 1px rgba(0, 0, 0, 0.15);

                &:not(.active):hover {
                    color: black;
                }
            }

            .sub-content{
                overflow: hidden;
                border: 1px solid #CCC;

                .about-notes {
                    height: 70%;
                    overflow: auto;
                }
                .about-files {
                    height: 30%;
                    position: relative;
                    display: flex;
                    align-items: flex-start;
                    padding: 5px;
                    border-top: 1px solid #CCC;

                    .about-files-body {
                        max-height: 100%;
                        width: 100%;
                        overflow: auto;
                    }
                    .about-upload {
                        position: absolute;
                        bottom: 0;
                        right: 0;
                    }
                }
            }
        }

        .modal-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            z-index: 1500;
            background: rgba(0, 0, 0, 0.45);

            .modal {
                display: block;
                top: 50%;
                transform: translateY(-50%);
                margin: 0 auto;

                .close-modal {
                    font-size: 2.5em;
                    line-height: 0.8em;
                    cursor: pointer;
                }

            }
        }
    }
</style>