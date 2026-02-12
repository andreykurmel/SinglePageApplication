<template>
    <div class="statics_container container-fluid">
        <div class="row full-height">
            <div class="statics_left full-height flex flex--col" ref="static_menu" v-show="isLeftMenu">

                <template v-for="(links, type) in pages">
                    <a @click.prevent="showSection(type)" class="pages-section section__header" :ref="'btn_section_'+type">
                        <type-header :type="type"></type-header>
                        <span class="state-shower">{{ type === currentType ? '-' : '+' }}</span>
                    </a>
                    <div :style="{height: (type === currentType ? 'auto' : 0)}"
                         :class="[type === currentType ? 'flex__elem-remain' : '']"
                         class="section__content"
                    >
                        <pages-tree :tree="links"
                                    :type="type"
                                    :page_id="currentPage.id"
                                    :with_search="true"
                                    @selected-page="loadPage"
                        ></pages-tree>
                    </div>
                </template>

            </div>
            <div class="full-height" :class="[isLeftMenu ? 'statics_right' : 'statics_right_full']">
                <div class="pages-section full-height">
                    <!-- HEADER -->
                    <div class="section__header section__header--selected">
                        <a class="menu-toggler" @click.prevent="showTree()">
                            <span class="glyphicon" :class="[ isLeftMenu ? 'glyphicon-triangle-left': 'glyphicon-triangle-right']"></span>
                        </a>

                        {{ currentPage.name }}

                        <template v-if="$root.user.is_admin || $root.user.role_id == 3">
                            <button v-if="!editing_content" class="btn btn-warning btn-sm" @click="showEditElem()">
                                <i class="glyphicon glyphicon-edit"></i>
                            </button>
                            <template v-else="">
                                <button class="btn btn-primary btn-sm blue-gradient" @click="saveElem()">
                                    <i class="glyphicon glyphicon-ok"></i>
                                </button>
                                <button class="btn btn-default btn-sm" @click="editing_content = false">
                                    <i class="glyphicon glyphicon-remove"></i>
                                </button>
                            </template>
                        </template>
                        <button v-if="currentPage.embed_view && currentPage.embed_view_active && !editing_content"
                                class="btn btn-success btn-sm pull-right"
                                @click="saveCopy()"
                        >Save a Copy</button>
                    </div>
                    <!-- CONTENT -->
                    <div class="section__content section__content--selected" ref="cont_wrapper" :style="{height: 'calc(100% - '+btnOuterHeight+'px)'}">
                        <template v-if="!editing_content">
                            <div v-html="currentPage.content" class="full-frame"></div>
                            <div v-if="currentPage.embed_view" class="full-height" v-html="currentPage.embed_view"></div>
                        </template>
                        <template v-else="">
                            <vue-ckeditor class="content_textarea"
                                          :config="ck_config"
                                          v-model="clone_content"
                                          @fileUploadRequest="onFileUploadRequest($event)"
                            ></vue-ckeditor>
                            <div class="flex ">
                                <div class="flex flex--center">
                                    <label>Embed View Code: </label>
                                </div>
                                <div class="flex__elem-remain">
                                    <input class="form-control" v-model="clone_embed"/>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    import VueCkeditor from 'vue-ckeditor2';

    import PagesTree from "./PagesTree";
    import TypeHeader from "./TypeHeader";

    export default {
        name: 'StaticPages',
        components: {
            TypeHeader,
            PagesTree,
            VueCkeditor,
        },
        data() {
            return {
                ck_config: {
                    allowedContent: true,
                    height: '100%',

                    filebrowserBrowseUrl: '/ckeditor/file-browse',
                    filebrowserUploadUrl: '/ckeditor/file-upload'
                },

                currentType: null,
                currentPage: {},
                redraw: 0,
                btnOuterHeight: 0,
                editing_content: false,
                clone_content: '',
                clone_embed: '',
                isLeftMenu: true,
            }
        },
        props: {
            pages: Object,
            selected_type: String,
            selected_page: Object,
        },
        methods: {
            onFileUploadRequest(evt) {
                let xhr = evt.data.fileLoader.xhr;
                xhr.setRequestHeader('X-CSRF-Token', $('meta[name="csrf-token"]').attr('content'));
            },
            showTree() {
                this.isLeftMenu = !this.isLeftMenu;
            },

            showSection(type) {
                let staticMenuHeight = $(this.$refs.static_menu).height();
                this.btnOuterHeight = $(this.$refs['btn_section_'+type]).outerHeight() + 2;
                this.currentType = type !== this.currentType ? type : null;

                this.ck_config.height = $(this.$refs.cont_wrapper).outerHeight();
            },
            showEditElem() {
                this.clone_content = this.currentPage.content;
                this.clone_embed = this.currentPage.embed_view;
                this.editing_content = true;
            },

            //edit static pages
            saveElem(no_cloned) {
                if (!no_cloned) {
                    this.currentPage.content = this.clone_content;
                    this.currentPage.embed_view = this.clone_embed;
                }

                let row_id = this.currentPage.id;
                let fields = _.cloneDeep(this.currentPage);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/static-page', {
                    page_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    this.editing_content = false;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //copy content
            saveCopy() {
                if (this.$root.user.id) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/table/copy-embed', {
                        embed_code: this.currentPage.embed_view
                    }).then(({ data }) => {
                        if (data.error) {
                            Swal(data.msg);
                        }
                        if (data.href) {
                            window.location.href = data.href;
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    Swal('Info', 'Please LogIn to save a copy!');
                }
            },

            //select page
            loadPage(pageObject, href) {
                this.currentPage = pageObject;
                this.currentType = this.currentPage.type;
                window.history.pushState(pageObject.name, pageObject.name, href);
            },
            globalKeyHandler(e) {
                let cmdOrCtrl = e.metaKey || e.ctrlKey;
                if (cmdOrCtrl) {
                    if (e.keyCode === 37) {//ctrl + left arrow
                        this.showTree();
                    }
                    if (e.keyCode === 38) {//ctrl + up arrow
                        this.$root.toggleTopMenu();
                    }
                }
                if (e.shiftKey) {
                    if (e.code === 'KeyO') {//ctrl + 'O'
                        this.showTree();
                        this.$root.toggleTopMenu();
                    }
                }
            }
        },
        mounted() {
            if (this.selected_type) {
                this.currentPage = this.selected_page;
                this.showSection(this.selected_type);
            }

            setTimeout(() => {
                let el = document.getElementById(location.hash.substr(1));
                if (!!window.chrome && el) {
                    el.scrollIntoView();
                }
            },100);

            eventBus.$on('global-keydown', this.globalKeyHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.globalKeyHandler);
        }
    }
</script>

<style lang="scss" scoped>
    .statics_container {
        padding: 5px;
        height: 100%;

        .pages-section {
            border: 1px solid #CCC;
            background-color: #EEE;
            transition: all 0.5s ease-out;
        }

        .section__header {
            display: flex;
            justify-content: space-between;
            cursor: pointer;
            text-decoration: none;
            color: #EEE;
            padding: 5px 10px;
            background-color: #575c62;
            font-weight: bold;
            font-size: 1.2em;

            &:hover {
                color: #FFF;
            }
        }
        .section__header--selected {
            cursor: default;
            background-color: #005fa4;
            color: #FFF;

            &:hover {
                color: #FFF;
            }

            .menu-toggler {
                position: relative;
                top: 2px;
                color: #FFF;
                cursor: pointer;
            }

            .form-ctr-fixed {
                display: inline-block;
                width: 100px;
                height: 26px;
                padding: 3px;
            }

            .node-span {
                margin: 0 15px;
            }
        }

        .section__content {
            overflow: auto;
            transition: all 0.5s ease-out;

            ul {
                margin: 12px 0;
                padding-right: 10px;
            }
            label {
                margin-bottom: 0;
            }
            .content_textarea {
                overflow: auto;
                margin-bottom: 10px;
                width: 100%;
                height: calc(100% - 50px);
            }
            .selected_a {
                color: #222;
                cursor: default;
                text-decoration: none;
            }
        }
        .section__content--selected {
            padding: 5px 10px;
        }

        .btn-sm {
            padding: 3px 7px;
        }

        .row {
            margin: 0 -3px;

            .statics_left, .statics_right, .statics_right_full {
                position: relative;
                min-height: 1px;
                padding-left: 3px;
                padding-right: 3px;
                float: left;
            }

            .statics_left {
                width: 22.5%;
            }

            .statics_right {
                width: 77.5%;
            }

            .statics_right_full {
                width: 100%;
            }
        }

        .switch_t--inline {
            display: inline-block;
            top: 3px;
        }
    }
</style>
<style>
    iframe {
        height: calc(100% - 1px);
        overflow: auto;
        margin: auto;
        display: block;
    }
</style>