(()=>{"use strict";const e=window.React,n=window.wp.plugins,i=window.wc.blocksCheckout,o=window.wp.element,t=({extensions:e})=>((0,o.useEffect)((()=>{jQuery("#wcs-debug").length>0&&(void 0!==e["woo-conditional-shipping"].debug&&e["woo-conditional-shipping"].debug?(jQuery("#wcs-debug").replaceWith(e["woo-conditional-shipping"].debug),jQuery(document.body).trigger("wcs_updated_debug")):jQuery("#wcs-debug #wcs-debug-contents").replaceWith("N/A"))}),[e["woo-conditional-shipping"].debug]),null),c=({cart:n,extensions:i})=>{const[t,c]=(0,o.useState)(i["woo-conditional-shipping"].notices);return(0,o.useEffect)((()=>{c(i["woo-conditional-shipping"].notices)}),[n.shippingRates]),0===t.length?null:(0,e.createElement)("div",{className:"wcs-shipping-notices wcs-shipping-notices-blocks"},t.map(((n,i)=>(0,e.createElement)(s,{notice:n,key:i}))))},s=n=>(0,e.createElement)("div",{dangerouslySetInnerHTML:{__html:n.notice}});(0,n.registerPlugin)("woo-conditional-shipping",{render:()=>(0,e.createElement)(i.ExperimentalOrderShippingPackages,null,(0,e.createElement)(t,null),(0,e.createElement)(c,null)),scope:"woocommerce-checkout"})})();