
export class MomentTzHelper {

    /**
     *
     * @returns {*[]}
     */
    static timezones() {
        return _.map(moment.tz.names(), (name) => {
            let tz = moment().tz(name);

            return {
                name: name,
                full: name + ' (' + tz.zoneAbbr() + ' ' + tz.format('Z') + ')',
            };
        });
    }
}