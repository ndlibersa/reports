<?php

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

?>
<div class='boxRight'>
    <p class="fontText"><?= _("Change language:");?></p>
    <select name="lang" id="lang" class="dropDownLang">
       <?php
        $fr="<option value='fr' selected='selected'>"._("French")."</option><option value='en'>"._("English")."</option>";
        $en="<option value='fr'>"._("French")."</option><option value='en' selected='selected'>"._("English")."</option>";
        if(isset($_COOKIE["lang"])){
            if($_COOKIE["lang"]=='fr'){
                echo $fr;
            }else{
                echo $en;
            }
        }else{
            $defLang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);
            if($defLang=='fr'){
                echo $fr;
            }else{
                echo $en;
            }
        }
        ?>

    </select>
</div>
<script>
    $("#lang").change(function() {
        setLanguage($("#lang").val());
        location.reload();
    });

    function setLanguage(lang) {
        var wl = window.location, now = new Date(), time = now.getTime();
        var cookievalid=86400000; // 1 day (1000*60*60*24)
        time += cookievalid;
        now.setTime(time);
        document.cookie ='lang='+lang+';path=/'+';domain='+wl.host+';expires='+now;
    }
</script>
</body>
</html>