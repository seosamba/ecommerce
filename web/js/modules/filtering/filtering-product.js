/*jslint nomen: true*/
/*global $, jQuery, _, Backbone */
$(function () {
    var $widgets = $('.plugin-filtering-widget');
    $widgets.on('click', 'button', function (e) {
        var $widget = $(e.currentTarget).closest('.plugin-filtering-widget'),
            data;
        data = $widget.find('input:checkbox:checked').serialize();

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: data,
            success: function () {
                console.log(arguments);
            }
        });
    });
    $widgets.on('change', 'input:checkbox[name^=hide]', function (e) {
        var $subList = $(e.currentTarget).closest('li').find('ul input:checkbox');
        console.log($subList);
        $subList.prop('checked', e.currentTarget.checked)
            .prop('disabled', e.currentTarget.checked);
    });
    $widgets.find('div.slider').each(function () {
        var min = Math.floor(parseFloat($(this).data('min'))),
            max = Math.ceil(parseFloat($(this).data('max')));
        $(this).slider({
            range: true,
            min: min,
            max: max,
            values: [ min, max ]
        });
    });
});