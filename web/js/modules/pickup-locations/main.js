define([ './views/app' ], function(AppView){
	if (!window.console) {
		window.console = {
		    log: function() {
				return false;
			}
		};
	}

    if (!window.Toastr) {
        window.Toastr = {}
    }

    $(function() {
        Toastr.StorePickupLocation = new AppView();

        jsPickupLogoUploader.bind('BeforeUpload', function(uploader, file) {
            $('#progressbar').fadeIn().progressbar({value: 0});
            file.name = 'catid'+$('.ui-state-active').find('a').data('category-id')+'.'+file.name.split('.').pop();
            jsPickupLogoUploader.settings.resize = {
                width : 32,
                quality : 90
            }
        });
        jsPickupLogoUploader.bind('UploadProgress', function(uploader, file) {
            $('#progressbar').progressbar({value: file.percent});
            $('#progressbar .value').text(file.percent);
        });
        jsPickupLogoUploader.bind('FileUploaded', function(uploader, file) {
            var websiteUrl = $('#website_url').val();
            var timestamp = new Date().getTime();
            var currentCategoryId = $('.ui-state-active').find('a').data('category-id');
            var category = Toastr.StorePickupLocation.PickupLoationCategories.categories.get(currentCategoryId);
            var imageName = file.name;
            category.set('img', imageName);
            category.save(category, {
                success: function(model, response) {
                var src = websiteUrl+'media/'+$('#things-select-folder').val()+'/small/'+imageName+'?'+timestamp;
                $('.uploader-category-logo img').attr('src', src);
            }});
            $('#progressbar').delay(800).fadeOut();
        });
    });

    return Toastr;
});