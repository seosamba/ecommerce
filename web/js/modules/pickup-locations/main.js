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
        });
        jsPickupLogoUploader.bind('UploadProgress', function(uploader, file) {
            $('#progressbar').progressbar({value: file.percent});
            $('#progressbar .value').text(file.percent);
        })
        jsPickupLogoUploader.bind('FileUploaded', function(uploader, file) {
            var websiteUrl = $('#website_url').val(),
                catId      = $('.ui-state-active').find('a').data('category-id'),
                catName    = $('.change-category-label').val();
            $.ajax({
                url:      websiteUrl+'api/store/pickuplocationcategories/id/'+catId+'/categoryName/'+catName
                    +'/categoryImg/'+file.name,
                type:     'PUT',
                dataType: 'json',
                success:   function(response) {
                    var src = websiteUrl+'media/'+$('#things-select-folder').val()+'/small/'+file.name;

                    var currentCategoryId = $('.ui-state-active').find('a').data('category-id');
                    Toastr.StorePickupLocation.PickupLoationCategories.categories.get(currentCategoryId).set('img', file.name);

                    $('.uploader-category-logo img').attr('src', src);
                }
            });
            $('#progressbar').delay(800).fadeOut();
        });
    });

    return Toastr;
});