define([
  'Underscore',
  'Backbone',
  'modules/people/views/app'
], function(_, Backbone, AppView ){
	if (!window.console) {
		window.console = {
		log: function(){
				return false;
			}
		};
	}

	var initialize = function(){
		app = new AppView();
	}

	return { 
		initialize: initialize
	};
});