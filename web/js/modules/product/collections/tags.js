define([
	'Underscore',
	'Backbone',
	'modules/product/models/tag'
], function(_, Backbone, TagModel){
	
	var TagsList = Backbone.Collection.extend({
		model: TagModel,
		url: '/plugin/shopping/run/getdata/type/tags/id/',
		exists: function(name){
			return this.find(function(tag){return tag.get('name').toLowerCase() == name.toLowerCase();});
		}
	});
	
	return TagsList;
});