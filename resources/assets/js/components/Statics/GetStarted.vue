<template>
    <div class="container get-started-block flex flex--col">
        <div class="row">
            <div class="col-xs-6" style="margin: 10px 25%;">
                <div class="search-in-tree flex">
                    <input type="text" class="form-control" @input="withClear" @keydown="enterSearch" v-model="globalSearch">
                    &nbsp;
                    <button class="btn btn-default" @click="globSearchRun" style="padding: 8px 12px 0 12px;">
                        <i class="fa fa-search" style="font-size: 1.7em; line-height: 0;"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row flex__elem-remain" style="height: calc(100% - 70px);">
            <div v-for="(group, type) in pages" class="col-xs-3 full-height">
                <div class="started-elem full-height">
                    <div class="elem__header">
                        <type-header :type="type"></type-header>
                    </div>
                    <div class="elem__content">
                        <pages-tree :tree="group"
                                    :type="type"
                                    @selected-page="loadPage"
                        ></pages-tree>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="padding: 5px 10px; font-size: 13px; font-style: italic;">
            <span>TablDA is a fully developed and functional solution.
                While we are currently finalizing the documentation and preparing tutorials, our team is always available
                to provide support and answer any questions you may have. Don't hesitate to Contact Us and let us help you
                take your work to the next level. Get ready to be pleasantly surprised and amazed by TablDA!</span>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import PagesTree from "./PagesTree";
    import TypeHeader from "./TypeHeader";

    export default {
        name: 'GetStarted',
        components: {
            TypeHeader,
            PagesTree
        },
        data() {
            return {
                globalSearch: '',
                withClearing: false,
            }
        },
        props: {
            pages: Object,
        },
        methods: {
            //select page
            loadPage(pageObject, href) {
                window.open(href, '_blank');
            },

            withClear() {
                this.withClearing = true;
            },
            enterSearch(e) {
                if (e.keyCode == 13) {
                    this.globSearchRun();
                }
            },
            globSearchRun() {
                eventBus.$emit('static-page-global-search', this.globalSearch, this.withClearing);
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .get-started-block {
        position: relative;
        height: 100%;
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