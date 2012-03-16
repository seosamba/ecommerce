define([
	'Underscore',
	'Backbone'
], function(_, Backbone){

    var CustomerModel = Backbone.Model.extend({
        urlRoot: $('#website_url').val()+'plugin/shopping/run/getdata/type/customer/',
        initialize: function(){

        },
        parse: function(response){

            var AddressModel = Backbone.Model.extend({}),
                AddressCollection = Backbone.Collection.extend({
                    model: AddressModel
                });
            if (response.hasOwnProperty('addresses')){
                response.addresses = new AddressCollection(response.addresses);
            }
            console.log(response);
            return response;
        }
    });
	return CustomerModel;
});