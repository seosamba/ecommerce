define([
	'backbone'
], function(Backbone){

    var supplierDetailsView = Backbone.View.extend({
        template: $('#supplierDetailsTemplate').template(),
        tagName: 'tr',
        events: {},
        render: function(){
            var data = {};
            $(this.el).html($.tmpl(this.template, data));
            return this;
        }
    });

	return supplierDetailsView;
});