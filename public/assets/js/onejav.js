/*
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

jQuery('button.load-more').on('click', function (el) {
    var jqxhr = jQuery.ajax({
        url: "/onejav/ajax",
        method: "GET",
        data: {
            date: jQuery('.daily-items').last().data('date')
        }
    })
        .done(function (data) {
            jQuery('.daily-items').after(data);
            if (lazyLoadInstance) {
                lazyLoadInstance.update();
            }
        })
        .fail(function () {
            console.log(el);
        })
        .always(function () {
            console.log(el);
        });
});