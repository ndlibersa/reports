<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CheckParameter
 *
 * @author bgarcia
 */
class CheckUnadjustedParameter extends CheckboxParameter implements ParameterInterface {
    public function htmlDisplay() {
        return '<b>Numbers are not adjusted for use violations</b><br/>';
    }

    protected function flagName() {
        return "showUnadjusted";
    }
}
