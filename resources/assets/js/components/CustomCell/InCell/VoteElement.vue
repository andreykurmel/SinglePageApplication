<template>
    <div class="vote-wrapper">
        <template v-if="table_header.f_format === 'Like/Dislike'">
            <i class="fa fa-thumbs-up" :class="{liked: usr_yes, avail: edit_avail, morepadd: !small_padding}" @click="setVote(true)"></i>
            <span class="vote-info" @click="loadUInfo('yes')">({{ is_def_cell ? '1' : votes.yes.length }})</span>
            <span>/</span>
            <i class="fa fa-thumbs-down" :class="{disliked: usr_no, avail: edit_avail, morepadd: !small_padding}" @click="setVote(false)"></i>
            <span class="vote-info" @click="loadUInfo('no')">({{ is_def_cell ? '0' : votes.no.length }})</span>
        </template>
        <template v-else="">
            <span :class="{yesed: usr_yes, avail: edit_avail}" @click="setVote(true)">Yes</span>
            <span class="vote-info" @click="loadUInfo('yes')">({{ is_def_cell ? '1' : votes.yes.length }})</span>
            <span>/</span>
            <span :class="{noned: usr_no, avail: edit_avail}" @click="setVote(false)">No</span>
            <span class="vote-info" @click="loadUInfo('no')">({{ is_def_cell ? '0' : votes.no.length }})</span>
        </template>

        <div v-if="uinfos" ref="vote_info" class="vote_info_wrap" :style="fourDimsStyle()">
            <div v-for="usr in uinfos">
                <div v-html="$root.getUserSimple(usr, user_info_settings, 'vote_user')"></div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../../app';

    import MixinSmartPosition from '../../_Mixins/MixinSmartPosition';

    export default {
        name: "VoteElement",
        mixins: [
            MixinSmartPosition,
        ],
        data: function () {
            return {
                votes: {yes:[], no:[]},
                uinfos: null,
                smart_wrapper: 'vote_info',
                smart_horizontal: 10,
                smart_limit: 25,
            }
        },
        props:{
            can_edit: Boolean,
            table_header: Object,
            cell_val: String,
            user_info_settings: Object,
            small_padding: Boolean,
            is_def_cell: Boolean,
        },
        computed: {
            edit_avail() {
                return this.can_edit && this.$root.user.id;
            },
            usr_yes() {
                return _.find(this.votes.yes, (el) => { return el == this.$root.user.id; });
            },
            usr_no() {
                return _.find(this.votes.no, (el) => { return el == this.$root.user.id; });
            },
        },
        watch: {
            cell_val(val) {
                this.getVotes(val);
            },
        },
        methods: {
            setVote(yes) {
                if (!this.edit_avail) {
                    return;
                }
                let was_before = yes
                    ? this.votes.yes.indexOf(this.$root.user.id) > -1
                    : this.votes.no.indexOf(this.$root.user.id) > -1;

                this.votes.yes = _.filter(this.votes.yes, (el) => { return el != this.$root.user.id; });
                this.votes.no = _.filter(this.votes.no, (el) => { return el != this.$root.user.id; });

                if (!was_before && yes) {
                    this.votes.yes.push(this.$root.user.id);
                }
                if (!was_before && !yes) {
                    this.votes.no.push(this.$root.user.id);
                }

                this.votes.yes = _.uniq(this.votes.yes);
                this.votes.no = _.uniq(this.votes.no);
                this.$emit( 'set-val', JSON.stringify(this.votes) );
            },
            getVotes(votes_str) {
                this.votes = votes_str
                    ? JSON.parse(votes_str)
                    : {yes:[], no:[]};
            },
            loadUInfo(key) {
                let idss = this.votes[key] || [];
                if (idss && idss.length) {
                    axios.post('/ajax/user/finds', {
                        users_ids: idss,
                    }).then(({data}) => {
                        this.uinfos = data;
                        this.showItemsList();
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    });
                }
            },
            hideInfo(e) {
                let container = $(this.$refs.vote_info);
                if (container && container.has(e.target).length === 0){
                    this.uinfos = null;
                }
            },
        },
        mounted() {
            this.getVotes(this.cell_val);
            eventBus.$on('global-click', this.hideInfo);
        },
        beforeDestroy() {
            eventBus.$on('global-click', this.hideInfo);
        }
    }
</script>

<style lang="scss" scoped>
    .vote-wrapper {
        .avail, .vote-info {
            cursor: pointer;

            &:hover {
                text-decoration: underline;
            }
        }

        .fa-thumbs-up, .fa-thumbs-down {
            color: #FFF;
            background: #777;
            padding: 1px;
            border-radius: 50%;
        }
        .morepadd {
            padding: 3px 3px 4px 4px;
        }
        .liked {
            background: #292;
        }
        .disliked {
            background: #922;
        }

        .yesed {
            font-weight: bold;
            color: #292;
        }
        .noned {
            font-weight: bold;
            color: #922;
        }

        .vote_info_wrap {
            max-height: 150px;
            position: absolute;
            white-space: nowrap;
            background-color: #EEE;
            z-index: 100;
            padding: 3px;
            border: 1px solid #aaa;
            border-radius: 3px;
            text-align: left;
            line-height: 1.3em;
            overflow: auto;

            ::-webkit-scrollbar {
                width: 5px;
            }
        }
    }
</style>