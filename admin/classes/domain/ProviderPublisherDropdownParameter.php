<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProviderPublisherParameter
 *
 * @author bgarcia
 */
class ProviderPublisherDropdownParameter extends DropdownParameter implements ParameterInterface {
    public function htmlDisplay() {
        return "<b>{$this->displayPrompt}:</b> "
            . implode(', ', $this->getPubPlatDisplayName($this->value)) . '<br/>';
    }
}
