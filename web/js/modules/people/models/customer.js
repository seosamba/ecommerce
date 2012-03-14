define([
	'Underscore',
	'Backbone',
    'modules/common/collections/dummy'
], function(_, Backbone, DummyCollection){

    var CustomerModel = Backbone.Model.extend({
        urlRoot: $('#website_url').val()+'plugin/shopping/run/getdata/type/customer/',
        initialize: function(){

        },
        parse: function(response){
            if (response.hasOwnProperty('addresses')){
                response.addresses = new DummyCollection(response.addresses);
            }
            console.log(response);
            return response;
        }
    });
	return CustomerModel;
});