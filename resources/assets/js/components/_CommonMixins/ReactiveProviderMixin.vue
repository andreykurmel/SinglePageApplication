<script>
    export default {
        data: function () {
            return {
                reactive_provider: {},
            }
        },
        methods: {
            reactive_provider_watcher(props) {
                _.each(props, (prop) => {
                    this.$set(this.reactive_provider, prop);
                });

                _.each(props, (prop) => {
                    //watch objects also, because if object created 'on the fly', it doesn't have vue observers
                    this.$watch(prop, function (newVal, oldVal) {
                        this.reactive_provider[prop] = newVal;
                    }, {
                        deep: true,
                        immediate: true
                    });
                });
            }
        },
    }
</script>