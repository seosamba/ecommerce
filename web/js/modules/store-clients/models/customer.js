define([
	'backbone'
], function(Backbone){

    var CustomerModel = Backbone.Model.extend({
        urlRoot: function(){ return $('#website_url').val()+'api/store/customers/id/'; },
        parse: function(response){

            var AddressModel = Backbone.Model.extend({}),
                AddressCollection = Backbone.Collection.extend({
                    model: AddressModel
                });
            if (response.hasOwnProperty('addresses')){
                response.addresses = new AddressCollection(response.addresses);
            }
            return response;
        }
    });
	return CustomerModel;
});