
export class JsTree {

    /**
     *
     * @param tree
     */
    static select_all(tree) {
        if (tree && tree.state && tree.children) {
            tree.state.selected = true;
            _.each(tree.children, (item) => {
                this.select_all(item);
            });
        }
    }

    /**
     *
     * @param path
     * @returns {string}
     */
    static get_no_domain(path) {
        try {
            let pathDomain = (new URL(path)).host.split('.')[0];
            let curDomain = window.location.host.split('.')[0];
            if (pathDomain !== curDomain) {
                window.location.href = path;
                return;
            }
        } catch (e) {}

        //remove domain
        let nodomain = path;
        try {
            nodomain = (new URL(nodomain)).pathname;
        } catch (e) {}

        return nodomain;
    }
}