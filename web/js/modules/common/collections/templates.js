define([ 'backbone' ], function(Backbone){
    var templateModel       = Backbone.Model.extend({});
	var templatesCollection = Backbone.Collection.extend({
        url: function(){ return $('#website_url').val() + 'api/store/templates/'; },
        model: templateModel
    });
	return templatesCollection;
});