function uniapi_sendlead(formname, name, email, phone, data){
	// Функция отправки заявки
	// Парсим utm метки
	console.log('Sending form');
	url = decodeURI(document.location.search); 
		
	console.log('Sending form');
	dataStr='formname='+formname+'&name='+name+'&email='+email+'&phone='+phone+'&data='+data;
	if(url.indexOf('?') >= 0){
		console.log('Sending form');
		url = url.split('?'); 
		url = url[1]; 
		
		var namekey = ['utm_source','utm_campaign','utm_content','utm_keyword'];
		var GET = [], 
		params = [], 
		key = []; 
		console.log('Sending form');
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

	
	// ajax отправка данных на сервер
	jQuery.ajax({
			url:     "/uniapi/insertlead.php", 
			type:     "POST",
			dataType: "html", 
			data: dataStr, 
			success: function(response) { 
			//document.getElementById(result_id).innerHTML = response;
			console.log(response);
        },
        error: function(response) {
			//document.getElementById(result_id).innerHTML = "Ошибка при отправке формы";
			console.log(response);
        }
	});
	
}