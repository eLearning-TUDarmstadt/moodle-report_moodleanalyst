/**
 * Einige Funktionen zum Formatieren von JavascriptTimestamps
 * 
 */

function getDisplayDate(Unixtimestamp) {
	var date = new Date(Unixtimestamp*1000);
	
	var tag = date.getDate();
	var monat = date.getMonth() + 1;
        
	var jahr = date.getFullYear();
	
	var stunde = date.getHours();
	var minute = date.getMinutes();
	var sekunde = date.getSeconds();
	
	if(tag < 10) {
		tag = "0" + tag;
	}
	if(monat < 10) {
		monat = "0" + monat;
	}
	
	var string = tag + "." + monat + "." + jahr + " " + stunde + ":" + minute + ":" + sekunde;
	
	return string;
}

function getTimeBetweenNowAndUnixtimetamp(Unixtimestamp) {
	var akt = new Date();
	var start = new Date(Unixtimestamp*1000);
	var diff = akt.getTime() - start.getTime();
	
	var tag = Math.floor(diff / (1000*60*60*24));
	diff = diff % (1000*60*60*24);
	var std = Math.floor(diff / (1000*60*60));
	diff = diff % (1000*60*60);
	var min = Math.floor(diff / (1000*60));
	diff = diff % (1000*60);
	var sec = Math.floor(diff / 1000);
	var mSec = diff % 1000;
	
	var string = "Vor " + tag;
	if(tag > 1) {
		string = string + " Tagen ";
	} 
	else {
		string = string + " Tag ";
	}
	string = string + std;
	if(std > 1) {
		string = string + " Stunden ";
	}
	else {
		string = string + " Stunde ";
	}
	string = string + min;
	if(min > 1) {
		string = string + " Minuten";
	}
	else {
		string = string + " Minute";
	}
	return string;
}

function DateAndTimeBetween(Unixtimestamp) {
	return getDisplayDate(Unixtimestamp) + "<br />(" + getTimeBetweenNowAndUnixtimetamp(Unixtimestamp) + ")";
}


/**
 * Handelt es sich um den Browser name?
 * @param name MSIE, opera, safari, firefox
 * @returns {Boolean}
 */
function checkBrowserName(name){  
	   var agent = navigator.userAgent.toLowerCase();  
	   if (agent.indexOf(name.toLowerCase())>-1) {  
	     return true;  
	   }  
	   return false;  
	 }  