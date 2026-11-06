<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // WooCommerce section
	$opt_name,
	array(
		'title'			=> esc_html__( 'WooCommerce', 'drplus' ),
		'id'			=> 'wc-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_avatar_in_myaccount
				'id'		=> 'show_avatar_in_myaccount',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show user avatar in my account', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
			],
			[ // enable_wc_shop
				'id'		=> 'enable_wc_shop',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable Woocommerce shop', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
			],
			[
				'id'	=> 'booking_info-notice',
				'type'	=> 'info',
				'desc'	=> __( "If you don't want to use the WooCommerce store, disable the option above.", 'drplus' ),
				'style'	=> 'info',
				'icon'	=> 'el-icon-info-sign',
			],
			[ // product_badge
				'id'		=> 'product_badge',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Product badge status', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // sku_status
				'id'		=> 'sku_status',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'SKU status', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Enabled', 'drplus' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Enabled', 'drplus' ),
				'off'		=> esc_html__( 'Disabled', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // wc-default-attribute-icon
				'id'		=> 'wc-default-attribute-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Default attribute icon', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-diamond' ),
				'default'		=> 'drplus-icon-diamond',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
		)
	)
);

Redux::set_section( // Shop archive
	$opt_name,
	array(
		'title'			=> esc_html__( 'Shop archive', 'drplus' ),
		'id'			=> 'wc-shop-archive-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wc-move-out-of-stock-to-end
				'id'		=> 'wc-move-out-of-stock-to-end',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Move out of stock products to the end of the list', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'No', 'drplus' ) ) . "<br>" . esc_html__( "Caution: Enabling this feature may slow down the site speed.", 'drplus' ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // wc-brands-page-id
				'id'	=> 'wc-brands-page-id',
				'type'	=> 'select',
				'title'	=> esc_html__( 'Select a page for brands archive', 'drplus' ),
				'data'	=> 'pages',
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // default_wc_products_style
				'id'		=> 'default_wc_products_style',
				'type'		=> 'image_select',
				'title'		=> esc_html__( "Default products card style", 'drplus' ),
				'subtitle'	=> esc_html__( "Default products card style in shop, product categories, product tags, search and other general pages", 'drplus' ),
				'options'	=> [
					'style-1'	=> [
						'alt'	=> esc_html__( "Products style 1", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/backend/products-style-1.png"
					],
					'style-2'	=> [
						'alt'	=> esc_html__( "Products style 2", 'drplus' ),
						'img'	=> DRPLUS_URI . "assets/images/backend/products-style-2.png"
					],
				],
				'default'	=> 'style-1',
			],
		),
	)
);

Redux::set_section( // Single product page
	$opt_name,
	array(
		'title'			=> esc_html__( 'Single product page', 'drplus' ),
		'id'			=> 'wc-single-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wc-single-gallery-thumbnail-position
				'id'		=> 'wc-single-gallery-thumbnail-position',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Gallery thumbnail position', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( "End", 'drplus' ) ),
				'default'	=> 'end',
				'options'	=> [
					'end'	=> esc_html__( "End", 'drplus' ),
					'start'	=> esc_html__( "Start", 'drplus' ),
				],
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // wc-single-show-subtitle
				'id'		=> 'wc-single-show-subtitle',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show subtitle', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // wc-single-show-stars
				'id'		=> 'wc-single-show-stars',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show stars', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // wc-single-show-comments
				'id'		=> 'wc-single-show-comments',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show comments', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // wc-single-show-featured-attrs
				'id'		=> 'wc-single-show-featured-attrs',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show featured attributes', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // wc-single-show-product-services
				'id'		=> 'wc-single-show-product-services',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show product services', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			
			[ // divider
				'id'	=> 'wc-single-divider',
				'type'	=> 'divide',
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],

			[ // wc-single-end-products-show
				'id'		=> 'wc-single-end-products-show',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show end page products', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[ // wc-single-end-products-title
				'id'			=> 'wc-single-end-products-title',
				'type'			=> 'text',
				'title'			=> esc_html__( 'End products title', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Related products', 'drplus' ) ),
				'default'		=> esc_html__( 'Related products', 'drplus' ),
				'placeholder'	=> esc_html__( 'Related products', 'drplus' ),
				'required'		=> [
					['wc-single-end-products-show','=',true],
					['enable_wc_shop','=',true]
				],
			],
			[ // wc-single-end-products-title_tag
				'id'		=> 'wc-single-end-products-title_tag',
				'type'		=> 'select',
				'title'		=> __( 'End products title tag', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'H3', 'drplus' ) ),
				'default'	=> 'h3',
				'options'	=> $tags,
				'required'		=> [
					['wc-single-end-products-show','=',true],
					['enable_wc_shop','=',true]
				]
			],
			[ // wc-single-end-products-ppp
				'id'		=> 'wc-single-end-products-ppp',
				'type'		=> 'spinner',
				'title'		=> __( 'Post to show', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), '8' ),
				'default'	=> '8',
				'min'		=> '1',
				'max'		=> '12',
				'required'	=> [
					['wc-single-end-products-show','=',true],
					['enable_wc_shop','=',true]
				]
			],
			[ // wc-single-end-products-type
				'id'		=> 'wc-single-end-products-type',
				'type'		=> 'select',
				'title'		=> __( 'End products type', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Related products', 'drplus' ) ),
				'default'	=> 'related',
				'options'	=> [
					'related'	=> esc_html__( "Related products", 'drplus' ),
					'latests'	=> esc_html__( "Latests products", 'drplus' ),
				],
				'required'		=> [
					['wc-single-end-products-show','=',true],
					['enable_wc_shop','=',true]
				]
			],
		),
	)
);

Redux::set_section( // Texts
	$opt_name,
	array(
		'title'			=> esc_html__( 'Texts', 'drplus' ),
		'id'			=> 'wc-texts-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'wc_add_to_cart_single_text',
				'type'		=> 'text',
				'title'		=> __( 'Add to cart (Single) text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Add to cart', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Add to cart', 'drplus' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_empty_cart_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty cart text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Your cart is empty!', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Your cart is empty!', 'drplus' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_return_to_shop_text',
				'type'		=> 'text',
				'title'		=> __( 'Return to shop button text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Return to shop', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Return to shop', 'drplus' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_checkout_text',
				'type'		=> 'text',
				'title'		=> __( 'Checkout button text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Checkout', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Checkout', 'woocommerce' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_proceed_to_checkout_text',
				'type'		=> 'text',
				'title'		=> __( 'Proceed to checkout button text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Proceed to checkout', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Proceed to checkout', 'woocommerce' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_view_cart_text',
				'type'		=> 'text',
				'title'		=> __( 'View cart button text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), __( 'View cart', 'woocommerce' ) ),
				'compiler'	=> true,
				'default'	=> __( 'View cart', 'woocommerce' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_pay_order_text',
				'type'		=> 'text',
				'title'		=> __( 'Pay order button text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Pay and submit order', 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Pay and submit order', 'drplus' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_empty_orders_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty orders page text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'No order has been made yet.', 'woocommerce' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'No order has been made yet.', 'woocommerce' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_empty_downloads_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty downloads page text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'No downloads available yet.', 'woocommerce' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'No downloads available yet.', 'woocommerce' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_empty_shop_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty shop page text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "No product was found.", 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'No product was found.', 'drplus' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_empty_notifications_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty notifications page text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "The notification list is empty.", 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'The notification list is empty.', 'drplus' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_empty_wishlist_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty wishlist page text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "There are no products in wishlist.", 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'There are no products in wishlist.', 'drplus' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
			[
				'id'		=> 'wc_empty_appointments_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty appointments page text', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "You have not booked any appointments yet.", 'drplus' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'You have not booked any appointments yet.', 'drplus' ),
				'required'	=> [
					['enable_wc_shop','=',true]
				],
			],
		),
	)
);

Redux::set_section( // Mini cart
	$opt_name,
	array(
		'title'			=> esc_html__( 'Mini cart', 'drplus' ),
		'id'			=> 'wc-mini-cart-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // mini-cart-style
				'id'		=> 'mini-cart-style',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Mini cart style', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Style 1', 'drplus' ) ),
				'default'	=> 'latest_view',
				'options'	=> [
					'style_1'	=> esc_html__( 'Style 1', 'drplus' ),
					'style_2'	=> esc_html__( 'Style 2', 'drplus' ),
				],
			],
			[ // mini-cart-tile
				'id'			=> 'mini-cart-tile',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Mini cart title', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "Your cart", 'drplus' ) ),
				'default'		=> esc_html__( "Your cart", 'drplus' ),
				'placeholder'	=> esc_html__( "Your cart", 'drplus' ),
				'required'		=> [
					['mini-cart-style','=','style_2'],
				],
			],
			[ // mini-cart-title-icon
				'id'			=> 'mini-cart-title-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Mini cart title icon', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 'drplus-icon-bag-2' ),
				'default'		=> 'drplus-icon-bag-2',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> DRPLUS_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Doctor plus icons', 'drplus' ),
						'prefix'	=> 'drplus-icon',
					],
				],
				'required'		=> [
					['mini-cart-style','=','style_2'],
				],
			],
			[ // empty-mini-cart-text
				'id'			=> 'empty-mini-cart-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Empty mini cart text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( "The cart is empty.", 'drplus' ) ),
				'default'		=> esc_html__( "The cart is empty.", 'drplus' ),
				'placeholder'	=> esc_html__( "The cart is empty.", 'drplus' ),
			],
		),
	)
);