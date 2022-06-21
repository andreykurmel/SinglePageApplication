<template>
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
</template>

<script>
    export default {
        name: "SendMessageBlock",
        data: function () {
            return {
                newMessage: '',
            }
        },
        props: {
            addMsgHeight: Number,
            owner: Boolean,
            owner_id: Number,
            table_id: Number,
            with_group: Boolean,
        },
        computed: {
            txaTitle() {
                return 'Enter 3 or more letters to search user or usergroup having permissions for this table to start messaging.';
            }
        },
        methods: {
            sendMessage() {
                let to_user, to_user_group;
                if (this.owner) {
                    let val = $(this.$refs.search_user).val();
                    to_user = val && val[0] !== '_' ? (val || 0) : 0;
                    to_user_group = val && val[0] === '_' ? (val.substr(1) || null) : null;
                } else {
                    to_user = this.owner_id;
                    to_user_group = null;
                }

                this.$emit('send-message', this.newMessage, to_user, to_user_group);

                this.newMessage = '';
                $(this.$refs.search_user).val(null).trigger('change');
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
                    url: this.with_group ? '/ajax/user/search-can-group' : '/ajax/user/search',
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
                width: calc(100% - 30px);
            }
        }
        .message-text, .message-btn {
            height: 100%;
        }
        .message-text {
            width: calc(100% - 34px);
            float: left;
            resize: none;
        }
    }
</style>