define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/category'
], function(_, Backbone, CategoryModel){
	
	var CategoryList = Backbone.Collection.extend({
		model: CategoryModel,
		url: '/plugin/shopping/run/getdata/type/categories/',
		exists: function(name){
			return this.find(function(category){return category.get('name').toLowerCase() == name.toLowerCase();});
		},
		filterForAutocomplete: function(){
			
		}
	});
	
	return CategoryList;
});