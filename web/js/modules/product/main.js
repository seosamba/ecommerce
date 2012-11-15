define([ './views/app' ], function(AppView){
	if (!window.console) {
		window.console = {
		    log: function(){ return false; }
		};
	}


    window.app = new AppView();
    $(function(){
        $(document).trigger('loaded.product');
    });
});