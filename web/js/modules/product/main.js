define([ './views/app' ], function(AppView){
	if (!window.console) {
		window.console = {
		    log: function(){ return false; }
		};
	}


    window.app = new AppView();
    $(function(){
        $(document).trigger('loaded.product');
        jsProductTeaserUploader.bind('FileUploaded', function(uploader, file){
            newSrc = $('#website_url').val()+'media/products/small/'+file.name;
            window.app.model.set('photo', 'products/' + file.name);
            $('#product-image').attr('src', newSrc);
            window.app.trigger('change');
            $('#progressbar').delay(800).fadeOut();
        });
    });

});