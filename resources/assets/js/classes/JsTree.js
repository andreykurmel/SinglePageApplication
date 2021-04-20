
export class JsTree {

    /**
     *
     * @param tree
     */
    static select_all(tree) {
        if (tree) {
            tree.state.selected = true;
            _.each(tree.children, (item) => {
                this.select_all(item);
            });
        }
    }
}