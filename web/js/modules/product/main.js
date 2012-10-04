define([ './router' ], function(Router){
	if (!window.console) {
		window.console = {
		log: function(){
				return false;
			}
		};
	}


    $(function(){
        var router =  new Router;
        Backbone.history.start();
        window.app = router.app;
    });
});