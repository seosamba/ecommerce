define([
	'backbone',
    '../../product/collections/brands',
    './brandlogo'
], function(Backbone, BrandsCollection, BrandView){

    var brandLogosListView = Backbone.View.extend({
        el: $('#manage-logos'),
        filename: '',
        events: {},
        initialize: function(){
            $('#progressbar').progressbar();
            this.brands = new BrandsCollection();
            this.brands.on('reset', this.render, this);
        },
        render: function(){
            var self = this;
            this.$('.brand-list').empty();
            this.brands.each(function(brand, i){
                if (!brand.has('src')){
                    var image = self.images.find(function(img){
                        var brandName = brand.get('name').replace(/[^\w\d._]/gi, '-').toLowerCase(),
                            imgName = img.get('name').toLowerCase();
                            regExp = new RegExp('^'+brandName+'\\.(png|jpe?g|gif)$')
                        return !!regExp.test(imgName);
                    });
                    brand.set({src: ( _.isObject(image) ? image.get('src').replace('product', 'small') : $('#website_url').val()+'system/images/noimage.png')});
                }
                var view = new BrandView({model: brand});
                view.render().$el
                    .on('click', function(){
                        self.filename = brand.get('name');
                        $('#brand-logo-uploader-pickfiles').trigger('click');
                    })
                    .appendTo('#manage-logos .brand-list')
                    .addClass(((i+1)%6==0)?'omega':'');
            })
        },
        fetchImages: function() {
            var self = this;
            return $.post(
                $('#website_url').val()+'backend/backend_media/getdirectorycontent',
                {folder: $('#things-select-folder').val()},
                function(response){ self.images = new Backbone.Collection(response.imageList); }
            );
        },
        triggerUpload: function() {
            console.log(arguments, this);
            this.filename =
            $('#brand-logo-uploader-pickfiles').trigger('click');
        }
    })
	
	return brandLogosListView;
});