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

$("#reportID").change(function(){
	//updateParms;
	if ($("#reportID").val() != "") {
		genericGetById('div_parm').innerHTML = "<br><label for=''>&nbsp;</label><img src='images/circle.gif'>  " . _("Refreshing Contents...");
		$.ajax({
		 type:       "GET",
		 url:        "ajax_htmldata.php",
		 cache:      false,
		 data:       "action=getReportParameters&reportID=" + $("#reportID").val(),
		 success:    function(html) {
                genericGetById('div_parm').innerHTML = html;
			}
		});
	}else{
        genericGetById('div_parm').innerHTML = "";
	}
});



function clearParms() {
    genericGetById('reportID').value = "";
    genericGetById('div_parm').innerHTML = "";
}


function updateChildren(reportID,parmID){

	//first get a list of this parm's children
	$.ajax({
		 type:       "GET",
		 url:        "ajax_htmldata.php",
		 cache:      false,
		 data:       "action=getChildParameters&reportID=" + reportID + "&parentReportParameterID=" + parmID,
		 success:    function(childParms) {
			var childParmArray = childParms.split("|");

			for (var i=0; i<childParmArray.length-1; i++) {
				$.ajax({
				 type:       "GET",
				 url:        "ajax_htmldata.php",
				 cache:      false,
				 async:	     false,
				 data:       "action=getChildUpdate&reportID="+reportID+"&reportParameterID=" + childParmArray[i] + "&reportParameterVal=" + $("#prm_" + parmID).val() ,
				 success:    function(html) {
					$("#div_parm_" + childParmArray[i]).html(html);
					}
				});
			}
		}
	});
}

function moveOptions(theSelFrom, theSelTo)
{
	var selectedText = new Array();
	var selectedValues = new Array();
	var selectedCount = 0;

	var i;

	// Find the selected Options in reverse order
	// and delete them from the 'from' Select.
	for(i= theSelFrom.length-1; i>=0; i--)
	{
		if(theSelFrom.options[i].selected)
		{
			selectedText[selectedCount] = theSelFrom.options[i].text;
			selectedValues[selectedCount] = theSelFrom.options[i].value;
			if (theSelFrom.length > 0){
				theSelFrom.options[i] = null;
			}
			selectedCount++;
		}
	}

	// Add the selected text/values in reverse order.
	// This will add the Options to the 'to' Select
	// This will add the Options to the 'to' Select
	// in the same order as they were in the 'from' Select.
	for(i=selectedCount-1; i>=0; i--)
	{
		theSelTo.options[theSelTo.length] = new Option(selectedText[i], selectedValues[i]);
	}

}

function placeInHidden(delim, selStr, hidStr)
{
	var selObj = document.getElementById(selStr);
	var hideObj = document.getElementById(hidStr);
	hideObj.value = '';
  for (var i=0; i<selObj.options.length; i++) {
	   hideObj.value = (hideObj.value ==
		    '' ? selObj.options[i].value : hideObj.value + delim + selObj.options[i].value);
	}
}

function toggleLayer(whichLayer, state) {
    var elem = genericGetById(whichLayer);
    if (elem){
        elem.style.display = state;
    }
}

function daterange_onsubmit() {
	try {
		var range = genericGetById('daterange');
	} catch(err) {
		return true; // daterange not on current form
	}

	var m0 = parseInt(genericGetById('date0m').value);
	var y0 = parseInt(genericGetById('date0y').value);

	var m1 = parseInt(genericGetById('date1m').value);
	var y1 = parseInt(genericGetById('date1y').value);

	if (y0>y1 || (y0===y1&&m0>m1)) {
		alert("'from' date cannot come after 'through' date!");
		return false;
	}

	range.value =
             ('0' + m0).slice(-2) + y0
           + ('0' + m1).slice(-2) + y1;

    return true;
}
