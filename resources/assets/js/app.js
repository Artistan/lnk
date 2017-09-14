
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example', require('./components/Example.vue'));

const app = new Vue({
    el: '#app'
});

////////////////////////////////////////////////////////
function split_op(selector){
	var plan = jQuery(selector).val();
	var plans = plan.match(/[\s\S]{1,650}\n\n/g) || [];
	var total = plans.length;
	jQuery('#split_op').empty();
	$.each(plans, function( index, value ) {
		jQuery('<h1/>', {
			html: 'OP: '+(index+1)+'/'+total,
			style: 'width: 100%; margin: 10px 0px; padding: 10px 0px;'
		}).appendTo('#split_op');
		jQuery('<textarea/>', {
			id: 'op'+index,
			html: value,
			style: 'width: 100%; margin: 1em; padding: 1em;'
		}).appendTo('#split_op');
		jQuery('<hr/>').appendTo('#split_op');
	});
}
function linkSplit(data){
	// l+k://coordinates?16224,16359&125
	var matches = /coordinates\?([0-9]*),([0-9]*)\&/i.exec(data);
	console.log(matches);
	jQuery('#originX').val(matches[1]);
	jQuery('#originY').val(matches[2]);
}
function linkRegex(ele){
	//l+k://player?12410&125
	var eleObj = jQuery(ele),
			val = eleObj.val();
	var arr = [];
	var reg = /([0-9]+)(&[0-9]*)?(,)?/g;
	while (match = reg.exec(val)) {
		arr.push(match[1]);
	}
	eleObj.val(arr.join());
}
function clear_op(){
	jQuery('#split_op').empty();
}