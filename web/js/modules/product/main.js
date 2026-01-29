define([ './views/app' ], function(AppView){
	if (!window.console) {
		window.console = {
		    log: function(){ return false; }
		};
	}


    window.app = new AppView();
    $(function(){
        $(document).trigger('loaded.product');
        jsProductTeaserUploader.bind('FileUploaded', function(uploader, file, info){
            var response = jQuery.parseJSON(info.response);
            if (typeof response.source !== 'undefined') {
                var newFileName = response.source.split(/[\\/]/).pop();
                var newSrc = $('#website_url').val()+'media/products/small/'+newFileName.toLowerCase();
                window.app.model.set('photo', 'products/' + newFileName.toLowerCase());
                $('#product-image').attr('src', newSrc);
                window.app.trigger('change');
                $('#progressbar').delay(800).fadeOut();
            }
        });
    });

});