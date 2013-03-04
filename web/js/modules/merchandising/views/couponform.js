define([
	'backbone'
], function(Backbone){
    var CouponFormView = Backbone.View.extend({
        el: $('#edit-coupon'),
        events: {
            'submit': 'submit',
            'change :input[data-validator]': 'validate',
            'change [data-action=render]': 'render',
            'click #genCoupon': 'genCoupon'
        },
        templates: {

        },
        initialize: function(){
            $('#startDate').datepicker({
                showOtherMonths: true,
                selectOtherMonths: false,
                dateFormat: 'yy-mm-dd',
                onClose: function(selectedDate){
                    $('#endDate').datepicker("option", "minDate", selectedDate);
                }
            });
            $('#endDate').datepicker({
                showOtherMonths: true,
                selectOtherMonths: false,
                dateFormat: 'yy-mm-dd',
                onClose: function(selectedDate){
                    $('#startDate').datepicker("option", "maxDate", selectedDate);
                }
            });
        },
        render: function(){
            var couponActionTmpl = '',
                couponType       = $('#coupon-type').val();
            switch (couponType){
                case 'discount':
                case 'freeshipping':
                    couponActionTmpl = _.template($('#actionDiscountFreeshippingTmpl').html(), {type: couponType});
                    break;
                default:
                    break;
            }
            $('#coupon-action').html(couponActionTmpl);

            if ($('#enable-coupon-limit').attr('checked')) {
                $('#scope').removeAttr('disabled');
            } else {
                $('#scope').attr('disabled', 'disabled');
            }
        },
        submit: function(e){
            e.preventDefault();
            var self = this,
                form = $(e.currentTarget),
                isValid = true;

            _.each(form.find('.required'), function(el){
                if (!$(el).val()){
                    isValid = false;
                }
            });

            if (!isValid){
                showMessage('Missing required field', true);
                return false;
            }

            $.ajax({
                url: this.app.coupons.url(),
                data: this.$el.serialize(),
                type: 'POST',
                dataType: 'json',
                success: function(response){
                    form.trigger('reset');
                    self.app.coupons.add(response);
                }
            });
        },
        validate: function(e){
            var el = $(e.currentTarget);
            console.log(el.data());
        },
        genCoupon: function(e){
            e.preventDefault();
            var string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
                code = '';

            $('#code').val(code);

            for(var i=0; i<10; i++){
                code += string[Math.floor(Math.random()*string.length)];
            }

            $('#code').val(code);
        }
    });

    return CouponFormView;
});