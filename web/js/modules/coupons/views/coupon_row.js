define([
	'backbone',
    'text!../templates/coupon_row.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(Backbone, RowTemplate, i18n){

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
            showConfirmCustom(_.isUndefined(i18n['Are you sure?'])?'Are you sure?':i18n['Are you sure?'], _.isUndefined(i18n['Yes'])?'Yes':i18n['Yes'], _.isUndefined(i18n['No'])?'No':i18n['No'], function(){
                model.destroy();
            });
        }
    });

    return CouponRowView;
});