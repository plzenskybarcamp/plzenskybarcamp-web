/*! Smooth Scroll - v1.4.6 - 2012-08-23
* Copyright (c) 2012 Karl Swedberg; Licensed MIT, GPL */(function(e){function s(e){return e.replace(/(:|\.)/g,"\\$1")}var t="1.4.6",n={exclude:[],excludeWithin:[],offset:0,direction:"top",scrollElement:null,scrollTarget:null,beforeScroll:function(){},afterScroll:function(){},easing:"swing",speed:700,autoCoefficent:2},r=function(t){var n=[],r=!1,i=t.dir&&t.dir=="left"?"scrollLeft":"scrollTop";this.each(function(){if(this==document||this==window)return;var t=e(this);if(t[i]()>0)n.push(this);else{t[i](1);r=t[i]()>0;r&&n.push(this);t[i](0)}});n.length||this.each(function(e){this.nodeName==="BODY"&&(n=[this])});t.el==="first"&&n.length>1&&(n=[n[0]]);return n},i="ontouchend"in document;e.fn.extend({scrollable:function(e){var t=r.call(this,{dir:e});return this.pushStack(t)},firstScrollable:function(e){var t=r.call(this,{el:"first",dir:e});return this.pushStack(t)},smoothScroll:function(t){t=t||{};var n=e.extend({},e.fn.smoothScroll.defaults,t),r=e.smoothScroll.filterPath(location.pathname);this.unbind("click.smoothscroll").bind("click.smoothscroll",function(t){var i=this,o=e(this),u=n.exclude,a=n.excludeWithin,f=0,l=0,c=!0,h={},p=location.hostname===i.hostname||!i.hostname,d=n.scrollTarget||(e.smoothScroll.filterPath(i.pathname)||r)===r,v=s(i.hash);if(!n.scrollTarget&&(!p||!d||!v))c=!1;else{while(c&&f<u.length)o.is(s(u[f++]))&&(c=!1);while(c&&l<a.length)o.closest(a[l++]).length&&(c=!1)}if(c){t.preventDefault();e.extend(h,n,{scrollTarget:n.scrollTarget||v,link:i});e.smoothScroll(h)}});return this}});e.smoothScroll=function(t,n){var r,i,s,o,u=0,a="offset",f="scrollTop",l={},c={},h=[];if(typeof t=="number"){r=e.fn.smoothScroll.defaults;s=t}else{r=e.extend({},e.fn.smoothScroll.defaults,t||{});if(r.scrollElement){a="position";r.scrollElement.css("position")=="static"&&r.scrollElement.css("position","relative")}s=n||e(r.scrollTarget)[a]()&&e(r.scrollTarget)[a]()[r.direction]||0}r=e.extend({link:null},r);f=r.direction=="left"?"scrollLeft":f;if(r.scrollElement){i=r.scrollElement;u=i[f]()}else i=e("html, body").firstScrollable();l[f]=s+u+r.offset;r.beforeScroll.call(i,r);o=r.speed;if(o==="auto"){o=l[f]||i.scrollTop();o/=r.autoCoefficent}c={duration:o,easing:r.easing,complete:function(){r.afterScroll.call(r.link,r)}};r.step&&(c.step=r.step);i.length?i.stop().animate(l,c):r.afterScroll.call(r.link,r)};e.smoothScroll.version=t;e.smoothScroll.filterPath=function(e){return e.replace(/^\//,"").replace(/(index|default).[a-zA-Z]{3,4}$/,"").replace(/\/$/,"")};e.fn.smoothScroll.defaults=n})(jQuery);