define([
	'underscore',
	'backbone',
    '../models/country'
], function(_, Backbone, CountryModel){
	var countriesCollection = Backbone.Collection.extend({
        url: function(){
            return $('#website_url').val()+'api/store/geo/type/country/';
        },
        model: CountryModel,
        findByCode: function(countryCode){
            return this.find(function(c){ return c.get('country') === countryCode; });
        }
    })

	return countriesCollection;
});