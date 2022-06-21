<template>
    <div class="flex flex--center">
        <button v-if="vuex_settings.__selected_feedback"
                class="btn btn-primary btn-sm blue-gradient"
                :style="$root.themeButtonStyle"
                @click="feedbacksClick"
        >Feedbacks ({{ vuex_settings.__feedback_results.length }})</button>

        <feedback-pass-block
            v-if="show_password_check && vuex_settings.__selected_feedback"
            :selected_feedback="vuex_settings.__selected_feedback"
            @password-correct="show_presents = true"
            @popup-close="show_password_check = false"
        ></feedback-pass-block>

        <present-results-popup
            v-if="show_presents && vuex_settings.__selected_feedback"
            :selected_feedback="vuex_settings.__selected_feedback"
            :all_results="vuex_settings.__feedback_results"
            @popup-close="show_presents = false"
            @show-add-feedback="show_feedback = true"
        ></present-results-popup>

        <feedback-result-popup
            v-if="show_feedback && vuex_settings.__selected_feedback"
            :selected_feedback="vuex_settings.__selected_feedback"
            @result-submitted="resultSubmitted"
            @popup-close="show_feedback = false"
        ></feedback-result-popup>
    </div>
</template>

<script>
    import { mapState } from 'vuex';

    import {SpecialFuncs} from "../../../../classes/SpecialFuncs";

    import PresentResultsPopup from "./PresentResultsPopup";
    import FeedbackResultPopup from "./FeedbackResultPopup";
    import FeedbackPassBlock from "./FeedbackPassBlock";

    export default {
        name: 'FeedbackBlock',
        components: {
            FeedbackPassBlock,
            FeedbackResultPopup,
            PresentResultsPopup,
        },
        computed: {
            ...mapState({
                vuex_settings: state => state.stim_settings,
            }),
        },
        data() {
            return {
                show_presents: false,
                show_feedback: false,
                show_password_check: false,
            }
        },
        methods: {
            feedbacksClick() {
                if (this.vuex_settings.__selected_feedback.request_pass) {
                    this.show_password_check = true;
                } else {
                    this.show_presents = true;
                }
            },
            toLocal(date) {
                return SpecialFuncs.convertToLocal(date, this.$root.user.timezone);
            },
            resultSubmitted(results) {
                this.show_feedback = false;
                this.vuex_settings.__feedback_results = [];
                this.$nextTick(() => {
                    this.vuex_settings.__feedback_results = results;
                });
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    .results_wrap {
        cursor: pointer;
        text-align: center;
    }
</style>