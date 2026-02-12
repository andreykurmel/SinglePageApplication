
export class ReportVariable {

    /**
     *
     * @returns {{val: string, attr: string}}
     */
    static attrObject() {
        return {
            attr: '',
            val: '',
        };
    }

    /**
     *
     * @param reportVariable
     * @returns {{val: any, attr: string}[]}
     */
    static getAttributes(reportVariable) {
        let attrs = JSON.parse(reportVariable.additional_attributes);
        return _.filter(attrs, (item) => {
            return !!item.attr;
        });
    }

    /**
     *
     * @param reportVariable
     * @param attrs
     * @returns {string}
     */
    static attrsSet(reportVariable, attrs) {
        attrs = _.filter(attrs, (item) => {
            return !!item.attr;
        });
        return JSON.stringify(attrs);
    }

    /**
     *
     * @param reportVariable
     * @returns {{val: any, attr: string}[]}
     */
    static attrsPreview(reportVariable) {
        let preview = this.getAttributes(reportVariable);
        return _.join(
            _.map(preview, (i) => { return i.attr + ':' + i.val; }),
            '|'
        );
    }
}