!function(t){var e={};function n(r){if(e[r])return e[r].exports;var o=e[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=t,n.c=e,n.d=function(t,e,r){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)n.d(r,o,function(e){return t[e]}.bind(null,o));return r},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=0)}([function(t,e,n){t.exports=n(1)},function(t,e){!function(t){"use strict";var e=new Date;e.getHours(),e.getMonth();t("body").addClass("daylight"),t(document).ready(function(){t(".entry-content p").each(function(e,n){/^[「（—『【　]/.test(t(n).text())&&t(n).addClass("no-indent")})}),t(document).ready(function(){t(".kyom-callout-button").click(function(){var e=t(this).parents(".kyom-callout");e.addClass("fade"),setTimeout(function(){t(document).trigger("callout-closed",[e.attr("data-slug")]),e.remove()},500)})}),t(document).ready(function(){t(".pager").each(function(e,n){t(n).find(".pager-link").fitHeight()})}),t(document).ready(function(){var e=null,n=0,r=[];t(".entry-content a[href]").each(function(o,a){var i=t(a).attr("href");if(!/\.(jpe?g|gif|png)$/i.test(i))return!0;if(t(a).parents(".tiled-gallery").length)return!0;var u=i.split("/"),c=u[u.length-1],l=t(a).find("img");l.length&&l.attr("alt")&&(c=l.attr("alt")),l.length&&l.attr("data-image-title")&&(c=l.attr("data-image-title")),t(a).next(".wp-caption").length&&(c=t(a).next(".wp-caption").text());var f={source:i,caption:c};r.push(f);var d=n+0;t(a).click(function(t){t.preventDefault(),e.show(d)}),n++}),r&&(e=UIkit.lightboxPanel({items:r}))})}(jQuery)}]);
//# sourceMappingURL=app.js.map