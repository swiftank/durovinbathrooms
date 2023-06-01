define([
    'jquery',
    'jquery/ui',
    'mage/tabs',
    'mgz.owlcarousel',
    'catalogAddToCart',
    'Magezon_TabsPro/js/swatch-renderer'
    ], function($){

        $.widget('tabspro.tabs', $.mage.tabs, {

        /**
         * Instantiate collapsible
         * @param element
         * @param index
         * @param active
         * @param disabled
         * @private
         */
         _instantiateCollapsible: function(element,index,active,disabled) {
            $(element).collapsible(
                $.extend({}, this.options, {
                    active: active,
                    disabled: disabled,
                    header: this.headers.eq(index),
                    content: this.contents.eq(index),
                    trigger: this.triggers.eq(index)}
                    ));
            if (active == true) {
                var targetE = $(element).find('a').eq(0);
                var id = $(targetE).attr('href');
                var owlcarousel = $(id).find('.owl-carousel[data-parent-id="' + id.replace('#', '') + '"]').eq(0);
                if (!targetE.data('tab-ajax')) {
                    this._OwlInit(owlcarousel);
                }
                this._loadContent(targetE);
            }
        },

        _loadContent: function (element) {
            var self = this;
            if (element.data('tab-ajax')) {
                var url = element.data('ajax-url');
                var tabId = element.data('tab-id');
                var blockId = element.data('block-id');
                if(url && tabId && blockId) {
                    $.ajax({
                        url: url,
                        type : "POST",
                        data : {
                            'tab_id': tabId,
                            'block_id': blockId
                        },
                        success: function(response) {
                            if (response['block_id']) {
                                var blockId = response['block_id'];
                                $(blockId).removeClass('ajaxloading');
                            }
                            if (response['status']) {
                                $(blockId + '.tabspro-ajax-loading').remove();
                                var formkey = $('input[name="form_key"]').val();

                                $(blockId).html(response.html);
                                $(blockId + ' input[name="form_key"]').val(formkey);
                                $(blockId).find('.owl-carousel[data-parent-id="' + blockId.replace('#', '') + '"]').each(function(index, el) {
                                    self._OwlInit($(this));
                                });
                            }
                        }
                    })
                }
            }
        },

        _calImageWrapperHeight(element) {
            element.find('.product-image-wrapper').each(function(index, el) {
                var imgE = $(this).find('.product-image-photo').eq(0);
                if (!imgE.hasClass('owl-lazy')) {
                    $(this).css('height', imgE.height());
                }
            });
        },

        _OwlInit: function(element) {
            var self = this;
            element.find('.product-image-container').css('width', '');
            element.find('.product-image-wrapper').css('padding', '');

            var config = {};
            if (element.data('autoplay') && element.data('autoplay') != 'undefined') {
                config['autoplay'] = element.data('autoplay');
            }
            if (element.data('nav') && element.data('nav') != 'undefined') {
                config['nav'] = element.data('nav');
            }
            if (element.data('autoplay-hover-pause') && element.data('autoplay-hover-pause') != 'undefined') {
                config['autoplayHoverPause'] = element.data('autoplay-hover-pause');
            }
            if (element.data('dots') && element.data('dots') != 'undefined') {
                config['dots'] = element.data('dots');
            } else {
                config['dots'] = false;
            }
            if (parseInt(element.data('autoplay-timeout'))>=0 != 'undefined') {
                config['autoplayTimeout'] = parseInt(element.data('autoplay-timeout'));
            }
            if (element.data('lazyload') && element.data('lazyload') != 'undefined') {
                config['lazyLoad'] = element.data('lazyload');
            }
            if (element.data('rtl') && element.data('rtl') != 'undefined') {
                config['rtl'] = element.data('rtl');
            }
            if (element.data('loop') && element.data('loop') != 'undefined') {
                config['loop'] = element.data('loop');
            }
            if (parseInt(element.data('margin'))>=0 && element.data('margin') != 'undefined') {
                config['margin'] = parseInt(element.data('margin'));
            }
            if (element.data('mousedrag') && element.data('mousedrag') != 'undefined') {
                config['mouseDrag'] = element.data('mousedrag');
            } else {
                config['mouseDrag'] = false;
            }
            if (element.data('touchdrag') && element.data('touchdrag') != 'undefined') {
                config['touchDrag'] = element.data('touchdrag');
            } else {
                config['touchDrag'] = false;
            }
            if (element.data('pulldrag') && element.data('pulldrag') != 'undefined') {
                config['pullDrag'] = element.data('pulldrag');
            } else {
                config['pullDrag'] = false;
            }
            config['responsive'] = {
                0: {'items': parseInt(element.data('device-0'))},
                480: {'items': parseInt(element.data('device-480'))},
                768: {'items': parseInt(element.data('device-768'))},
                960: {'items': parseInt(element.data('device-960'))},
                1024: {'items': parseInt(element.data('device-1024'))},
            };

            config['navText'] = ['',''];
            if (!element.hasClass('owl-loaded')) {
                if (element.data('lazyload') == true) {
                    element.find('.product-image-photo[data-lazyload="1"]').each(function(index, el) {
                        $(this).parents('.product-item-info').addClass('tabspro-lazyload');
                        $(this).addClass('owl-lazy');
                    });
                }
                element.owlCarousel(config);
                element.on('loaded.owl.lazy', function(event) {
                    event.element.parents('.product-item-info').removeClass('tabspro-lazyload');
                    event.element.removeClass('owl-lazy');
                    self._calImageWrapperHeight(element);
                });

                element.on('refreshed.owl.carousel', function(event) {
                    self._calImageWrapperHeight(element);
                });
                element.find('[data-role=tocart-form]').catalogAddToCart();
                element.find('.tabspro-swatch').each(function(index, el) {
                    var $id = $(this).data('id');
                    if ($id && window[$id]!= 'undefined') {
                        $(this).SwatchRenderer(window[$id]);
                    }
                });
            }
        },

        /**
         * When the widget gets instantiated, the first tab that is not disabled receive focusable property
         * Updated: for accessibility all tabs receive tabIndex 0
         * @private
         */
         _processTabIndex: function() {
            var self = this;
            self.triggers.attr("tabIndex",0);
            $.each(this.collapsibles, function(i) {
                if(!$(this).collapsible("option","disabled")) {
                    self.triggers.eq(i).attr("tabIndex", 0);
                    return false;
                }
            });
            $.each(this.collapsibles, function(i) {
                $(this).on("beforeOpen", function () {
                    var targetE = $(this).find('a').eq(0);
                    var id = $(targetE).attr('href');
                    if (!$(id).find('.owl-carousel[data-parent-id="' + id.replace('#', '') + '"]').eq(0).length) {
                        self._loadContent(targetE);
                    }
                    $(id).find('.owl-carousel[data-parent-id="' + id.replace('#', '') + '"]').each(function(index, el) {
                        self._OwlInit($(this));

                        if(!$(this).data('lazyload')) {
                            self._calImageWrapperHeight(element);
                        }
                    });
                    self.triggers.attr("tabIndex",0);
                    self.triggers.eq(i).attr("tabIndex",0);
                });
            });
        },
    });

    return $.tabspro.tabs;
});