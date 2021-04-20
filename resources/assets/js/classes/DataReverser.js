
export class DataReverser {
    /**
     *
     */
    constructor() {
        this.revs = [];
        this.olds = null;
        this.news = null;
    }

    /**
     *
     * @param tableRows
     * @param start
     * @param length
     */
    pre_change(tableRows, start, length)
    {
        if (tableRows && tableRows.length && !isNaN(start)) {
            let finish = to_float(length) || 0;
            this.olds = {};
            _.each(tableRows, (row, i) => {
                if (start <= i && i <= start+finish) {
                    this.olds[i] = _.cloneDeep(row);
                }
            });
        } else {
            this.olds = null;
        }
    }

    /**
     *
     * @param table_id
     * @param tableRows
     */
    after_change(table_id, tableRows)
    {
        if (tableRows && tableRows.length && this.olds) {
            this.news = {};

            _.each(this.olds, (row, i) => {
                if (tableRows[i]) {
                    this.news[i] = tableRows[i];
                }
            });

            this.revs.push({
                table_id: table_id,
                news: this.news,
                olds: this.olds,
            });
            if (this.revs.length > 3) {
                this.revs.shift();
            }

            this.news = null;
        }
        this.olds = null;
    }

    /**
     *
     * @param table_id
     * @returns {Array}
     */
    do_reverse(table_id)
    {
        let revs = [];
        if (this.revs.length) {
            let obj = this.revs.pop();
            if (table_id == obj.table_id) {
                _.each(obj.news, (row, i) => {
                    if (obj.olds[i]) {
                        obj.news[i] = obj.olds[i];
                        revs.push(obj.news[i]);
                    }
                });
            }
        }
        return revs;
    }
}