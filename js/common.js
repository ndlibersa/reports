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

function genericGetById() {
    if (arguments.length===0) {
        throw "genericGetById() : Did not receive any id parameters"
    }

    if (! document.getElementById) {
        if (document.all) {
            document.getElementById = function(id) {
                return document.all[id];
            }
        } else if (document.layers) {
            document.getElementById = function(id) {
                return document.layers[id];
            }
        } else {
            throw "genericGetById() : Document missing all supported ways for retrieving an element";
        }
    }
    var elems = [];
    for (var i=0; i<arguments.length; ++i) {
        elems[i] = document.getElementById(arguments[i]);
        if(! elems[i]) {
            throw "genericGetById() : id '" + arguments[i] + "' is undefined";
        }
    }
    if (elems.length===1) {
        return elems[0];
    }
    return elems;
}
