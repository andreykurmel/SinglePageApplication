<template>
    <div class="avail-wrapper">
        <div v-for="(group,gr_name) in tabs_tree">
            <!--HORIZONTALS-->
            <div class="check-wrap">
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check checkbox-input" @click="toggleGroup(group)" :style="$root.checkBoxStyle">
                        <i v-if="checkedGroup(group)" class="glyphicon glyphicon-ok group__icon"></i>
                        <i v-else-if="someInGroup(group)" class="glyphicon glyphicon-minus group__icon"></i>
                    </span>
                </span>
                <label>{{ gr_name }}</label>
            </div>

            <!--VERTICAL SUBGROUPS-->
            <div v-for="table in group" v-if="group.length > 1">
                <div class="check-wrap check-wrap--sub">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check checkbox-input" @click="toggleOne(table)" :style="$root.checkBoxStyle">
                            <i v-if="checkedOne(table)" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>{{ table.vertical }}</label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ViewAvailTabs",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
                avails: [],
            }
        },
        props:{
            app_view: Object,
            tabs_tree: Object,
        },
        methods: {
            //Checkers
            checkedOne(table) {
                return this.avails.indexOf(table.id) > -1;
            },
            checkedGroup(group) { //all tables checked
                return !_.find(group, (table) => {
                    return !this.checkedOne(table);
                });
            },
            someInGroup(group) { //some tables checked
                return _.find(group, (table) => {
                    return this.checkedOne(table);
                });
            },

            //Togglers
            toggleOne(table) {
                if (this.checkedOne(table)) { //remove from avails
                    this.avails = _.filter(this.avails, (id) => {
                        return id !== table.id;
                    });
                } else { //add to avails
                    this.avails.push(table.id);
                }
                this.emitUpdate();
            },
            toggleGroup(group) {
                if (this.checkedGroup(group)) { //remove from avails
                    this.avails = _.filter(this.avails, (id) => {
                        return !_.find(group, {id: id});
                    });
                } else { //add to avails
                    _.each(group, (table) => {
                        this.avails.push(table.id);
                    });
                }
                this.emitUpdate();
            },

            //Emits
            emitUpdate() {
                this.app_view.visible_tab_ids = JSON.stringify(this.avails);
                this.$emit('update-view', this.app_view);
            },

            //Prepares
            fillAvails() {
                let present;

                try {
                    present = this.app_view.visible_tab_ids ? JSON.parse(this.app_view.visible_tab_ids) : [];
                } catch ($e) {
                    present = [];
                }

                if (!present.length) {
                    _.each(this.tabs_tree, (group) => {
                        _.each(group, (table) => {
                            present.push(table.id);
                        });
                    });
                }
                this.avails = present;
            },
        },
        mounted() {
            this.fillAvails();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .check-wrap {
        margin-left: 10px;

        label {
            margin: 0;
        }
    }
    .check-wrap--sub {
        margin-left: 30px;
    }
</style>