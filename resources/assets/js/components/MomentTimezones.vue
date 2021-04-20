<template>
    <select type="text" class="form-control" :id="name" :name="name"
            placeholder="Timezone" v-model="tz" @change="$emit('changed-tz', tz)">
        <option v-for="zone in timezones">{{ zone }}</option>
    </select>
</template>

<script>
    export default {
        name: "MomentTimezones",
        data: function () {
            return {
                timezones: moment.tz.names(),
                tz: this.cur_tz ? this.cur_tz : moment.tz.guess(),
            }
        },
        props:{
            name: String,
            cur_tz: String,
        },
        mounted() {
        }
    }
</script>