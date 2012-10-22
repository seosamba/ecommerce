define([
    'underscore',
    'backbone',
    './views/app'
    //'../zones/collections/zones'
], function(_, Backbone, AppView){
	if (!window.console) {
		window.console = {
		log: function(){
				return false;
			}
		};
	}
    window.app = new AppView();
    $(function(){
        $(document).trigger('taxes:loaded');
    });

});