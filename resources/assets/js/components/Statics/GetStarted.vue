<template>
    <div class="container get-started-block">
        <div class="row full-height">
            <div class="col-xs-3 full-height">
                <div class="started-elem full-height">
                    <div class="elem__header">Introduction</div>
                    <div class="elem__content">
                        <pages-tree :tree="pages['introduction'] || {}"
                                    :type="'introduction'"
                                    @selected-page="loadPage"
                        ></pages-tree>
                    </div>
                </div>
            </div>
            <div class="col-xs-3 full-height">
                <div class="started-elem full-height">
                    <div class="elem__header">Tutorials</div>
                    <div class="elem__content">
                        <pages-tree :tree="pages['tutorials'] || {}"
                                    :type="'tutorials'"
                                    @selected-page="loadPage"
                        ></pages-tree>
                    </div>
                </div>
            </div>
            <div class="col-xs-3 full-height">
                <div class="started-elem full-height">
                    <div class="elem__header">Templates</div>
                    <div class="elem__content">
                        <pages-tree :tree="pages['templates'] || {}"
                                    :type="'templates'"
                                    @selected-page="loadPage"
                        ></pages-tree>
                    </div>
                </div>
            </div>
            <div class="col-xs-3 full-height">
                <div class="started-elem full-height">
                    <div class="elem__header">Applications</div>
                    <div class="elem__content">
                        <pages-tree :tree="pages['applications'] || {}"
                                    :type="'applications'"
                                    @selected-page="loadPage"
                        ></pages-tree>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PagesTree from "./PagesTree";

    export default {
        name: 'GetStarted',
        components: {
            PagesTree
        },
        data() {
            return {
            }
        },
        props: {
            pages: Object,
        },
        methods: {
            //select page
            loadPage(pageObject, href) {
                window.location.href = href;
            }
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .get-started-block {
        position: relative;
        top: 5%;
        height: 90%;
        color: #222;
        width: 95%;

        .started-elem {
            border: 1px solid #CCC;
            background-color: #EEE;
            overflow: auto;

            .elem__header {
                color: #EEE;
                padding: 5px 10px;
                background-color: #575c62;
                font-weight: bold;
                font-size: 1.2em;
                margin-bottom: 12px;
            }
            .elem__content {
                height: calc(100% - 50px);
            }
        }
    }
    .row {
        margin: 0 -7px;

        .col-xs-3 {
            padding-left: 7px;
            padding-right: 7px;
        }
    }
</style>