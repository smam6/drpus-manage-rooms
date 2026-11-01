const settings = window.wc.wcSettings.getSetting('WC_ZPal_data', {});

const ZarinpalContent = () => {
    return window.wp.element.createElement('div', {}, [
        window.wp.element.createElement('div', {
            key: 'description',
            dangerouslySetInnerHTML: { __html: settings.description || '' }
        }),
        settings.fee_payer === 'customer' ? window.wp.element.createElement('div', {
            key: 'fee-notice',
            style: { 
                margin: '10px 0', 
                padding: '10px', 
                backgroundColor: '#e7f3ff', 
                border: '1px solid #b3d9ff',
                borderRadius: '4px',
                fontSize: '14px',
                textAlign: 'center'
            }
        }, 'کارمزد درگاه پرداخت به مبلغ سفارش اضافه خواهد شد.') : null
    ]);
};

const ZarinpalLabel = () => {
    return window.wp.element.createElement('span', {
        style: { display: 'flex', alignItems: 'center', gap: '10px' }
    }, [
        settings.title || 'ZarinPal',
        settings.icon ? window.wp.element.createElement('img', {
            key: 'icon',
            src: settings.icon,
            style: { height: '24px' },
            alt: 'ZarinPal'
        }) : null
    ]);
};

window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: 'WC_ZPal',
    label: window.wp.element.createElement(ZarinpalLabel),
    content: window.wp.element.createElement(ZarinpalContent),
    edit: window.wp.element.createElement(ZarinpalContent),
    canMakePayment: () => true,
    ariaLabel: settings.title || 'ZarinPal',
    supports: {
        features: settings.supports || []
    }
});

jQuery(document).ready(function($) {
    var isCheckoutPage = $('body').hasClass('woocommerce-checkout') || 
                        $('.wc-block-checkout').length > 0 || 
                        window.location.pathname.includes('/checkout') ||
                        $('.woocommerce-checkout-review-order-table').length > 0;
    
    if (!isCheckoutPage) {
        return;
    }
    
    function triggerBlocksRefresh() {
        try {
            $(document).trigger('wc_cart_fragments_loaded');
            $(window).trigger('wc_cart_hash_changed');
        } catch (e) {
        }
    }
    
    function manipulateCartDisplay(selectedMethod) {
        var feeFound = false;
        var feeValue = 0;
        
        $('.wc-block-components-totals-item').each(function() {
            var $item = $(this);
            var text = $item.text();
            
            if (text.includes('کارمزد درگاه') || (text.includes('کارمزد') && text.includes('درگاه'))) {
                feeFound = true;
                
                if (selectedMethod !== 'WC_ZPal') {
                    var $valueElement = $item.find('.wc-block-components-totals-item__value');
                    feeValue = extractNumericValue($valueElement.text());
                }
                
                if (selectedMethod === 'WC_ZPal') {
                    $item.show();
                } else {
                    $item.hide();
                }
                
                return false;
            }
        });
        
        if (feeFound && selectedMethod !== 'WC_ZPal' && feeValue > 0) {
            updateTotalDisplay(-feeValue);
        }
    }
    
    function extractNumericValue(priceText) {
        if (!priceText) return 0;
        
        var numericStr = priceText.replace(/[۰-۹]/g, function(w) {
            var persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return persian.indexOf(w).toString();
        });
        
        numericStr = numericStr.replace(/[^\d.,]/g, '');
        numericStr = numericStr.replace(/,/g, '');
        
        return parseFloat(numericStr) || 0;
    }
    
        function updateTotalDisplay(adjustment) {
        var totalSelectors = [
            '.wc-block-components-totals-item--total .wc-block-components-totals-item__value',
            '.wc-block-checkout__totals .wc-block-components-totals-item--total .wc-block-components-totals-item__value'
        ];
        
        totalSelectors.forEach(function(selector) {
            var $totalElement = $(selector);
            if ($totalElement.length) {
                var currentText = $totalElement.text();
                var currentValue = extractNumericValue(currentText);
                var newValue = currentValue + adjustment;
                
                var formattedValue = newValue.toLocaleString('fa-IR');
                var newText = currentText.replace(/[\d,۰-۹.]+/g, formattedValue);
                $totalElement.text(newText);
            }
        });
    }

    function useClientSideFallback(selectedMethod) {
        if (typeof wc_checkout_params === 'undefined') {
            manipulateCartDisplay(selectedMethod);
            triggerBlocksRefresh();
        } else {
            $('body').trigger('update_checkout');
        }
        
        isUpdating = false;
    }

    var updateTimeout;
    var lastSelectedMethod = '';
    var isUpdating = false;
    
    function updatePaymentMethod(selectedMethod) {
        if (isUpdating || selectedMethod === lastSelectedMethod) {
            return;
        }
        
        if (updateTimeout) {
            clearTimeout(updateTimeout);
        }
        
        lastSelectedMethod = selectedMethod;
        isUpdating = true;
        
        var ajaxUrl = '';
        var nonce = '';
        
        if (typeof wc_checkout_params !== 'undefined') {
            ajaxUrl = wc_checkout_params.ajax_url;
            nonce = wc_checkout_params.update_order_review_nonce;
        } else {
            if (window.zarinpalAjaxUrl) {
                ajaxUrl = window.zarinpalAjaxUrl;
            } else if (typeof wc_settings !== 'undefined' && wc_settings.admin_url) {
                ajaxUrl = wc_settings.admin_url + 'admin-ajax.php';
            } else if (typeof wcSettings !== 'undefined' && wcSettings.adminUrl) {
                ajaxUrl = wcSettings.adminUrl + 'admin-ajax.php';
            } else {
                var iconUrl = settings.icon || '';
                if (iconUrl.includes('/wp-content/')) {
                    var basePath = iconUrl.split('/wp-content/')[0];
                    ajaxUrl = basePath + '/wp-admin/admin-ajax.php';
                } else {
                    var currentHost = window.location.origin;
                    var possiblePaths = [
                        currentHost + '/wp-admin/admin-ajax.php',
                        currentHost + '/wordpress/wp-admin/admin-ajax.php',
                        currentHost + window.location.pathname.split('/')[1] + '/wp-admin/admin-ajax.php'
                    ];
                    ajaxUrl = possiblePaths[0];
                }
            }
        }
        
                $.post(ajaxUrl, {
            action: 'zarinpal_update_payment_method',
            payment_method: selectedMethod,
            nonce: nonce
        }).done(function(response) {
            updateTimeout = setTimeout(function() {
                if (typeof wc_checkout_params !== 'undefined') {
                    $('body').trigger('update_checkout');
                    isUpdating = false;
                } else {
                    if (window.wp && window.wp.data) {
                        try {
                            var cartDispatch = window.wp.data.dispatch('wc/store/cart');
                            if (cartDispatch && typeof cartDispatch.invalidateResolution === 'function') {
                                cartDispatch.invalidateResolution('getCartData');
                                cartDispatch.invalidateResolution('getCartTotals');
                            }
                        } catch (e) {
                        }
                    }
                    
                    setTimeout(function() {
                        manipulateCartDisplay(selectedMethod);
                        triggerBlocksRefresh();
                        isUpdating = false;
                    }, 150);
                }
            }, 50);
        }).fail(function(xhr, status, error) {
            isUpdating = false;
            
            updateTimeout = setTimeout(function() {
                useClientSideFallback(selectedMethod);
            }, 50);
        });
    }
    
    var classicCheckoutTimeout;
    $(document.body).on('change', 'input[name="payment_method"]', function() {
        var selectedMethod = $(this).val();
        
        if (classicCheckoutTimeout) {
            clearTimeout(classicCheckoutTimeout);
        }
        
        classicCheckoutTimeout = setTimeout(function() {
            updatePaymentMethod(selectedMethod);
        }, 100);
    });
    
    setTimeout(function() {
        var selectedMethod = $('input[name="payment_method"]:checked').val();
        if (selectedMethod) {
            updatePaymentMethod(selectedMethod);
        }
    }, 500);
    
    if (window.MutationObserver) {
        var blocksLastSelectedMethod = '';
        var blocksObserverTimeout;
        
        var observer = new MutationObserver(function(mutations) {
            if (blocksObserverTimeout) {
                clearTimeout(blocksObserverTimeout);
            }
            
            blocksObserverTimeout = setTimeout(function() {
                var selectedRadio = document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked');
                if (selectedRadio) {
                    var selectedMethod = selectedRadio.value;
                    if (selectedMethod !== blocksLastSelectedMethod) {
                        blocksLastSelectedMethod = selectedMethod;
                        updatePaymentMethod(selectedMethod);
                    }
                }
            }, 150);
        });
        
        setTimeout(function() {
            var checkoutContainer = document.querySelector('.wc-block-checkout');
            if (checkoutContainer) {
                observer.observe(checkoutContainer, {
                    childList: true,
                    subtree: true
                });
                
                var initialRadio = document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked');
                if (initialRadio) {
                    blocksLastSelectedMethod = initialRadio.value;
                    updatePaymentMethod(initialRadio.value);
                }
            }
        }, 1000);
    }
}); 