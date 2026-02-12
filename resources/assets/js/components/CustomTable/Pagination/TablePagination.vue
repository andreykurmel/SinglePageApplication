<template>
    <div ref="pager">
        <div class="pagi-pos pagi-pos--left flex" :style="{bottom: horScroll && isDesc ? '20px' : 0}">
            <div class="custom-pagination custom-padd blue-gradient" :style="themeButtonStyleNo">
                <span v-if="!compact">
                    {{ (page-1)*perPage + 1 }}
                    to {{ perPage ? (page*perPage > rowsCount ? rowsCount : page*perPage) : rowsCount }}
                    of {{rowsCount}}</span>
                <span v-else="">{{rowsCount}} entries</span>
                <span v-if="hasFiltered">| {{ tableMeta._global_rows_count - rowsCount }} filtered out.</span>
            </div>
        </div>
        <div class="pagi-pos pagi-pos--right flex flex--automargin" :style="{bottom: horScroll && isDesc ? '20px' : 0, right: vertScroll && isDesc ? '20px' : 0}">
            <div class="custom-pagination" :style="themeButtonStyleNo">
                <row-per-page-button :row-per-page="perPage" @val-changed="rowPerPageChanged"></row-per-page-button>
            </div>
            <div class="custom-pagination flex flex--automargin">
                <button v-if="!compact" class="btn btn-primary btn-sm blue-gradient p-first" :style="$root.themeButtonStyle" @click="changePage(1)">First</button>
                <button v-if="!compact" class="btn btn-primary btn-sm blue-gradient" :style="$root.themeButtonStyle" @click="changePage(page > 1 ? page-1 : 1)">{{ isDesc ? 'Prev.' : '<' }}</button>

                <template v-if="page <= 3">
                    <button v-for="i in Math.min(3, maxPage)"
                            class="btn btn-primary btn-sm blue-gradient"
                            :style="btnActive(i)"
                            @click="changePage(i)"
                    >{{ i }}</button>
                    <template v-if="maxPage > 3">
                        <template v-if="maxPage > (3+1)">
                            <pagination-button class="btn btn-primary btn-sm blue-gradient btn-inactive" :style="$root.themeButtonStyle" @change-page="changePage"></pagination-button>
                        </template>
                        <button class="btn btn-primary btn-sm blue-gradient" :style="btnActive(maxPage)" @click="changePage(maxPage)">{{ (maxPage) }}</button>
                    </template>
                </template>
                <!-- available if MaxPage > 3 -->
                <template v-else-if="page > (maxPage-3)">
                    <button class="btn btn-primary btn-sm blue-gradient" :style="btnActive(1)" @click="changePage(1)">1</button>
                    <template v-if="maxPage > (3+1)">
                        <pagination-button class="btn btn-primary btn-sm blue-gradient btn-inactive" :style="$root.themeButtonStyle" @change-page="changePage"></pagination-button>
                    </template>
                    <button v-for="i in 3"
                            class="btn btn-primary btn-sm blue-gradient"
                            :style="btnActive(maxPage-(3-i))"
                            @click="changePage(maxPage-(3-i))"
                    >{{ maxPage-(3-i) }}</button>
                </template>
                <!-- available if MaxPage > 7 -->
                <template v-else="">
                    <button class="btn btn-primary btn-sm blue-gradient" :style="btnActive(1)" @click="changePage(1)">1</button>
                    <pagination-button class="btn btn-primary btn-sm blue-gradient btn-inactive" :style="$root.themeButtonStyle" @change-page="changePage"></pagination-button>
                    <button class="btn btn-primary btn-sm blue-gradient" :style="btnActive(page+1)" @click="changePage(page-1)">{{ page-1 }}</button>
                    <button class="btn btn-primary btn-sm blue-gradient" :style="btnActive(page)" @click="changePage(page)">{{ page }}</button>
                    <button class="btn btn-primary btn-sm blue-gradient" :style="btnActive(page-1)" @click="changePage(page+1)">{{ page+1 }}</button>
                    <pagination-button class="btn btn-primary btn-sm blue-gradient btn-inactive" :style="$root.themeButtonStyle" @change-page="changePage"></pagination-button>
                    <button class="btn btn-primary btn-sm blue-gradient" :style="btnActive(maxPage)" @click="changePage(maxPage)">{{ maxPage }}</button>
                </template>

                <button v-if="!compact" class="btn btn-primary btn-sm blue-gradient" :style="$root.themeButtonStyle" @click="changePage(page < maxPage ? page+1 : maxPage)">{{ isDesc ? 'Next' : '>' }}</button>
                <button v-if="!compact" class="btn btn-primary btn-sm blue-gradient p-last" :style="$root.themeButtonStyle" @click="changePage(maxPage)">Last</button>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../app";

    import PaginationButton from './PaginationButton';
    import RowPerPageButton from "../../Buttons/RowPerPageButton";

    import IsShowFieldMixin from "../../_Mixins/IsShowFieldMixin";

    export default {
        name: "TablePagination",
        mixins: [
            IsShowFieldMixin,
        ],
        components: {
            RowPerPageButton,
            PaginationButton,
        },
        data: function () {
            return {
                bottomPos: '0px',
            }
        },
        props: {
            page: Number,
            tableMeta: Object,
            rowsCount: Number,
            is_link: Object,
            compact: Boolean,
            vertScroll: Boolean,
            horScroll: Boolean,
        },
        computed: {
            isDesc() {
                return window.innerWidth >= 768;
            },
            perPage() {
                return this.is_link ? this.tableMeta.max_rows_in_link_popup : this.tableMeta.rows_per_page;
            },
            maxPage() {
                return this.perPage ? Math.ceil(this.rowsCount / this.perPage) : 1;
            },
            themeButtonStyleNo() {
                let style = _.cloneDeep(this.$root.themeButtonStyle);
                style.border = 'none';
                return style;
            },
            hasFiltered() {
                return this.rowsCount < this.tableMeta._global_rows_count;
            },
        },
        methods: {
            btnActive(need) {
                let style = _.cloneDeep(this.$root.themeButtonStyle);
                style.color = (this.page === (need) ? '#FD0' : '');
                return style;
            },
            changePage(page) {
                this.$emit('change-page', page);
            },
            rowPerPageChanged(val) {
                if (this.is_link) {
                    this.tableMeta.max_rows_in_link_popup = val;
                } else {
                    this.tableMeta.rows_per_page = val;
                }

                if (this.tableMeta._is_owner) {

                    if (this.is_link) {
                        this.$root.updateTable(this.tableMeta, 'vert_tb_hdrwidth');
                    } else {
                        this.$root.sm_msg_type = 1;
                        axios.put('/ajax/table', {
                            table_id: this.tableMeta.id,
                            rows_per_page: this.tableMeta.rows_per_page,
                        }).then(() => {
                            this.changePage(1);
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        }).finally(() => {
                            this.$root.sm_msg_type = 0;
                        });
                    }

                }

                this.$emit('change-page', this.page);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../CustomTable.scss";
</style>