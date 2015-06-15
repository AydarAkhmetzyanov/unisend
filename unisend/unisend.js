function uniapi_sendlead(formname, name, email, phone, data){
	console.log('Sending form');
	url = decodeURI(document.location.search);
	dataStr='formname='+encodeURIComponent(formname)+'&name='+encodeURIComponent(name)+'&email='+encodeURIComponent(email)+'&phone='+encodeURIComponent(phone)+'&data='+encodeURIComponent(data)+'&referrer='+encodeURIComponent(document.referrer)+'&domain='+encodeURIComponent(document.domain);

	if(url.indexOf('?') >= 0){
		url = url.split('?');
		url = url[1];

		var namekey = ['utm_source','utm_campaign','utm_content','utm_keyword','utm_medium','utm_network','utm_placement','utm_term'];

		var GET = [],
		params = [],
		key = [];
		if(url.indexOf('#')!=-1){ url = url.substr(0,url.indexOf('#')); }
		if(url.indexOf('&') > -1){ params = url.split('&');} else {params[0] = url; }

		for (r=0; r<params.length; r++){
			for (z=0; z<namekey.length; z++){
				if(params[r].indexOf(namekey[z]+'=') > -1){
					if(params[r].indexOf('=') > -1) {
						key = params[r].split('=');
						GET[key[0]]=key[1];
					}
				}
			}
		}
		for(z=0;z<namekey.length;z++){
			if(!!GET[namekey[z]]){
				dataStr+='&'+namekey[z]+'='+GET[namekey[z]];
			}
		}
	}

	jQuery.ajax({
			url:     "/unisend/insertlead.php",
			type:     "POST",
			async:   false
			cache: false,
			data: dataStr,
			success: function(response) {
				console.log(response);
	        },
	        error: function(response) {
				console.error(response);
	        }
	});
	jQuery.ajax({
			url:     "/unisend/php_worker.php",
			type:     "GET",
			async:   false
			cache: false,
			success: function(response) {
				console.log(response);
	        },
	        error: function(response) {
				console.error(response);
	        }
	});
	return true;
}

