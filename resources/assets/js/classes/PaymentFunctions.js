
export class PaymentFunctions {

    static updateUser(user) {
        axios.put('/ajax/user', {
            recurrent_pay: Number(user.recurrent_pay),
            pay_method: String(user.pay_method),
            use_credit: Number(user.use_credit),
            selected_card: Number(user.selected_card) || null,
        }).then(({ data }) => {
        }).catch(errors => {
            Swal('', getErrors(errors));
        });
    }

}