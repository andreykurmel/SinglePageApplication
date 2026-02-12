<template>
    <div class="tournament-tab flex flex--col">
        <div v-if="can_tour" class="flex" style="padding: 10px">
            <div v-for="st in stages" v-html="st" :style="roundStl"></div>
        </div>

        <div v-if="emptySett" class="flex flex--center full-height" style="font-size: 2em;">Please fill all settings!</div>
        <div v-else-if="can_tour" ref="tournament_ref" class="full-height"></div>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    export default {
        name: "TournamentTab",
        mixins: [
        ],
        components: {
        },
        data: function () {
            return {
                can_tour: false,
                stages: [],
            }
        },
        props:{
            tableMeta: Object,
            tableTournament: Object,
            tableRows: Array,
            isVisible: Boolean,
        },
        watch: {
            isVisible(val) {
                if (val) {
                    this.redrTourn();
                }
            },
        },
        computed: {
            emptySett() {
                return !this.tableTournament.teamhomename_fld_id
                    || !this.tableTournament.teamhomegoals_fld_id
                    || !this.tableTournament.teamguestname_fld_id
                    || !this.tableTournament.teamguestgoals_fld_id
                    || !this.tableTournament.stage_fld_id;
            },
            roundStl() {
                return {
                    width: (this.tableTournament.p_team_width + this.tableTournament.p_goal_width) + 'px',
                    marginRight: (this.tableTournament.p_round_margin) + 'px',
                    textAlign: 'center',
                    background: '#EEE',
                };
            },
        },
        methods: {
            changeActab(val) {
                this.acttab = '';
                this.$nextTick(() => {
                    this.acttab = val;
                });
            },
            redrTourn() {
                if (!this.isVisible) {
                    return;
                }
                this.can_tour = false;
                this.$nextTick(() => {
                    this.can_tour = true;
                    this.$nextTick(() => {
                        this.findStages();
                        this.buildTournam();
                    });
                });
            },
            findStages() {
                let homename = _.find(this.tableMeta._fields, {id: Number(this.tableTournament.teamhomename_fld_id)});
                let guestname = _.find(this.tableMeta._fields, {id: Number(this.tableTournament.teamguestname_fld_id)});
                let stage = _.find(this.tableMeta._fields, {id: Number(this.tableTournament.stage_fld_id)});
                if (!homename || !guestname || !stage) {
                    return;
                }

                let rows = _.filter(this.tableRows, (row) => {
                    return row[homename.field] && row[guestname.field] && row[stage.field];
                });
                rows = _.groupBy(rows, stage.field);

                this.stages = [];
                _.each(rows, (group) => {
                    let rw = _.first(group);
                    this.stages.push( SpecialFuncs.showFullHtml(stage, rw, this.tableMeta) );
                });
            },
            buildTournam() {
                if (this.$refs.tournament_ref) {
                    let homeHdr = _.find(this.tableMeta._fields, {id: Number(this.tableTournament.teamhomename_fld_id)});
                    let guestHdr = _.find(this.tableMeta._fields, {id: Number(this.tableTournament.teamguestname_fld_id)});

                    let homename = this.getTrFld(this.tableTournament.teamhomename_fld_id, 'HomeName');
                    let homegoals = this.getTrFld(this.tableTournament.teamhomegoals_fld_id, 'HomeGoals');
                    let guestname = this.getTrFld(this.tableTournament.teamguestname_fld_id, 'GuestName');
                    let guestgoals = this.getTrFld(this.tableTournament.teamguestgoals_fld_id, 'GuestGoals');
                    let stage = this.getTrFld(this.tableTournament.stage_fld_id, 'Stage');
                    if (!homename || !homegoals || !guestname || !guestgoals || !stage) {
                        return;
                    }

                    let rows = _.filter(this.tableRows, (row) => {
                        return row[homename] && row[guestname] && row[stage];
                    });
                    rows = _.groupBy(rows, stage);

                    //build sorted tree from the end
                    let teams = [];
                    let results = [];
                    let teamsorder = [];
                    let stages = _.keys(rows).reverse();

                    stages.forEach((stg) => {
                        results.push([]);
                    });

                    //Prepare data (teams and results).
                    stages.forEach((stage, i) => {
                        if (i == (stages.length-1)) {
                            let winners = [];
                            _.each(teamsorder, (ord) => {
                                let rrow = _.find(rows[stage], (r) => {
                                    return r[homename] == ord || r[guestname] == ord;
                                });
                                if (rrow) {
                                    teams.push( this.teamsObject(rrow, homename, guestname, homeHdr, guestHdr) );
                                    winners.push( this.bracketResult(rrow, homegoals, guestgoals) );
                                }
                            });
                            results[0] = winners;
                        } else {
                            _.each(rows[stage].slice(0, Math.pow(2,i)), (row) => {
                                if (teamsorder.indexOf(row[homename]) == -1) {
                                    let aftr = teamsorder.indexOf(row[guestname]);
                                    aftr > -1
                                        ? teamsorder.splice(aftr, 0, row[homename])
                                        : teamsorder.push(row[homename]);
                                }
                                if (teamsorder.indexOf(row[guestname]) == -1) {
                                    let bfr = teamsorder.indexOf(row[homename]);
                                    bfr > -1
                                        ? teamsorder.splice(bfr+1, 0, row[guestname])
                                        : teamsorder.push(row[guestname]);
                                }
                            });

                            let winners = [];
                            for(let i = 0; i < teamsorder.length; i+=2) {
                                let rw = _.find(rows[stage], (r) => {
                                    return r[homename] == teamsorder[i] && r[guestname] == teamsorder[i+1];
                                });
                                if (rw) {
                                    winners.push( this.bracketResult(rw, homegoals, guestgoals) );
                                }
                                let rev = _.find(rows[stage], (r) => {
                                    return r[guestname] == teamsorder[i] && r[homename] == teamsorder[i+1];
                                });
                                if (rev) {
                                    winners.push( this.bracketResult(rev, guestgoals, homegoals) );
                                }
                            }
                            results[stages.length-i-1] = winners;
                        }
                    });

                    //Play for 3rd place.
                    let win = rows[stages[0]];
                    if (win && win.length === 2) {
                        if (teamsorder.splice(0, teamsorder.length/2).indexOf(win[1][homename]) > -1) {
                            results[stages.length - 1].push( this.bracketResult(win[1], homegoals, guestgoals) );
                        } else {
                            results[stages.length - 1].push( this.bracketResult(win[1], guestgoals, homegoals) );
                        }
                    }

                    try {
                        $(this.$refs.tournament_ref).bracket({
                            teamWidth: this.tableTournament.p_team_width,
                            scoreWidth: this.tableTournament.p_goal_width,
                            matchMargin: this.tableTournament.p_match_margin,
                            roundMargin: this.tableTournament.p_round_margin,
                            init: {
                                teams,
                                results,
                            },
                            onMatchClick: (row_id) => {
                                this.$emit('row-clicked', row_id);
                            },
                            decorator: {
                                edit: function (container, data, doneCb) {
                                    return;
                                },
                                render: function (container, data, score, state) {
                                    switch(state) {
                                        case "empty-bye":
                                            container.append("No team")
                                            return;
                                        case "empty-tbd":
                                            container.append("TBD")
                                            return;

                                        case "entry-no-score":
                                        case "entry-default-win":
                                        case "entry-complete":
                                            if (data.flag) {
                                                container.append('<img src="/flags/'+data.flag+'" /> ');
                                            }
                                            container.append(data.name);
                                            return;
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        Swal('Info','Incorrect Data Input! ' + e.message);
                    }
                }
            },
            getTrFld(id, msg) {
                let fld = _.find(this.tableMeta._fields, {id: Number(id)});
                fld = fld ? fld.field : '';
                if (!fld) {
                    Swal("Empty "+msg+"!");
                }
                return fld;
            },
            bracketResult(row, firstKey, secondKey) {
                return [
                    _.trim(row[firstKey]),
                    _.trim(row[secondKey]),
                    row['id']
                ];
            },
            teamsObject(rrow, homename, guestname, homeHdr, guestHdr) {
                let hometeam = SpecialFuncs.showhtml(homeHdr, rrow, rrow[homename]);
                let guestteam = SpecialFuncs.showhtml(guestHdr, rrow, rrow[guestname]);
                return [
                    { name: hometeam, flag: this.flagByName(hometeam) },
                    { name: guestteam, flag: this.flagByName(guestteam) },
                ];
            },
            flagByName(name) {
                let country = _.find(this.$root.settingsMeta.countries_all, (cntr) => {
                    return cntr.name === name || cntr.full_name === name || cntr.capital === name || cntr.iso_3166_2 === name || cntr.iso_3166_3 === name;
                });
                return country ? country.flag : '';
            },
        },
        mounted() {
            this.redrTourn();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .tournament-tab {
        position: relative;
        height: 100%;
        background-color: #FFF;
        padding: 10px;
        overflow: auto;
    }
</style>

<style lang="scss">
    .jQBracket {
        .teamContainer {
            .label {
                font-size: 14px;
                font-weight: normal;
                line-height: 22px;
                color: #222;
            }
        }
    }
</style>