jQuery(document).ready(function($){
	var url = 'https://api.github.com/repos/jarednova/timber';
	jsonp(url);
	function addCommas(a){
		return String(a).replace(/(\d)(?=(\d{3})+$)/g,"$1,")
	}
	function jsonp(b){
		var a=document.createElement("script");
		a.src=b+"?callback=gitHubCallback";
		var head = document.getElementsByTagName("head")[0];
		head.insertBefore(a,head.firstChild)
	}
});

function gitHubCallback(a){
	var watchers = a.data.watchers;
	$('#gh-count').text(watchers);	
}