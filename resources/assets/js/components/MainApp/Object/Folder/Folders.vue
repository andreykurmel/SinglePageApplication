<template>
    <div class="folders-wrapper full-width">

        <nav class="navbar navbar-default" role="navigation">
            <div class="container-fluid nav-inner">
                <div class="nav flex flex--center flex--automargin pull-left">
                    <div>
                        <a @click.prevent="showTree()">
                            <span class="glyphicon" :class="[ $root.isLeftMenu ? 'glyphicon-triangle-left': 'glyphicon-triangle-right']"></span>
                        </a>
                    </div>
                    <div :class="{active: activeTab === 'basics'}" v-if="folderMeta">
                        <a @click.prevent="activeTab = 'basics'">Basics</a>
                    </div>
                    <div :class="{active: activeTab === 'permissions'}" v-if="folderMeta">
                        <a @click.prevent="activeTab = 'permissions'">Share</a>
                    </div>
                    <div :class="{active: activeTab === 'views'}" v-if="folderMeta">
                        <a @click.prevent="activeTab = 'views'">Views</a>
                    </div>
                    <div :class="{active: activeTab === 'import'}" v-if="folderMeta">
                        <a @click.prevent="activeTab = 'import'">Import</a>
                    </div>
                </div>
                <div class="nav flex flex--center flex--automargin pull-right">
                    <div>
                        <a @click.prevent="showNote()"><span class="glyphicon" :class="[ $root.isRightMenu ? 'glyphicon-triangle-right': 'glyphicon-triangle-left']"></span></a>
                    </div>
                </div>
            </div>
        </nav>
        <div class="tabs-wrapper" v-if="folderMeta">
            <!--BASICS TAB-->
            <div class="basics-tab full-frame" v-show="activeTab === 'basics'">
                <div class="form-group">
                    <label>Name: </label>
                    <input class="form-control input-200" v-model="folderMeta.name" @change="renameFolder()"/>
                </div>
                <div class="form-group">
                    <label>Icon: </label>
                    <template v-if="folderMeta.icon_path">
                        <img :src="$root.fileUrl({url:folderMeta.icon_path})" class="preview-img"/>
                        <button class="btn btn-danger" @click="deleteIcon()">
                            <span class="glyphicon glyphicon-remove"></span>
                        </button>
                    </template>
                    <button class="btn btn-primary" @click="$refs.upload_file.click()">
                        <span class="glyphicon glyphicon-upload"></span>
                    </button>
                    <input class="form-control hidden" type="file" ref="upload_file" @change="uploadIcon()"/>
                </div>
            </div>
            <!--PERMISSIONS TAB-->
            <div class="full-frame" v-if="activeTab === 'permissions' && folderMeta">
                <folder-permissions
                    :folder-meta="folderMeta"
                    :settings-meta="settingsMeta"
                ></folder-permissions>
            </div>
            <!--VIEWS TAB-->
            <div class="full-frame" v-if="activeTab === 'views' && folderMeta">
                <folder-views
                    :folder-meta="folderMeta"
                ></folder-views>
            </div>
            <!--IMPORT TAB-->
            <div class="full-frame" v-if="activeTab === 'import' && folderMeta">
                <folder-import
                    :folder-meta="folderMeta"
                ></folder-import>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../../../app';

    import FolderPermissions from './FolderPermissions';
    import FolderViews from "./FolderViews";
    import FolderImport from "./FolderImport";

    export default {
        name: "Folders",
        components: {
            FolderImport,
            FolderViews,
            FolderPermissions
        },
        data: function () {
            return {
                activeTab: 'basics',
                folderMeta: null,
                oldFolderName: '',
            }
        },
        props: {
            folder_id: Number|null,
            settingsMeta: Object,
        },
        watch: {
            folder_id: function(val) {
                this.activeTab = 'basics';
                if (val) {
                    this.getFolderMeta();
                }
            }
        },
        methods: {
            showNote(){
                this.$root.toggleRightMenu();
            },
            showTree(){
                this.$root.toggleLeftMenu();
            },
            getFolderMeta() {
                $.LoadingOverlay('show');
                axios.get('/ajax/folder', {
                    params: {
                        folder_id: this.folder_id,
                    }
                }).then(({ data }) => {
                    this.folderMeta = data.folder;
                    this.oldFolderName = this.folderMeta.name;
                    if (this.$root.user.id) {
                        $('head title').html(this.$root.app_name+': '+this.folderMeta.name);
                    }

                    eventBus.$emit('change-folder-meta', this.folderMeta);
                    eventBus.$emit('re-highlight-menu-tree', true);

                    console.log('FolderSettings', this.folderMeta, 'size about: ', JSON.stringify(this.folderMeta).length);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            },
            renameFolder() {
                if (this.folderMeta.name) {
                    this.folderMeta.name = this.folderMeta.name.replace(/[^\w\d\.-_ ]/gi, '');
                    $.LoadingOverlay('show');
                    axios.put('/ajax/folder', {
                        folder_id: this.folderMeta.id,
                        fields: {
                            parent_id: this.folderMeta.parent_id,
                            name: this.folderMeta.name
                        }
                    }).then(({ data }) => {
                        this.$root.user.memutree_hash = null;//reload menutree in 10 sec (MainAppWrapper)

                        let path = window.location.href.replace(this.oldFolderName, this.folderMeta.name);
                        window.history.pushState(this.folderMeta.name, this.folderMeta.name, path);
                        this.oldFolderName = this.folderMeta.name;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
            uploadIcon() {
                let data = new FormData();
                let file = this.$refs.upload_file.files[0];
                data.append('folder_id', this.folderMeta.id);
                data.append('file', file);

                if (file) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/folder/icon', data, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(({ data }) => {
                        this.folderMeta.icon_path = data;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    Swal('No file', '', 'info');
                }
            },
            deleteIcon(col, idx) {
                Swal({
                    title: 'Delete Icon',
                    text: 'Are you sure?',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        $.LoadingOverlay('show');
                        axios.delete('/ajax/folder/icon', {
                            params: {
                                folder_id: this.folderMeta.id
                            }
                        }).then(({ data }) => {
                            this.folderMeta.icon_path = '';
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    }
                });
            },
        },
        mounted() {
            if (this.folder_id) {
                this.getFolderMeta();
            }
        }
    }
</script>

<style scoped lang="scss">
    .folders-wrapper {
        overflow: auto;

        .tabs-wrapper {
            height: calc(100% - 45px);
            background-color: #005fa4;
            width: 100%;
        }

        @media (max-width: 767px) {
            .tabs-wrapper {
                height: calc(100% - 52px);
            }
        }

        @media (min-width: 768px) {
            .nav > div > a {
                padding: 5px;
                height: 40px;
                cursor: pointer;
                display: flex;
                align-items: center;
            }
            .navbar-collapse {
                padding-right: 0;
                padding-left: 0;
            }
        }

        .navbar-left {
            margin-left: -15px;
        }

        .navbar {
            border-radius: 0;
            min-height: fit-content;

            .glyphicon {
                top: 0;
                padding-right: 2px;
            }

            .favorite-btn {
                font-size: 2em;
                top: 1px;
            }
        }

        .tabs-wrapper .basics-tab {
            background-color: #FFF;
            border: 1px solid #CCC;
            padding: 20px;
        }

        .input-200 {
            width: 200px;
            display: inline-block;
        }

        .preview-img {
            max-width: 150px;
            max-height: 100px;
        }
    }
</style>

<style lang="scss" scoped>
    @import "../Table/SettingsModule/TabSettings";
    @import "../Table/SettingsModule/TabSettingsPermissions";
</style>