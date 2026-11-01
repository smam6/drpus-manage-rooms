const ZP_settings = window.wc.wcSettings.getSetting( 'WC_ZPal_data', {} );
const ZP_label = window.wp.htmlEntities.decodeEntities( ZP_settings.title ) || '';

const ZP_Content = () => {
    return window.wp.htmlEntities.decodeEntities( ZP_settings.description || '' );
};

const ZP_Icon = () => {
    return ZP_settings.icon
        ? React.createElement('img', { src: ZP_settings.icon, style: { marginLeft: '20px' } })
        : null;
}

const ZP_Label = () => {
    return React.createElement(
        'span',
        { style: { width: '100%', display: 'flex', gap: '5px' } },
        ZP_label,
        React.createElement(ZP_Icon)
    );
}


const ZP_Block_Gateway = {
    name: 'WC_ZPal',
    label: React.createElement(ZP_Label),
    content: React.createElement(ZP_Content),
    edit: React.createElement(ZP_Content),
    canMakePayment: () => true,
    ariaLabel: ZP_label,
    supports: {
        features: ZP_settings.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod( ZP_Block_Gateway );