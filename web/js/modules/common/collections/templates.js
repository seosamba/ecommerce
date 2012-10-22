define([ 'backbone' ], function(Backbone){
    var templateModel       = Backbone.Model.extend({});
	var templatesCollection = Backbone.Collection.extend({
        url: $('#website_url').val() + 'storeapi/v1/templates/',
        model: templateModel
    });
	return templatesCollection;
});