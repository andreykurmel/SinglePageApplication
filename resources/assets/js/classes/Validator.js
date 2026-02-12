
export class Validator {

    static ruleObject() {
        return {
            rule: '',
            val: '',
            err: '',
        };
    }

    /**
     *
     * @param tableHeader
     * @param rowValue
     * @returns {string}
     */
    static check(tableHeader, rowValue) {
        let errors = [];
        _.each(this.getRules(tableHeader), (item) => {
            if (this._applyRule(tableHeader.f_type, item.rule, item.val, rowValue)) {
                if (item.err) {
                    errors.push(item.err);
                } else {
                    errors.push(item.rule + ':' + item.val);
                }
            }
        });
        return errors.join(', ');
    }

    /**
     *
     * @param tableHeader
     * @returns {{val: any, rule: string, err: string}[]}
     */
    static getRules(tableHeader) {
        let rules = tableHeader.validation_rules ? JSON.parse(tableHeader.validation_rules) : [];
        return _.filter(rules, (item) => {
            return !!item.rule;
        });
    }

    /**
     *
     * @param tableHeader
     * @param rules
     * @returns {string}
     */
    static rulesSet(tableHeader, rules) {
        rules = _.filter(rules, (item) => {
            return !!item.rule;
        });
        return JSON.stringify(rules);
    }

    /**
     *
     * @param tableHeader
     * @returns {{val: any, rule: string}[]}
     */
    static rulesPreview(tableHeader) {
        let preview = this.getRules(tableHeader);
        return _.join(
            _.map(preview, (i) => { return i.rule + ':' + i.val; }),
            '|'
        );
    }

    /**
     *
     * @param headerType
     * @param rule
     * @param ruleVal
     * @param rowValue
     * @returns {boolean}
     * @private
     */
    static _applyRule(headerType, rule, ruleVal, rowValue) {
        switch (rule) {
            case 'Max': return this._max(ruleVal, rowValue);
            case 'Min': return this._min(ruleVal, rowValue);
            case 'Email': return this._email(rowValue);
            case 'Regex': return this._regex(ruleVal, rowValue);
            default: return false;
        }
    }

    /**
     *
     * @param ruleVal
     * @param rowValue
     * @returns {boolean}
     * @private
     */
    static _max(ruleVal, rowValue) {
        if (isNaN(rowValue)) {
            return String(rowValue).length > Number(ruleVal);
        } else {
            return Number(rowValue) > Number(ruleVal);
        }
    }

    /**
     *
     * @param ruleVal
     * @param rowValue
     * @returns {boolean}
     * @private
     */
    static _min(ruleVal, rowValue) {
        if (isNaN(rowValue)) {
            return String(rowValue).length < Number(ruleVal);
        } else {
            return Number(rowValue) < Number(ruleVal);
        }
    }

    /**
     *
     * @param rowValue
     * @returns {boolean}
     * @private
     */
    static _email(rowValue) {
        return !String(rowValue)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    }

    /**
     *
     * @param ruleVal
     * @param rowValue
     * @returns {boolean}
     * @private
     */
    static _regex(ruleVal, rowValue) {
        let reg = new RegExp(ruleVal, 'gi');
        return !String(rowValue).match(reg);
    }
}