<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author bgarcia
 */
interface ParameterInterface {
    public function value();
    public function process();
    public function form();
    public function description();
    public function ajaxGetChildUpdate();
    public function ajaxGetChildParameters();
}
