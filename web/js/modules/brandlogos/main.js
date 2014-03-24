/**
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
require.config({
    paths: {
        'underscore': '../../libs/underscore/underscore-min',
        'backbone'  : '../../libs/backbone/backbone-min',
        'text'  : '../../libs/require/text'
    },
    shim: {
        underscore: { exports: '_' },
        backbone: {
            deps: ['underscore'],
            exports: 'Backbone'
        }
    }
});

require([
    './views/app'
], function(AppView) {

    if (!window.Toastr){
        window.Toastr = {}
    }

    $(function(){

        Toastr.BrandLogos = new AppView();

        jsBrandLogoUploader.bind('BeforeUpload', function(uploader, file) {
            $('#progressbar').fadeIn().progressbar({value: 0});
            //var ext = /\.jpe?g|\.gif|\.png$/i.exec(file.name);
            file.name = Toastr.BrandLogos.filename+'.png';
        });
        jsBrandLogoUploader.bind('UploadProgress', function(uploader, file) {
            $('#progressbar').progressbar({value: file.percent});
            $('#progressbar .value').text(file.percent);
        })
        jsBrandLogoUploader.bind('FileUploaded', function(uploader, file) {
            var timestamp = new Date().getTime();
            var imageName = file.name.replace(/[^\w\d._]/gi, '-');
            var newSrc = $('#website_url').val()+'media/brands/small/'+imageName.toLowerCase()+'?'+timestamp,
                brand = Toastr.BrandLogos.brands.find(function(brand){ return brand.get('name') === Toastr.BrandLogos.filename; });
            if (brand !== undefined){
                brand.set('src', newSrc);
            }
            brand.trigger('change');
            $('#progressbar').delay(800).fadeOut();
        });

        $.when(
            Toastr.BrandLogos.fetchImages()
        ).done(function(){
            Toastr.BrandLogos.brands.fetch();
        });
    });
});