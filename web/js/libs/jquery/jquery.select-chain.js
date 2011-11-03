(function ($) {
    $.fn.selectChain = function (options) {
        var defaults = {
            key: "id",
            value: "label"
        };
        
        var settings = $.extend({}, defaults, options);
        
        if (!(settings.target instanceof $)) settings.target = $(settings.target);
		
        return this.each(function () {
            var $$ = $(this);
            
            $$.change(function () {
                var data = null;
                if (typeof settings.data == 'string') {
                    data = settings.data + '&' + this.name + '=' + $$.val();
                } else if (typeof settings.data == 'object') {
                    data = settings.data;
                    data[this.name] = $$.val();
                }
                
                settings.target.empty();
                
                $.ajax({
                    url: settings.url,
                    data: data,
                    type: (settings.type || 'get'),
                    dataType: 'json',
                    success: function (response) {
//                        var options = [], i = 0, o = null;
                        
						$.each(response, function(key, value){
							var $option;
							$option = $('<option>', {value: key, 'label': value}).html(value);
							$option.appendTo(settings.target);
						});

			// hand control back to browser for a moment
			setTimeout(function () {
			    settings.target
                                .find('option:first')
                                .attr('selected', 'selected')
                                .parent('select')
                                .trigger('change');
			}, 0);
                    },
                    error: function (xhr, desc, er) {
						window.console && console.log("an error occurred");
                    }
                });
            });
        });
    };
})(jQuery);