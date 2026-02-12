<template>
    <div class="module-wrapper">
        <div>
            <label>Transfer credits to:</label>
        </div>
        <div>
            <table>
                <tr>
                    <th width="40"></th>
                    <th width="350">User Group Name Or Username</th>
                    <th width="110">Users in Group</th>
                    <th width="50"></th>
                    <th width="70" class="center">Credit</th>
                </tr>

                <tr>
                    <td></td>
                    <td>Selected user group(s):</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr v-for="(gr, idx) in groups">
                    <td class="center">
                        <span class="indeterm_check__wrap chk">
                            <span class="indeterm_check" @click="gr.checked = !gr.checked">
                                <i v-if="gr.checked" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </td>
                    <td>
                        <a title="Open usergroup settings in popup." @click.stop="showUsergroupPopup(gr.id)">{{ gr.name }}</a>
                    </td>
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
                    <td>Individual user(s):</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr v-for="(usr, idx) in users">
                    <td class="center">
                        <span class="indeterm_check__wrap chk">
                            <span class="indeterm_check" @click="usr.checked = !usr.checked">
                                <i v-if="usr.checked" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                    </td>
                    <td>{{ usr.name }}</td>
                    <td>{{ usr.cnt }}</td>
                    <td class="center">
                        <span class="glyphicon glyphicon-remove" @click="deleteUser(idx)"></span>
                    </td>
                    <td>
                        <input class="form-control" v-model="usr.val"/>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <div style="position: relative; height: 36px;">
                            <select ref="search_user"></select>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-success" @click="addUser()">Add</button>
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
    import {eventBus} from "../../app";

    export default {
        name: "TransferCredit",
        data: function () {
            return {
                user_group_id: null,
                groups: [],
                users: [],
            }
        },
        props:{
            user: Object,
        },
        computed: {
            noActiveGroups() {
                let gr = _.filter(this.groups, {checked: true});
                let usr = _.filter(this.users, {checked: true});
                return !(gr && gr.length) && !(usr && usr.length);
            }
        },
        methods: {
            showUsergroupPopup(id) {
                let idx = _.findIndex(this.user._user_groups, {id: id});
                eventBus.$emit('open-resource-popup', 'users', idx);
            },
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
            addUser() {
                if ($(this.$refs.search_user).val()) {
                    this.users.push({
                        checked: true,
                        eml: $(this.$refs.search_user).val(),
                        name: $(this.$refs.search_user).text(),
                        cnt: 1,
                        val: null,
                    });
                    $(this.$refs.search_user).empty().trigger('change');
                }
            },
            deleteUser(idx) {
                this.users.splice(idx, 1);
            },
            transferCredits() {
                let total = 0;
                let grnames = [];
                _.each(this.groups, (el) => {
                    if (el.checked) {
                        total += el.cnt * this.$root.getFloat(el.val);
                        grnames.push(el.name);
                    }
                });
                let usrnames = [];
                _.each(this.users, (el) => {
                    if (el.checked) {
                        total += el.cnt * this.$root.getFloat(el.val);
                        usrnames.push(el.name);
                    }
                });
                if (total <= this.user.avail_credit) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/user/transfer-credits', {
                        groups: _.filter(this.groups, {checked: true}),
                        users: _.filter(this.users, {checked: true}),
                    }).then(({ data }) => {
                        this.user.avail_credit = data.avail_credit;
                        let swalmsg = '$' + total + ' credit has been successfully transferred to:<br>';
                        if (grnames.length) {
                            swalmsg += 'Groups: ' + grnames.join(', ') + '<br>';
                        }
                        if (usrnames.length) {
                            swalmsg += 'Users: ' + usrnames.join(', ');
                        }
                        Swal('Info', swalmsg);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    Swal('Info', 'You do not have sufficient credit.');
                }
            }
        },

        mounted() {
            $(this.$refs.search_user).select2({
                ajax: {
                    url: '/ajax/user/search',
                    dataType: 'json',
                    delay: 250,
                    data: (params) => {
                        return {
                            q: params.term,
                            request_field: 'email',
                        }
                    },
                },
                minimumInputLength: {val:3},
                width: '100%',
                height: '100%'
            });
            $(this.$refs.search_user).next().css('height', '36px');
        }
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