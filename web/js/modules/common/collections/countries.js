define([
	'Underscore',
	'Backbone',
    'modules/common/models/country'
], function(_, Backbone, CountryModel){
	var countriesCollection = Backbone.Collection.extend({
        url: $('#websiteUrl').val()+'/plugin/shopping/run/getdata/type/countryList/',
        model: CountryModel,
        findByCode: function(countryCode){
            var a = this.find(function(c){
                return c.get('country') === countryCode;
            });
            return a;
        }
    })

	return countriesCollection;
});