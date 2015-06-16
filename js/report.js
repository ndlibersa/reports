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


$(document).ready(function(){
	if($('#R1')) $('#R1').floatThead();
	if($('#R2')) $('#R2').floatThead();
});



function viewReportOutput(reportOutput) {
	document.viewreport.outputType.value=reportOutput;
	document.viewreport.submit();
}

function sortRecords(columnIndex, sortOrder){
	document.viewreport.sortColumn.value=columnIndex;
	document.viewreport.sortOrder.value=sortOrder;
	document.viewreport.submit();

}
function showPubPlat(pub_plat_id, plat_id){
	window.open('pub_plat.php?pub_plat_id=' + pub_plat_id + '&plat_id=' + plat_id, "myWindow", "status = 1, height = 300, width = 450, resizable = 0, scrollbars = 1");
}
function showPopup(type, value){
	window.open('popup.php?type=' + type + '&value=' + value, "myWindow", "status = 1, height = 300, width = 450, resizable = 0, scrollbars = 1");
}

function showReportPopup(location){
	window.open(location, "myWindow", "status = 1, height = 500, width = 950, resizable = 1, scrollbars = 1");
}