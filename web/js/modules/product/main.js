define([ './views/app' ], function(AppView){
	if (!window.console) {
		window.console = {
		log: function(){
				return false;
			}
		};
	}


    $(function(){
        window.app = new AppView();
        $(document).trigger('loaded.product');
    });
});