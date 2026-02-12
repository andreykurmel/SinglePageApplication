<template>
    <td :style="getCellStyle()" class="td-custom">
        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content" style="text-align: center">

                    <label class="switch_t" v-if="tableHeader.f_type === 'Boolean'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!canCellEdit" :checked="checkBoxOn" @change.prevent="updateCheckBox()">
                        <span class="toggler round" :class="[!canCellEdit ? 'disabled' : '']"></span>
                    </label>

                    <div v-else-if="tableHeader.field === 'table_name'" class="inner-content">
                        <a target="_blank"
                           title="Open the “Visiting” MRV in a new tab."
                           :href="showField('__visiting_url')"
                           @click.stop=""
                           v-html="showField()"></a>
                        <a v-if="isOwner"
                           title="Open the source table in a new tab."
                           target="_blank"
                           :href="showField('__url')"
                           @click.stop="">(Table)</a>
                    </div>

                    <a v-else-if="tableHeader.f_type === 'User' && userFull()"
                       title="Open the user profile in a new tab."
                       :target="user.is_admin ?  '_blank' : ''"
                       :href="user.is_admin ? userHref() : 'javascript:void(0)'"
                       :style="{whiteSpace: 'nowrap'}"
                    >
                        <span v-html="userFull()"></span>
                    </a>

                    <a v-else-if="tableHeader.field === 'ref_cond_name' && tableRow.table_id == globalMeta.id"
                       title="Open ref condition in popup."
                       @click.stop="showPopRefCond()"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'use_name' && tableRow.table_id == globalMeta.id"
                       title="Open used category in popup."
                       @click.stop="showUseCatPop()"
                    >{{ showField() }}</a>

                    <div v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>
    </td>
</template>

<script>
import {eventBus} from "../../app";

import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

export default {
        name: "CustomCellIncomingLinks",
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                canCellEdit: this.tableHeader.field === 'incoming_allow',
            }
        },
        props:{
            globalMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
        },
        computed: {
            checkBoxOn() {
                return Number(this.tableRow[this.tableHeader.field]);
            },
            isOwner() {
                return this.$root.user.id == this.globalMeta.user_id;
            },
        },
        methods: {
            updateCheckBox() {
                this.tableRow[this.tableHeader.field] = !this.tableRow[this.tableHeader.field];
                this.$emit('updated-cell', this.tableRow);
            },
            userHref() {
                let usr = this.$root.smallUserStr(this.tableRow, this.tableHeader, this.tableRow[this.tableHeader.field], 'object');
                return usr && usr.id && !isNaN(usr.id)
                    ? '/profile/'+usr.id
                    : 'javascript:void(0)';
            },
            userFull() {
                return this.$root.getUserFullStr(this.tableRow, this.tableHeader, this.globalMeta._cur_settings);
            },
            showField(link) {
                let res = '';

                if (this.tableHeader.field === 'table_name' && this.tableRow.table_id) {
                    let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow.table_id)});
                    res = tb && link
                        ? tb[link]
                        : (this.tableRow.table_id == this.globalMeta.id
                            ? '<span style="color: #00F;">SELF</span>'
                            : this.tableRow.table_name);
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_danger_tags(res);
            },
            showPopRefCond() {
                eventBus.$emit('show-ref-conditions-popup', this.globalMeta.db_name, this.tableRow.ref_cond_id);
            },
            showUseCatPop() {
                switch (this.tableRow.use_category) {
                    case 'RowGroup':
                        eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, 'row', this.tableRow.use_id);
                        break;
                    case 'DDL':
                        eventBus.$emit('show-ddl-settings-popup', this.globalMeta.db_name, this.tableRow.use_id);
                        break;
                    case 'Link':
                        eventBus.$emit('show-display-links-settings-popup', this.tableRow.use_id);
                        break;
                }
            },
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";
</style>