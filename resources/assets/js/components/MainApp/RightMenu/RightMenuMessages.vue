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
        <send-message-block
            :add-msg-height="addMsgHeight"
            :owner="owner"
            :owner_id="owner_id"
            :table_id="table_id"
            :with_group="true"
            @send-message="sendMessage"
        ></send-message-block>
    </div>
</template>

<script>
    import MessageUserInfo from "./MessageUserInfo";
    import SendMessageBlock from "../../CommonBlocks/SendMessageBlock";

    export default {
        components: {
            SendMessageBlock,
            MessageUserInfo,
        },
        name: "RightMenuMessages",
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
            sendMessage(message, to_user_id, to_group_id) {
                $.LoadingOverlay('show');
                axios.post('/ajax/table/message', {
                    table_id: this.table_id,
                    to_user_id: to_user_id,
                    to_user_group_id: to_group_id,
                    message: message
                }).then(({ data }) => {
                    this.tableMessages.splice(0, 0, data);
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
                minimumInputLength: {val:3},
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
</style>