var config = {
  map: {
        "*": {
            "cdz_slider": "js/owlcarousel/owlslider",
            "modal" : "Magento_Ui/js/modal/modal",
			"cdz_menu": "js/menu/cdz_menu",
			"cdz_ajax_product":"Codazon_ProductFilter/js/ajaxload",
			"cdzZoom": "Magento_Catalog/js/cdzZoom",
            "owlSlider": "js/owlcarousel/owl.carousel.min",
            "owlslider" : "js/owlcarousel/owl.carousel.min",
            "owl_slider": "js/owlcarousel/owl.carousel.min",
			"description":"js/images",
            themewidgets : 'js/theme-widgets'
        }
    },
    paths:  {
        "owlslider" : "js/owlcarousel/owl.carousel.min",
        'jquery-ui-modules': 'jquery/ui-modules'
    },
	config: {
        mixins: {
            'mage/collapsible': {
                'js/mage/collapsible-mixin': true
            }
        }
    },
    "shim": {
		"js/owlcarousel/owl.carousel.min": ["jquery"],
	},
	deps: [
        "Magento_Theme/js/fastest",
    ]

};
