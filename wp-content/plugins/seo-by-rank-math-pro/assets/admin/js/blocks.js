!function(e){var t={};function o(a){if(t[a])return t[a].exports;var n=t[a]={i:a,l:!1,exports:{}};return e[a].call(n.exports,n,n.exports,o),n.l=!0,n.exports}o.m=e,o.c=t,o.d=function(e,t,a){o.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:a})},o.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},o.t=function(e,t){if(1&t&&(e=o(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(o.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)o.d(a,n,function(t){return e[t]}.bind(null,n));return a},o.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return o.d(t,"a",t),t},o.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},o.p="",o(o.s=377)}({11:function(e,t){e.exports=wp.element},2:function(e,t){e.exports=wp.i18n},362:function(e,t){e.exports=wp.blocks},377:function(e,t,o){"use strict";o.r(t);var a=o(5),n=o(2),r=o(39),l=o(8),s=o(362),i=o(11),p=o(9),u=o(60);function c(e){return(c="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function h(e,t,o){return t in e?Object.defineProperty(e,t,{value:o,enumerable:!0,configurable:!0,writable:!0}):e[t]=o,e}function b(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _(e,t){for(var o=0;o<t.length;o++){var a=t[o];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function m(e,t){return(m=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function d(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var o,a=g(e);if(t){var n=g(this).constructor;o=Reflect.construct(a,arguments,n)}else o=a.apply(this,arguments);return y(this,o)}}function y(e,t){return!t||"object"!==c(t)&&"function"!=typeof t?f(e):t}function f(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function g(e){return(g=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var w,k,O=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&m(e,t)}(i,e);var t,o,r,s=d(i);function i(){var e;return b(this,i),(e=s.apply(this,arguments)).getAdditionalSettings=e.getAdditionalSettings.bind(f(e)),e.getMapSettings=e.getMapSettings.bind(f(e)),e}return t=i,(o=[{key:"render",value:function(){var e=this,t=this.props,o=t.className,a=t.setAttributes,r=t.attributes,s=t.locationsData,i=t.termsData;return wp.element.createElement("div",{id:"rank-math-local",className:"rank-math-block "+o},wp.element.createElement(u.InspectorControls,{key:"inspector"},wp.element.createElement(l.PanelBody,{title:Object(n.__)("Settings","rank-math-pro"),initialOpen:"true"},wp.element.createElement(l.SelectControl,{label:Object(n.__)("Type","rank-math-pro"),value:r.type,options:[{value:"address",label:Object(n.__)("Address","rank-math-pro")},{value:"opening-hours",label:Object(n.__)("Opening Hours","rank-math-pro")},{value:"map",label:Object(n.__)("Map","rank-math-pro")},{value:"store-locator",label:Object(n.__)("Store Locator","rank-math-pro")}],onChange:function(e){return a({type:e})}}),"store-locator"!==r.type&&wp.element.createElement(l.SelectControl,{label:Object(n.__)("Locations","rank-math-pro"),value:r.locations,options:s,onChange:function(e){return a({locations:e})}}),"store-locator"!==r.type&&wp.element.createElement(l.SelectControl,{label:Object(n.__)("Location Categories","rank-math-pro"),multiple:!0,value:r.terms,options:i,onChange:function(e){return a({terms:e})}}),wp.element.createElement(l.TextControl,{type:"number",label:Object(n.__)("Maximum number of locations to show","rank-math-pro"),value:r.limit,onChange:function(t){return e.props.setAttributes({limit:t})}}),"address"===r.type&&this.getAddressSettings(),"opening-hours"===r.type&&this.getHoursSettings(),"map"===r.type&&this.getMapSettings(),"store-locator"===r.type&&this.getStoreLocatorSettings()),"address"===r.type&&this.getAdditionalSettings("getHoursSettings"),("map"===r.type||"store-locator"===r.type)&&this.getAdditionalSettings("getAddressSettings"),"store-locator"===r.type&&this.getAdditionalSettings("getMapSettings")),this.getBlockContent(r))}},{key:"getBlockContent",value:function(e){return"map"===e.type?wp.element.createElement("img",{src:rankMath.previewImage,alt:Object(n.__)("Preview Image","rank-math-pro")}):wp.element.createElement(l.ServerSideRender,{block:"rank-math/local-business",attributes:e})}},{key:"getAdditionalSettings",value:function(e){var t=Object(n.__)("Address Settings","rank-math-pro");return"getHoursSettings"===e?t=Object(n.__)("Opening Hours Settings","rank-math-pro"):"getMapSettings"===e&&(t=Object(n.__)("Map Settings","rank-math-pro")),wp.element.createElement(l.PanelBody,{title:t},this[e]())}},{key:"getFieldsData",value:function(e){var t=this,o=[];return Object(a.forEach)(e,(function(e,a){"boolean"===e.type&&o.push(wp.element.createElement(l.ToggleControl,{label:e.label,checked:t.props.attributes[a],onChange:function(e){return t.props.setAttributes(h({},a,e))}})),"string"===e.type&&o.push(wp.element.createElement(l.TextControl,{label:e.label,value:t.props.attributes[a],onChange:function(e){return t.props.setAttributes(h({},a,e))}})),"select"===e.type&&o.push(wp.element.createElement(l.SelectControl,{label:e.label,value:t.props.attributes[a],options:e.options,onChange:function(e){return t.props.setAttributes(h({},a,e))}})),"range"===e.type&&o.push(wp.element.createElement(l.RangeControl,{label:e.label,value:t.props.attributes[a],onChange:function(e){return t.props.setAttributes(h({},a,e))},min:e.min,max:e.max}))})),o}},{key:"getMapSettings",value:function(){var e=this,t=[],o="store-locator"===this.props.attributes.type;if(o&&t.push(wp.element.createElement(l.ToggleControl,{label:Object(n.__)("Show Map","rank-math-pro"),checked:this.props.attributes.show_map,onChange:function(t){return e.props.setAttributes({show_map:t})}})),o&&!this.props.attributes.show_map)return t;var a={map_style:{label:Object(n.__)("Map Type","rank-math-pro"),type:"select",options:[{value:"roadmap",label:Object(n.__)("Roadmap","rank-math-pro")},{value:"hybrid",label:Object(n.__)("Hybrid","rank-math-pro")},{value:"satellite",label:Object(n.__)("Satellite","rank-math-pro")},{value:"terrain",label:Object(n.__)("Terrain","rank-math-pro")}]},map_width:{label:Object(n.__)("Map Width","rank-math-pro"),type:"string"},map_height:{label:Object(n.__)("Map Height","rank-math-pro"),type:"string"},show_category_filter:{label:Object(n.__)("Show Category filter","rank-math-pro"),type:"boolean"},zoom_level:{label:Object(n.__)("Zoom Level","rank-math-pro"),type:"range",min:-1,max:19},allow_zoom:{label:Object(n.__)("Allow Zoom","rank-math-pro"),type:"boolean"},allow_scrolling:{label:Object(n.__)("Allow Zoom by scroll","rank-math-pro"),type:"boolean"},allow_dragging:{label:Object(n.__)("Allow Dragging","rank-math-pro"),type:"boolean"},show_marker_clustering:{label:Object(n.__)("Show Marker Clustering","rank-math-pro"),type:"boolean"},show_infowindow:{label:Object(n.__)("Show InfoWindow","rank-math-pro"),type:"boolean"},show_route_planner:{label:Object(n.__)("Show Route Planner","rank-math-pro"),type:"boolean"},route_label:{label:Object(n.__)("Route Label","rank-math-pro"),type:"string"}};return o||(delete a.show_route_planner,delete a.route_label),t.concat(this.getFieldsData(a))}},{key:"getStoreLocatorSettings",value:function(){var e={show_radius:{label:Object(n.__)("Show radius","rank-math-pro"),type:"boolean"},search_radius:{label:Object(n.__)("Search Locations within the radius","rank-math-pro"),type:"range",min:5,max:1e3},show_category_filter:{label:Object(n.__)("Add dropdown to filter results by category","rank-math-pro"),type:"boolean"},show_nearest_location:{label:Object(n.__)("Show nearest location if none is found within radius","rank-math-pro"),type:"boolean"}};return this.getFieldsData(e)}},{key:"getAddressSettings",value:function(){var e={show_company_name:{label:Object(n.__)("Show Company Name","rank-math-pro"),type:"boolean"},show_company_address:{label:Object(n.__)("Show Company Address","rank-math-pro"),type:"boolean"},show_on_one_line:{label:Object(n.__)("Show address on one line","rank-math-pro"),type:"boolean"},show_state:{label:Object(n.__)("Show State","rank-math-pro"),type:"boolean"},show_country:{label:Object(n.__)("Show Country","rank-math-pro"),type:"boolean"},show_telephone:{label:Object(n.__)("Show Primary number","rank-math-pro"),type:"boolean"},show_secondary_number:{label:Object(n.__)("Show Secondary number","rank-math-pro"),type:"boolean"},show_fax:{label:Object(n.__)("Show FAX number","rank-math-pro"),type:"boolean"},show_email:{label:Object(n.__)("Show Email","rank-math-pro"),type:"boolean"},show_url:{label:Object(n.__)("Show Business URL","rank-math-pro"),type:"boolean"},show_logo:{label:Object(n.__)("Show Logo","rank-math-pro"),type:"boolean"},show_vat_id:{label:Object(n.__)("Show VAT number","rank-math-pro"),type:"boolean"},show_tax_id:{label:Object(n.__)("Show TAX ID","rank-math-pro"),type:"boolean"},show_coc_id:{label:Object(n.__)("Show COC number","rank-math-pro"),type:"boolean"},show_priceRange:{label:Object(n.__)("Show Price Indication","rank-math-pro"),type:"boolean"}};return"address"===this.props.attributes.type&&delete e.show_company_address,this.getFieldsData(e)}},{key:"getHoursSettings",value:function(){var e=this,t=[],o=this.props.attributes.type;if("address"===o&&t.push(wp.element.createElement(l.ToggleControl,{label:Object(n.__)("Show Opening Hours","rank-math-pro"),checked:this.props.attributes.show_opening_hours,onChange:function(t){return e.props.setAttributes({show_opening_hours:t})}})),"opening-hours"===o||this.props.attributes.show_opening_hours){var r=this.props.attributes.show_days.split(",");Object(a.forEach)(["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],(function(o){t.push(wp.element.createElement(l.ToggleControl,{label:Object(n.sprintf)(Object(n.__)("Show %s","rank-math-pro"),o),checked:r.includes(o),onChange:function(){var t=r.indexOf(o);t>-1?r.splice(t,1):r.push(o),e.props.setAttributes({show_days:r.toString()})}}))})),t.push(wp.element.createElement(l.ToggleControl,{label:Object(n.__)("Hide Closed Days","rank-math-pro"),checked:this.props.attributes.hide_closed_days,onChange:function(t){return e.props.setAttributes({hide_closed_days:t})}})),t.push(wp.element.createElement(l.ToggleControl,{label:Object(n.__)("Show open now label after opening hour for current day","rank-math-pro"),checked:this.props.attributes.show_opening_now_label,onChange:function(t){return e.props.setAttributes({show_opening_now_label:t})}})),this.props.attributes.show_opening_now_label&&t.push(wp.element.createElement(l.TextControl,{label:Object(n.__)("Show open now label after opening hour for current day","rank-math-pro"),value:this.props.attributes.opening_hours_note,onChange:function(t){return e.props.setAttributes({opening_hours_note:t})}}))}return t}}])&&_(t.prototype,o),r&&_(t,r),i}(i.Component),j=Object(p.withSelect)((function(e){var t=e("core").getEntityRecords("postType","rank_math_locations"),o=[];t&&(o.push({value:0,label:Object(n.__)("All Locations","rank-math-pro")}),t.forEach((function(e){o.push({value:e.id,label:e.title.rendered})})));var a=e("core").getEntityRecords("taxonomy","rank_math_location_category"),r=[];return a&&a.forEach((function(e){r.push({value:e.id,label:e.name})})),{locationsData:o,termsData:r}}))(O);Object(a.isUndefined)(rankMath.limitLocations)||(w=[Object(n.__)("Local Business","rank-math-pro"),Object(n.__)("Rank Math","rank-math-pro"),Object(n.__)("Contact","rank-math-pro")],k={type:{type:"string",default:"address"},locations:{type:"string",default:""},terms:{type:"array",default:[]},limit:{type:"string",default:rankMath.limitLocations},show_company_name:{type:"boolean",default:!0},show_company_address:{type:"boolean",default:!0},show_on_one_line:{type:"boolean",default:!1},show_state:{type:"boolean",default:!0},show_country:{type:"boolean",default:!0},show_telephone:{type:"boolean",default:!0},show_secondary_number:{type:"boolean",default:!0},show_fax:{type:"boolean",default:!1},show_email:{type:"boolean",default:!0},show_url:{type:"boolean",default:!0},show_logo:{type:"boolean",default:!0},show_vat_id:{type:"boolean",default:!1},show_tax_id:{type:"boolean",default:!1},show_coc_id:{type:"boolean",default:!1},show_priceRange:{type:"boolean",default:!1},show_opening_hours:{type:"boolean",default:!1},show_days:{type:"string",default:"Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday"},hide_closed_days:{type:"boolean",default:!1},show_opening_now_label:{type:"boolean",default:!1},opening_hours_note:{type:"string",default:"Open Now"},show_map:{type:"boolean",default:!1},map_style:{type:"string",default:rankMath.mapStyle},map_width:{type:"string",default:"100%"},map_height:{type:"string",default:"300px"},zoom_level:{type:"integer",default:-1},allow_zoom:{type:"boolean",default:!0},allow_scrolling:{type:"boolean",default:!0},allow_dragging:{type:"boolean",default:!0},show_route_planner:{type:"boolean",default:!0},route_label:{type:"string",default:""},show_category_filter:{type:"boolean",default:!1},show_marker_clustering:{type:"boolean",default:!0},show_infowindow:{type:"boolean",default:!0},show_radius:{type:"boolean",default:!0},show_nearest_location:{type:"boolean",default:!0},search_radius:{type:"string",default:10}},Object(s.registerBlockType)("rank-math/local-business",{title:Object(n.__)("Local Business by Rank Math","rank-math-pro"),description:Object(n.__)("Rank Math's Local Business block","rank-math-pro"),category:"rank-math-blocks",icon:"editor-ul",label:Object(n.__)("Local Business by Rank Math","rank-math-pro"),keywords:w,attributes:k,edit:j,save:function(){return null}})),Object(r.addFilter)("rank_math_block_howto_attributes","rank-math-pro",(function(e){return e.estimatedCost={type:"string",default:""},e.estimatedCostCurrency={type:"string",default:"USD"},e.supply={type:"string",default:""},e.tools={type:"string",default:""},e.material={type:"string",default:""},e})),Object(r.addFilter)("rank_math_block_howto_data","rank-math-pro",(function(e,t){var o=t.attributes,a=t.setAttributes;return wp.element.createElement(React.Fragment,null,o.hasOwnProperty("estimatedCost")&&wp.element.createElement("div",{className:"rank-math-howto-estimated-cost"},wp.element.createElement(l.TextControl,{label:Object(n.__)("Estimated Cost","rank-math-pro"),value:o.estimatedCostCurrency,placeholder:Object(n.__)("USD","rank-math-pro"),onChange:function(e){a({estimatedCostCurrency:e})}}),wp.element.createElement(l.TextControl,{type:"number",value:o.estimatedCost,placeholder:Object(n.__)("Estimated Cost:","rank-math-pro"),onChange:function(e){a({estimatedCost:e})}})),o.hasOwnProperty("supply")&&wp.element.createElement(l.TextareaControl,{label:Object(n.__)("Supply","rank-math-pro"),help:Object(n.__)("Add one supply element per line.","rank-math-pro"),value:o.supply,onChange:function(e){a({supply:e})}}),o.hasOwnProperty("tools")&&wp.element.createElement(l.TextareaControl,{label:Object(n.__)("Tools","rank-math-pro"),help:Object(n.__)("Add one tool per line.","rank-math-pro"),value:o.tools,onChange:function(e){a({tools:e})}}),o.hasOwnProperty("material")&&wp.element.createElement(l.TextareaControl,{label:Object(n.__)("Material","rank-math-pro"),value:o.material,onChange:function(e){a({material:e})}}))})),Object(r.addFilter)("rank_math_block_schema_attributes","rank-math-pro",(function(e){return e.id={type:"string",default:""},e}))},39:function(e,t){e.exports=wp.hooks},5:function(e,t){e.exports=lodash},60:function(e,t){e.exports=wp.blockEditor},8:function(e,t){e.exports=wp.components},9:function(e,t){e.exports=wp.data}});