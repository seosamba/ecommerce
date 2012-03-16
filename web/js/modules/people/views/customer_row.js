define([
	'Underscore',
	'Backbone'
], function(_, Backbone){

    var customerRowView = Backbone.View.extend({
        template: $('#tableRowTemplate').template(),
        tagName: 'tr',
        events: {
            'click a.details': 'details'
        },
        render: function(){
            $(this.el).html($.tmpl(this.template, this.model.toJSON()));
            return this;
        },
        details: function(){
            app.showCustomerDetails(this.model.get('id'));
        }
    });

	return customerRowView;
});