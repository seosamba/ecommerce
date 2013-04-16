define([ './views/app' ], function(AppView){
	if (!window.console) {
		window.console = {
		log: function(){
				return false;
			}
		};
	}

    if (!window.Toastr){
        window.Toastr = {}
    }

    Toastr.StoreMerchandising = new AppView();

    return Toastr;
});