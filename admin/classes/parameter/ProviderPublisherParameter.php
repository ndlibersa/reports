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
class ProviderPublisherParameter extends DropdownParameter implements ParameterInterface {

    public function description() {
        return "<b>" . $this->prompt . ":</b> "
            . implode(', ', $this->getPubPlatDisplayName($this->value)) . '<br/>';
    }
}
