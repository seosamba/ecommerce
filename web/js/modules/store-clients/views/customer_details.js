/**
 * .
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
define([
	'backbone'
], function(Backbone){

    var customerDetailsView = Backbone.View.extend({
        template: $('#customerDetailsTemplate').template(),
        tagName: 'tr',
        events: {},
        render: function(){
            var data = {};
            if (this.model.has('defaultbillingaddressid')){
                data.defBillingAddress = this.model.addresses.get(this.model.get('defaultbillingaddressid'))
            }
            if (this.model.has('defaultshippingaddressid')){
                data.defShippingAddress = this.model.addresses.get(this.model.get('defaultshippingaddressid'))
            }
            $(this.el).html($.tmpl(this.template, data));
            return this;
        }
    });

	return customerDetailsView;
});