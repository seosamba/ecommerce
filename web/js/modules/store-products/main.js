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

//    $(function(){
        Toastr.StoreProductsWidget = new AppView();
//    });
    $(function(){
        alert(123);
    })
    return Toastr;
});