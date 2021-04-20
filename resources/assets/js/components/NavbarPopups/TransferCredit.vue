<template>
    <div class="module-wrapper">
        <div>
            <label>Transfer credits to selected user group(s):</label>
        </div>
        <div>
            <table>
                <tr>
                    <th width="40"></th>
                    <th width="350">User Group Name</th>
                    <th width="110">Users in Group</th>
                    <th width="50"></th>
                    <th width="70" class="center">Credit</th>
                </tr>
                <tr v-for="(gr, idx) in groups">
                    <td class="center">
                        <span class="indeterm_check__wrap chk">
                            <span class="indeterm_check" @click="gr.checked = !gr.checked">
                                <i v-if="gr.checked" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </td>
                    <td>{{ gr.name }}</td>
                    <td>{{ gr.cnt }}</td>
                    <td class="center">
                        <span class="glyphicon glyphicon-remove" @click="deleteGroup(idx)"></span>
                    </td>
                    <td>
                        <input class="form-control" v-model="gr.val"/>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <select v-model="user_group_id" class="form-control cell-input">
                            <option v-for="usr_gr in user._user_groups" v-if="notPresent(usr_gr)" :value="usr_gr.id">{{ usr_gr.name }}</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-success" @click="addGroup()">Add</button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button class="btn btn-sm" :class="[noActiveGroups ? '' : 'btn-success']" :disabled="noActiveGroups" @click="transferCredits()">Confirm</button>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>
</template>

<script>
    export default {
        name: "TransferCredit",
        data: function () {
            return {
                user_group_id: null,
                groups: []
            }
        },
        props:{
            user: Object,
        },
        computed: {
            noActiveGroups() {
                let gr = _.filter(this.groups, {checked: true});
                return !(gr && gr.length);
            }
        },
        methods: {
            notPresent(item) {
                return _.findIndex(this.groups, {id: item.id}) === -1;
            },
            addGroup() {
                let u_gr = _.find(this.user._user_groups, {id: Number(this.user_group_id)});
                if (u_gr) {
                    this.groups.push({
                        checked: false,
                        id: u_gr.id,
                        name: u_gr.name,
                        cnt: u_gr._individuals_all_count,
                        val: null,
                    });
                }
            },
            deleteGroup(idx) {
                this.groups.splice(idx, 1);
            },
            transferCredits() {
                let total = 0;
                _.each(this.groups, (el) => {
                    if (el.checked) {
                        total += el.cnt * this.$root.getFloat(el.val);
                    }
                });
                if (total <= this.user.avail_credit) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/user/transfer-credits', {
                        groups: _.filter(this.groups, {checked: true}),
                    }).then(({ data }) => {
                        this.user.avail_credit = data.avail_credit;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    Swal('', 'You do not have sufficient credit.');
                }
            }
        },
    }
</script>

<style lang="scss" scoped>
    .module-wrapper {
        .chk {
            width: 15px;
            height: 15px;
        }

        table {
            border-spacing: 5px;
            border-collapse: separate;
            width: 90%;

            td {
                position: relative;
            }
            .glyphicon-remove {
                color: #F00;
            }
            .center {
                text-align: center;
            }
        }
    }
</style>