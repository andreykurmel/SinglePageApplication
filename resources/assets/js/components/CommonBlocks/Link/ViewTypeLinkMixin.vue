<script>
export default {
    data: function () {
        return {
            popupViewType: '',
            viewTypeListing: 'Listing',
            viewTypeTable: 'Table',
            viewTypeBoards: 'Boards',
            viewTypeVvs: 'vVS',
            show_vtype: false,
        };
    },
    methods: {
        allTypes(link) {
            let allTypes = [];
            if (!link || link.popup_can_table || link.embd_table) allTypes.push(this.viewTypeTable);
            if (!link || link.popup_can_list || link.embd_listing) allTypes.push(this.viewTypeListing);
            if (!link || link.popup_can_board || link.embd_board) allTypes.push(this.viewTypeBoards);
            if ([1, 3].indexOf(this.$root.user.role_id) > -1) allTypes.push(this.viewTypeVvs);
            return allTypes;
        },
        viewsAreAvail(link) {
            link = link && link.length
                ? _.first(link)
                : (link || {});

            let vCount = 0;
            if (link.popup_can_table || link.embd_table) vCount++;
            if (link.popup_can_list || link.embd_listing) vCount++;
            if (link.popup_can_board || link.embd_board) vCount++;
            if ([1, 3].indexOf(this.$root.user.role_id) > -1) vCount++;
            return vCount > 1;
        },
        viewTypeIcon(type) {
            switch (type || this.popupViewType) {
                case this.viewTypeListing: return ['fa-th-list'];
                case this.viewTypeTable: return ['fa-th'];
                case this.viewTypeBoards: return ['fa-bars'];
                case this.viewTypeVvs: return ['fa-folder-open'];
            }
            return [''];
        },
        viewTypeTitle(type) {
            switch (type || this.popupViewType) {
                case this.viewTypeListing: return 'List';
                case this.viewTypeTable: return 'Table';
                case this.viewTypeBoards: return 'Board';
                case this.viewTypeVvs: return 'vVS';
            }
            return '';
        },
        viewTypeTooltip(type) {
            switch (type || this.popupViewType) {
                case this.viewTypeListing: return 'Switch to List display';
                case this.viewTypeTable: return 'Switch to Table display';
                case this.viewTypeBoards: return 'Switch to Board display';
                case this.viewTypeVvs: return 'Switch to vVS display';
            }
            return '';
        },
    },
}
</script>
