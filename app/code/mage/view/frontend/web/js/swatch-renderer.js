define([
    'jquery',
    'jquery/ui',
    'Magento_Swatches/js/swatch-renderer'
    ], function($) {

	$.widget('tabspro.SwatchRenderer', $.mage.SwatchRenderer, {

		/**
         * @private
         */
        _create: function () {
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main'),
                isProductViewExist =  0,
                $main = isProductViewExist ?
                    this.element.parents('.column.main') :
                    this.element.parents('.product-item-info');

            if (isProductViewExist) {
                gallery.data('gallery') ?
                    this._onGalleryLoaded(gallery) :
                    gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));
            } else {
                options.mediaGalleryInitial = [{
                    'img': $main.find('.product-image-photo').attr('src')
                }];
            }

            this.productForm = this.element.parents(this.options.selectorProductTile).find('form:first');
            this.inProductList = this.productForm.length > 0;
        },

		/**
         * Gets all product media and change current to the needed one
         *
         * @private
         */
        _LoadProductMedia: function () {
            var $widget = this,
                $this = $widget.element,
                attributes = {},
                productId = 0,
                mediaCallData,
                mediaCacheKey,

                /**
                 * Processes product media data
                 *
                 * @param {Object} data
                 * @returns void
                 */
                mediaSuccessCallback = function (data) {
                    if (!(mediaCacheKey in $widget.options.mediaCache)) {
                        $widget.options.mediaCache[mediaCacheKey] = data;
                    }
                    $widget._ProductMediaCallback($this, data);
                    $widget._DisableProductMediaLoader($this);
                };

            if (!$widget.options.mediaCallback) {
                return;
            }

            $this.find('[option-selected]').each(function () {
                var $selected = $(this);

                attributes[$selected.attr('attribute-code')] = $selected.attr('option-selected');
            });

            productId = $this.parents('.product.details.product-item-details')
                    .find('.price-box.price-final_price').attr('data-product-id');

            mediaCallData = {
                'product_id': this.getProduct()
            };
            mediaCacheKey = JSON.stringify(mediaCallData);

            if (mediaCacheKey in $widget.options.mediaCache) {
                $widget._XhrKiller();
                $widget._EnableProductMediaLoader($this);
                mediaSuccessCallback($widget.options.mediaCache[mediaCacheKey]);
            } else {
                mediaCallData.isAjax = true;
                $widget._XhrKiller();
                $widget._EnableProductMediaLoader($this);
                $widget.xhr = $.get(
                    $widget.options.mediaCallback,
                    mediaCallData,
                    mediaSuccessCallback,
                    'json'
                ).done(function () {
                    $widget._XhrKiller();
                });
            }
        },

                /**
         * Enable loader
         *
         * @param {Object} $this
         * @private
         */
        _EnableProductMediaLoader: function ($this) {
            var $widget = this;

            //Category View
                $this.parents('.product-item-info').find('.product-image-photo')
                    .addClass($widget.options.classes.loader);
        },

      	        /**
         * Disable loader
         *
         * @param {Object} $this
         * @private
         */
        _DisableProductMediaLoader: function ($this) {
            var $widget = this;

            //Category View
            $this.parents('.product-item-info').find('.product-image-photo')
                    .removeClass($widget.options.classes.loader);
        },


        /**
         * Callback for product media
         *
         * @param {Object} $this
         * @param {String} response
         * @private
         */
        _ProductMediaCallback: function ($this, response) {
            var isProductViewExist = 0,
                $main = isProductViewExist ? $this.parents('.column.main') : $this.parents('.product-item-info'),
                $widget = this,
                images = [],

                /**
                 * Check whether object supported or not
                 *
                 * @param {Object} e
                 * @returns {*|Boolean}
                 */
                support = function (e) {
                    return e.hasOwnProperty('large') && e.hasOwnProperty('medium') && e.hasOwnProperty('small');
                };

            if (_.size($widget) < 1 || !support(response)) {
                this.updateBaseImage(this.options.mediaGalleryInitial, $main, isProductViewExist);

                return;
            }

            images.push({
                full: response.large,
                img: response.medium,
                thumb: response.small,
                isMain: true
            });

            if (response.hasOwnProperty('gallery')) {
                $.each(response.gallery, function () {
                    if (!support(this) || response.large === this.large) {
                        return;
                    }
                    images.push({
                        full: this.large,
                        img: this.medium,
                        thumb: this.small
                    });
                });
            }

            this.updateBaseImage(images, $main, isProductViewExist);
        },
	});

	

	return $.tabspro.SwatchRenderer;
});