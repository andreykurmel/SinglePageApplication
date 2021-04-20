<template>
    <div class="full-height">
        <div class="messages-list" :style="{height: 'calc(100% - '+addMsgHeight+'px)'}">
            <div class="message-elem" v-for="msg in tableMessages">
                <div>
                    <label class="no-margin">
                        <message-user-info :msg-obj="msg" :type="'from'"></message-user-info>
                        @
                        <message-user-info :msg-obj="msg" :type="'to'"></message-user-info>
                    </label>
                    <span class="del_msg_btn" v-if="owner || $root.user.id === msg.from_user_id" @click="deleteMessage(msg.id)">&times;</span>
                </div>
                <div>
                    <label>{{ $root.convertToLocal(msg.date, $root.user.timezone) }}</label>
                </div>
                <div>{{ msg.message }}</div>
            </div>
        </div>
        <div class="message-add" :style="{height: addMsgHeight+'px'}">
            <div v-show="owner" class="user-wrapper">
                <label class="user-label">To: </label>
                <div class="user-select">
                    <select ref="search_user" :title="txaTitle"></select>
                </div>
            </div>
            <div :style="{height: (addMsgHeight-(owner ? 40 : 10))+'px'}">
                <textarea class="form-control message-text" v-model="newMessage" @keydown="keyPressedOnText()"></textarea>
                <button class="btn btn-sm btn-primary message-btn" :style="$root.themeButtonStyle" @click="sendMessage()">
                    <i class="glyphicon glyphicon-send"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import MessageUserInfo from "./MessageUserInfo";

    export default {
        components: {MessageUserInfo}, name: "RightMenuMessages",
        mixins: [
        ],
        data: function () {
            return {
                newMessage: '',
                addMsgHeight: this.owner ? 110 : 80
            }
        },
        props: {
            owner: Boolean,
            owner_id: Number,
            table_id: Number,
            tableMessages: Array,
        },
        computed: {
            txaTitle() {
                return 'Enter 3 or more letters to search user or usergroup having permissions for this table to start messaging.';
            }
        },
        methods: {
            sendMessage() {
                $.LoadingOverlay('show');
                let to_user, to_user_group;
                if (this.owner) {
                    let val = $(this.$refs.search_user).val();
                    to_user = val && val[0] !== '_' ? (val || 0) : 0;
                    to_user_group = val && val[0] === '_' ? (val.substr(1) || null) : null;
                } else {
                    to_user = this.owner_id;
                    to_user_group = null;
                }
                axios.post('/ajax/table/message', {
                    table_id: this.table_id,
                    to_user_id: to_user,
                    to_user_group_id: to_user_group,
                    message: this.newMessage
                }).then(({ data }) => {
                    this.tableMessages.splice(0, 0, data);
                    this.newMessage = '';
                    $(this.$refs.search_user).val(null).trigger('change');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteMessage(id) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table/message', {
                    params: {
                        msg_id: id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMessages, {id: Number(id)});
                    if (idx > -1) {
                        this.tableMessages.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            keyPressedOnText() {
                if (window.event.keyCode === 13 && !window.event.shiftKey) {
                    this.sendMessage();
                }
            },
        },
        mounted() {
            $(this.$refs.search_user).select2({
                ajax: {
                    url: '/ajax/user/search-can-group',
                    dataType: 'json',
                    delay: 250,
                    data: (params) => {
                        return {
                            q: params.term,
                            table_id: this.table_id
                        }
                    },
                },
                minimumInputLength: 3,
                width: '100%'
            });
            $(this.$refs.search_user).next().css('height', '28px');
        }
    }
</script>

<style lang="scss" scoped>
    .messages-list {
        padding: 5px;
        overflow: auto;

        .message-elem {
            margin-bottom: 25px;

            .del_msg_btn {
                float: right;
                font-size: 2em;
                cursor: pointer;
            }
        }
    }
    .message-add {
        position: relative;
        padding: 5px;
        border-top: 1px solid #CCC;

        .user-wrapper {
            height: 30px;

            .user-label {
                line-height: 28px;
            }
            .user-select {
                float: right;
                width: 200px;
            }
        }
        .message-text, .message-btn {
            height: 100%;
        }
        .message-text {
            width: 192px;
            float: left;
            resize: none;
        }
    }
</style>