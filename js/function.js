function f(){
	alert('f');
}




//Takes in a Zuora data format, and returns a readable date string
function formatZDate(dateStr){
	//2012-06-01T00:00:00.000-08:00
	return dateStr.substr(5,2) + ' / ' + dateStr.substr(8,2) + ' / ' + dateStr.substr(0,4);
}


//Logs an error to the console
function addError(emsg){
	console.log(emsg);
}