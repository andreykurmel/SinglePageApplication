
export class MapPosition {

    static empty(table_id, object_id, type) {
        return {
            table_id: Number(table_id),
            user_id: window.vueRootApp.user ? window.vueRootApp.user.id : null,
            object_type: type,
            object_id: Number(object_id),
            pos_x: 0,
            pos_y: 0,
            opened: 1,
            used_only: 1,
            changed: 0,
            visible: 1,
        };
    }

    /**
     * Update or create a backend position
     */
    static storePosition(position)
    {
        window.vueRootApp.sm_msg_type = 1;
        axios.post('/ajax/rc-maps', position)
        .then(({ data }) => {
            _.each(data, (val, key) => {
                position[key] = data[key];
            });
        }).catch(errors => {
            Swal('Info', getErrors(errors));
        }).finally(() => {
            window.vueRootApp.sm_msg_type = 0;
        });
    }

    /**
     * Delete backend positions
     */
    static deleteLayout(table_id, position)
    {
        return new Promise((resolve) => {
            window.vueRootApp.sm_msg_type = 1;
            axios.delete('/ajax/rc-maps', {
                params: {
                    table_id: table_id,
                    position: position,
                }
            })
            .then(({ data }) => {
                resolve(data);
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                window.vueRootApp.sm_msg_type = 0;
            });
        });
    }
}