<template>
    <div class="full-height" style="padding: 5px;">
        <div style="height: calc(100% - 40px); overflow: auto;"
             ref="ai_messages"
             @scroll="loadMessages"
        >
            <div v-if="selectedAi" class="flex" style="flex-direction: column-reverse;">
                <div v-for="(msg, idx) in selectedAi._ai_messages"
                     class="mb10 flex"
                     :class="[msg.who === 'Me' ? 'flex--end' : 'flex--start']"
                     :style="msgStyle"
                >
                    <div class="msg" :style="msgStl(msg.who)">
                        <div style="font-weight: bold;">
                            <who-part :msg="msg" :size="selectedAi.font_size || 14"></who-part>
                            <i title="Delete" class="glyphicon glyphicon-remove hover-red" @click="$emit('remove-msg', idx, msg.id)"></i>
                            <i title="Copy" class="fa fa-copy hover-red ml10" @click="copyMsg(msg.content)"></i>
                        </div>
                        <div :style="tableStyleIfNeeded(msg)" v-html="msgContent(msg)"></div>
                    </div>
                </div>
            </div>
            <div v-if="tempMyMessage" :style="msgStyle" class="mb10 flex flex--end">
                <div :style="msgStl('Me')">
                    <div style="font-weight: bold;">Me:</div>
                    <div>{{ tempMyMessage }}</div>
                </div>
            </div>
        </div>
        <div style="height: 40px;" class="flex">
            <textarea class="form-control message-text" v-model="newMessage" @keydown="keyPressedOnText()"></textarea>
            <button class="btn btn-sm btn-primary message-btn" :style="$root.themeButtonStyle" @click="sendMessage()">
                <i class="glyphicon glyphicon-send"></i>
            </button>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import {eventBus} from '../../../../../app';

    import WhoPart from "./WhoPart.vue";

    export default {
        name: "AiModule",
        mixins: [
        ],
        components: {
            WhoPart
        },
        data: function () {
            return {
                allMessagesLoaded: false,
                messagesLoading: false,
                tempMyMessage: '',
                newMessage: '',
            }
        },
        props: {
            selectedAi: Object,
            request_params: Object,
        },
        computed: {
            msgStyle() {
                return this.selectedAi ? {
                    position: 'relative',
                    //color: SpecialFuncs.smartTextColorOnBg(this.selectedAi.bg_color),
                } : {};
            },
        },
        watch: {
        },
        methods: {
            msgStl(who) {
                return {
                    padding: '10px',
                    borderRadius: '10px',
                    backgroundColor: who === 'Me' ? this.selectedAi.bg_me_color : this.selectedAi.bg_gpt_color,
                }
            },
            tableStyleIfNeeded(msg) {
                return {
                    fontFamily: String(msg.content).match('|') ? 'monospace' : null,
                };
            },
            msgContent(msg) {
                let str = SpecialFuncs.nl2br(msg.content);
                str = SpecialFuncs.space2nbsp(str);
                return SpecialFuncs.strip_tags(str);
            },
            loadMessages() {
                if (this.allMessagesLoaded) {
                    return;
                }

                let scrl = this.$refs.ai_messages ? $(this.$refs.ai_messages).scrollTop() : 99999;
                if (scrl < 10 && ! this.messagesLoading) {
                    this.messagesLoading = true;
                    axios.get('/ajax/addon-ai/messages', {
                        params: {
                            model_id: this.selectedAi.id,
                            offset: this.selectedAi._ai_messages.length,
                        }
                    }).then(({data}) => {
                        this.messagesLoading = false;
                        if (data && data.length) {
                            this.selectedAi._ai_messages = this.selectedAi._ai_messages.concat(data);
                            this.$nextTick(() => {
                                let h = Number(data.length) * Number(this.selectedAi.font_size || 14) * 3.2 + 20;
                                this.$refs.ai_messages.scrollTo(0, h);
                            });
                        } else {
                            this.allMessagesLoaded = true;
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },
            sendMessage() {
                this.tempMyMessage = this.newMessage;
                this.scrollBottom();

                axios.post('/ajax/addon-ai/messages', {
                    model_id: this.selectedAi.id,
                    message: this.newMessage,
                    request_params: SpecialFuncs.dataRangeRequestParams(this.selectedAi.ai_data_range, this.selectedAi.table_id, this.request_params)
                }).then(({data}) => {
                    this.tempMyMessage = '';
                    if (data && data.question && data.response) {
                        this.selectedAi._ai_messages.splice(0, 0, data.question);
                        this.selectedAi._ai_messages.splice(0, 0, data.response);
                    }
                    this.scrollBottom();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });

                this.newMessage = '';
            },
            keyPressedOnText() {
                if (window.event.keyCode === 13 && !window.event.shiftKey) {
                    window.event.preventDefault();
                    this.sendMessage();
                }
            },
            scrollBottom() {
                this.$nextTick(() => {
                    let el = this.$refs.ai_messages;
                    el.scrollTo(0, el.scrollHeight);
                });
            },
            copyMsg(msg) {
                SpecialFuncs.strToClipboard(msg);
                Swal('Info', 'Copied to Clipboard!');
            },
        },
        mounted() {
            this.scrollBottom();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .message-text, .message-btn {
        height: 100%;
    }
    .message-text {
        width: 100%;
        float: left;
        resize: none;
    }
    .glyphicon-remove {
        cursor: pointer;
        top: 2px;
    }
    .fa-copy {
        cursor: pointer;
    }
</style>