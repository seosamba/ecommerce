define([
	'Underscore',
	'Backbone',
    'modules/common/models/country'
], function(_, Backbone, CountryModel){
	var countriesCollection = Backbone.Collection.extend({
        url: $('#website_url').val()+'plugin/shopping/run/getdata/type/countryList/',
        model: CountryModel,
        findByCode: function(countryCode){
            return this.find(function(c){ return c.get('country') === countryCode; });
        }
    })

	return countriesCollection;
});