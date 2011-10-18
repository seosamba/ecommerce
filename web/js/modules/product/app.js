define([
  'libs/underscore/underscore', 
  'libs/backbone/backbone',
  'modules/product/router', // Request router.js
], function(_, Backbone, Router ){
	var initialize = function(){
		Router.initialize();
	}

	return { 
		initialize: initialize
	};
});