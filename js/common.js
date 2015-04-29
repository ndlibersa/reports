/*
 **************************************************************************************************************************
 ** CORAL Usage Statistics Reporting Module v. 1.0
 **
 ** Copyright (c) 2010 University of Notre Dame
 **
 ** This file is part of CORAL.
 **
 ** CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 **
 ** CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 **
 ** You should have received a copy of the GNU General Public License along with CORAL.  If not, see <http://www.gnu.org/licenses/>.
 **
 **************************************************************************************************************************
 */

function getCookie(keyCo){
    var valCookie= ""; 
    var search= keyCo + "="; 
    if(document.cookie.length > 0) { 
        pos=document.cookie.indexOf(search); 
        if (pos != -1) {
            pos += search.length; 
            end= document.cookie.indexOf(";", pos); 
            if (end == -1) 
                end= document.cookie.length; 
            valCookie= unescape(document.cookie.substring(pos,end)) 
        }
    } 
    return valCookie; 
} 

function fLang() {
    var langBrowser = '';
    // Search the language according to the browser
    if(getCookie('lang')=='' || getCookie('lang')==undefined){
        if (navigator.languages==undefined) {
            if (navigator.language==undefined) {
                // Internet Explorer Compatibility
                langBrowser= navigator.userLanguage.slice(0,2);
            } else {
                // Old navigator compatibility
                langBrowser= navigator.language.slice(0,2);
            }
        } else { 
            // Recent navigators
            langBrowser= navigator.languages[0].slice(0,2);                                
        }
    }else{
        langBrowser = getCookie('lang');
    }
    return langBrowser;
}
var gt = new Gettext({ 'domain' : 'messages' });
function _(msgid) {
    return gt.gettext(msgid);
}

//image preloader
(function($) {
	var cache = [];
	// Arguments are image paths relative to the current page.
	$.preLoadImages = function() {
		var args_len = arguments.length;
		for (var i = args_len; i--;) {
			var cacheImage = document.createElement('img');
			cacheImage.src = arguments[i];
			cache.push(cacheImage);
		}
	}
})(jQuery)


//This prototype is provided by the Mozilla foundation and
//is distributed under the MIT license.
//http://www.ibiblio.org/pub/Linux/LICENSES/mit.license

if (!Array.prototype.indexOf)
{
	Array.prototype.indexOf = function(elt /*, from*/)
	{
    var len = this.length;

		var from = Number(arguments[1]) || 0;
		from = (from < 0)
				? Math.ceil(from)
				: Math.floor(from);
		if (from < 0)
			from += len;

		for (; from < len; from++)
		{
			if (from in this && 
					this[from] === elt)
				return from;
		}
		return -1;
	};
}