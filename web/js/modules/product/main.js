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
            newSrc = $('#website_url').val()+'media/products/small/'+file.name.replace(/\s+/g, '-').toLowerCase();
            window.app.model.set('photo', 'products/' + file.name.replace(/\s+/g, '-').toLowerCase());
            $('#product-image').attr('src', newSrc);
            window.app.trigger('change');
            $('#progressbar').delay(800).fadeOut();
        });
    });

});