<template>
    <div class="flex flex--center full-height" style="position: relative;">

        <div class="flex flex--center full-height w-33" :style="w_theme ? $root.themeMainTxtColor : {color: '#222'}">
            <div class="number_top">From: {{ twAcc.name }}<br><span v-html="$root.telFormat(twAcc.twilio_phone)"></span></div>
            <i class="fas fa-phone-alt fsize"
               :class="[from_active ? 'green' : 'red']"
               :style="{rotate: !from_active ? '135deg' : ''}"
            ></i>
        </div>

        <div class="flex flex--center full-height w-33" :style="w_theme ? $root.themeMainTxtColor : {color: '#222'}">
            <button v-if="!from_active && !to_active && !timer_value && can_show_call"
                    class="btn btn-success"
                    @click="$emit('browser-call')"
            >Click to Call</button>

            <span v-if="from_active && !to_active" style="position: absolute;top: 50%;left: 45%;">Dialing</span>
            <span v-if="from_active && !to_active" style="font-size: 36px;">---------&gt;</span>

            <span v-if="from_active && to_active" style="position: absolute;top: 50%;left: 44%;">In Progress</span>
            <span v-if="from_active && to_active" style="font-size: 36px;">&lt;---------&gt;</span>
            <span v-if="from_active && to_active" style="position: absolute;top: 56%;left: 47%;">{{ timerUi }}</span>
            <button v-if="from_active && to_active"
                    class="btn btn-danger"
                    @click="$emit('hang-up')"
                    style="position: absolute;top: 65%;left: 41%;"
            >End the Call</button>

            <span v-if="timer_value && !from_active && !to_active" style="position: absolute;top: 50%;left: 46%;">Duration</span>
            <span v-if="timer_value && !from_active && !to_active" style="font-size: 36px;">&lt;---------&gt;</span>
            <span v-if="timer_value && !from_active && !to_active" style="position: absolute;top: 56%;left: 48%;">{{ timerUi }}</span>
        </div>

        <div class="flex flex--center full-height w-33" :style="w_theme ? $root.themeMainTxtColor : {color: '#222'}">
            <i class="fas fa-phone fsize"
               :class="[to_active ? 'green' : 'red']"
               :style="{rotate: !to_active ? '-135deg' : ''}"
            ></i>
            <div class="number_bot">To: {{ toName }}<br><span v-html="$root.telFormat(toPhone)"></span></div>
        </div>

    </div>
</template>

<script>
    export default {
        name: "CallUi",
        data: function () {
            return {
            }
        },
        props: {
            can_show_call: Boolean,
            from_active: Boolean,
            to_active: Boolean,
            timer_value: Number,
            timerUi: String,
            twAcc: Object,
            toName: String,
            toPhone: String|Number,
            w_theme: Boolean,
        },
    }
</script>

<style scoped lang="scss">
.fsize {
    font-size: 72px;
    margin: 36px;
}
.w-33 {
    width: 33%;
}
.number_top, .number_bot {
    font-size: 18px;
    font-weight: bold;
}
.number_top {
    position: absolute;
    top: 15%;
}
.number_bot {
    position: absolute;
    bottom: 15%;
}
</style>