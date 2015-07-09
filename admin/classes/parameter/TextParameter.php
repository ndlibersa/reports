<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TextParameter
 *
 * @author bgarcia
 */
class TextParameter extends Parameter implements ParameterInterface {
    public function value() {
        if (isset($_REQUEST["prm_$this->id"])) {
            $val = trim(DBService::sanitize($_REQUEST["prm_$this->id"]));
            if ($val !== '') {
                return $val;
            }
        }

        return null;
    }
}
