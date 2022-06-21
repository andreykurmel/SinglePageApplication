<template>
    <div ref="addr_position" class="full-height"></div>
</template>

<script>
    import {eventBus} from '../../../app';

    export default {
        name: "GoogleAddressViewer",
        data: function () {
            return {
                uuid: uuidv4(),
                can_google: this.$root.checkAvailable(this.$root.user, 'can_google_autocomplete')
                    || this.$root.is_dcr_page,
            }
        },
        props: {
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
        },
        computed: {
        },
        methods: {
            hideElement(tableRow) {
                if (tableRow) {
                    this.$emit('update-row', tableRow);
                }
                this.$emit('hide-elem');
            }
        },
        mounted() {
            if (this.can_google) {
                let rect = this.$refs.addr_position.getBoundingClientRect();
                eventBus.$emit('google-addr-autocomplete__show', rect, this.tableMeta, this.tableHeader, this.tableRow);
            }

            eventBus.$on('google-addr-autocomplete__hide', this.hideElement);
        },
        beforeDestroy() {
            eventBus.$off('google-addr-autocomplete__hide', this.hideElement);
        }
    }
</script>

<style lang="scss" scoped>
    .addr_wrapper {
    }
</style>