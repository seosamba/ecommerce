define([
	'backbone',
    'text!../templates/coupon_row.html'
], function(Backbone, RowTemplate){

    var CouponRowView = Backbone.View.extend({
        tagName: 'tr',
        className: 'coupon-row',
        template: _.template(RowTemplate),
        events: {
            'click a[data-role=delete]': 'deleteAction'
        },
        initialize: function(){
            this.model.on('change', this.render, this);
            this.model.on('remove', this.remove, this);
        },
        render: function(){
            $(this.el).html(this.template({coupon: this.model}));
            return this;
        },
        deleteAction: function(){
            var model = this.model;
            showConfirm('Are you sure?', function(){
                model.destroy();
            });
        }
    });

    return CouponRowView;
});