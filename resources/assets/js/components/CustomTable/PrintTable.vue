<template>
    <div>
        <table v-for="i in printPagesForRow" class="print-table">
            <thead>
            <tr>
                <th>Row #</th>
                <th v-for="(hdr, index) in printHeaders" v-if="isShowed(hdr, index, i)">{{ getTitle(hdr.name) }}</th>
            </tr>
            </thead>

            <tbody>
                <tr v-for="(row, index) in printRows">
                    <td>{{ index }}</td>
                    <td v-for="(hdr, hdr_idx) in printHeaders" v-if="isShowed(hdr, hdr_idx, i)">{{ row[hdr.field] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import IsShowFieldMixin from './../_Mixins/IsShowFieldMixin.vue';

    export default {
        name: "PrintTable",
        mixins: [
            IsShowFieldMixin,
        ],
        data: function () {
            return {
                printColPerPage: 9
            }
        },
        computed: {
            printPagesForRow() {
                return Math.ceil(this.printHeaders.length / this.printColPerPage);
            }
        },
        props:{
            printHeaders: Array,
            printRows: Array,
            allShowed: Boolean
        },
        methods: {
            getTitle(name) {
                return _.uniq( name.split(',') ).join(' ');
            },
            isShowed(hdr, index, number) {
                if (this.allShowed && (index >= (number-1)*this.printColPerPage && index < number*this.printColPerPage)) {
                    return true;
                }

                return this.isShowField(hdr)
                    && (index >= (number-1)*this.printColPerPage && index < number*this.printColPerPage);
            }
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomTable.scss";
</style>