// usage: log('inside coolFunc', this, arguments);
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function(){
	log.history = log.history || [];   // store logs to an array for reference
	log.history.push(arguments);
  	if(this.console) {
    	arguments.callee = arguments.callee.caller;
    	var newarr = [].slice.call(arguments);
    	(typeof console.log === 'object' ? log.apply.call(console.log, console, newarr) : console.log.apply(console, newarr));
  	}
};

// make it safe to use console.log always
(function(b){function c(){}for(var d="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,timeStamp,profile,profileEnd,time,timeEnd,trace,warn".split(","),a;a=d.pop();){b[a]=b[a]||c}})((function(){try
{console.log();return window.console;}catch(err){return window.console={};}})());

var upBase;

;(function(){

	function UpBase(){
		this.init();
	}

	UpBase.prototype.init = function() {
		this.initClassFixing();
	};

	UpBase.prototype.initClassFixing = function(){
		var first_last = new Array('table tr', 'table td', 'dl dt', 'ul li', '.table_container .table_default');
		for (var i=0; i<first_last.length; i++){
			var f = first_last[i];
			$(f+":first-child").addClass("first");
			$(f+":last-child").addClass("last");
		}
		$("table tr:odd").addClass("odd");
	}

	$(document).ready(function() {
		upBase = new UpBase();
	}); /* end jQuery functions */

})();

/* Universal Functions */

function trace(msg){
	try{console.log(msg);} catch(e){}
}

Array.prototype.getRandom = function(){
	var r = Math.floor(Math.random() * this.length);
	return this[r];
}

Array.prototype.getLast = function(){
	var l = this.length - 1;
	return this[l];
}

Array.prototype.remove = function(removeMe){
	var index = this.indexOf(removeMe);
	if (index > -1){
		this.splice(index, 1);
	}
	return this;
}

Array.prototype.fill = function(size, oneBased){
	for (var i = 0; i<size; i++){
		var j = i;
		if (oneBased){
			j = i + 1;
		}
		this.push(j);
	}
	return this;
}