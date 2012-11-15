define([
	'backbone'
], function(Backbone){

    var customerRowView = Backbone.View.extend({
        template: $('#tableRowTemplate').template(),
        tagName: 'tr',
        events: {
            'click a.details': 'details',
            'change input[name^=select]': 'toggle'
        },
        initialize: function() {
            this.model.on('change:checked', this.render, this);
        },
        render: function(){
            $(this.el).html($.tmpl(this.template, this.model.toJSON()));
            return this;
        },
        details: function(){
            Toastr.StoreClientsWidget.showCustomerDetails(this.model.get('id'));
        },
        toggle: function(e) {
            this.model.set({checked: e.target.checked});
        }
    });

	return customerRowView;
});