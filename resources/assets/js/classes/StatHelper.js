
export class StatHelper {

    /**
     *
     * @param {Array} rows
     * @returns Number
     */
    static count(rows)
    {
        return rows.length;
    }

    /**
     *
     * @param {Array} rows
     * @param {String} field
     * @returns Number
     */
    static countUnique(rows, field)
    {
        let obj = _.groupBy(rows, field);
        return Object.keys(obj).length;
    }

    /**
     *
     * @param {Array} rows
     * @param {String} field
     * @returns Number
     */
    static min(rows, field)
    {
        let res = to_float( _.first(rows)[field] );
        _.each(rows, (r) => {
            if (res > to_float(r[field])) res = to_float(r[field]);
        });
        return res;
    }

    /**
     *
     * @param {Array} rows
     * @param {String} field
     * @returns Number
     */
    static max(rows, field)
    {
        let res = to_float( _.first(rows)[field] );
        _.each(rows, (r) => {
            if (res < to_float(r[field])) res = to_float(r[field]);
        });
        return res;
    }

    /**
     *
     * @param {Array} rows
     * @param {String} field
     * @returns Number
     */
    static sum(rows, field)
    {
        return _.reduce(rows, (res, r) => {
            return res + to_float(r[field]);
        }, 0);
    }

    /**
     *
     * @param {Array} rows
     * @param {String} field
     * @returns Number
     */
    static avg(rows, field)
    {
        return this.sum(rows, field) / rows.length;
    }

    /**
     *
     * @param {Array} rows
     * @param {String} field
     * @returns Number
     */
    static mean(rows, field)
    {
        return (this.max(rows, field) + this.min(rows, field)) / 2;
    }

    /**
     *
     * @param {Array} rows
     * @param {String} field
     * @returns Number
     */
    static variance(rows, field)
    {
        let $avg = this.avg(rows, field);
        let $res = 0;
        _.each(rows, (el) => {
            $res += Math.pow($avg - to_float(el[field]), 2);
        });
        return $res / rows.length;
    }

    /**
     *
     * @param {Array} rows
     * @param {String} field
     * @returns Number
     */
    static std(rows, field)
    {
        return Math.sqrt( this.variance(rows, field) );
    }

}