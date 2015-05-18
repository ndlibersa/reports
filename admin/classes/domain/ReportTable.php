<?php

class ReportTable {
    private $_outputType;
    private $_fields;
    private $_needToCheck = true;

    public function __construct($outputType, array $fields) {
        if ($fields===null || $outputType===null) {
            die("ReportTable constructor received a null param!");
        }
        $this->_outputType = $outputType;
        $this->_fields = $fields;
    }

    public function dropFields($fields) {
        $tmparr = array();

        foreach ($this->_fields as $fld) {
            if(in_array($fld,$fields))
                continue;
            $tmparr[] = $fld;
        }
        $this->_fields = $tmparr;
        $this->_needToCheck = false;
    }

    public function fields() {
        if ($this->_needToCheck) {
            die('Not ready to call fields()!');
        }
        return $this->_fields;
    }

    public function numFields() {
        if ($this->_needToCheck) {
            die('Not ready to call numFields()!');
        }
        return count($this->_fields);
    }

    public function printHeader($sortColumn,$sortOrder) {
        if ($this->_needToCheck) {
            die('Not ready to call printHeader()!');
        }
        $output = "";
        $colcount = 1;
        echo "<thead>";
        foreach ( $this->fields() as $field ) {
            echo "<th>" . ucwords(strtolower(strtr($field, '_', ' ')));
            if ($this->_outputType === 'web') {
                echo "<div><a
                    href=\"javascript:sortRecords('$colcount', 'asc');\"> <img
                    align='center' src='images/arrowdown";
                if ($sortColumn == $colcount && $sortOrder === 'asc')
                    echo '_sel';
                echo ".gif' border=0></a>&nbsp; <a
                    href=\"javascript:sortRecords('$colcount', 'desc');\"> <img
                    align='center' src='images/arrowup";
                if ($sortColumn == $colcount && $sortOrder === 'desc')
                    echo '_sel';
                echo ".gif' border=0></a></div>";
            }   
            echo "</th>";
            ++$colcount;
        }
        echo "</thead>";
    }

    //returns rather than prints the string it prepares
    public function prepareRow(array $row) {
        if ($this->_needToCheck) {
            die('Not ready to call prepareRow()!');
        }

        $tr_pieces = array("<tr class='data'><td class='sum'>","</tr>");

        $td_pieces = array();
        foreach ($row as $col_txt) {
            $td_pieces[] = "<td class='sum'>$col_txt</td>";
        }

        return array('tr'=>$tr_pieces,'td'=>$td_pieces);
    }

    public function prepare_row_as_str(array $row) {
        if ($this->_needToCheck) {
            die('Not ready to call printRow()!');
        }

        $td = '';
        foreach ($row as $col_txt) {
            $td .= "<td class='sum'>$col_txt</td>";
        }
        return "<tr class='data'>$td</tr>";
    }

    public function printRow(array $row) {
        echo $this->prepare_row_as_str($row);
    }

    public function prep_colspan_row($text,$td_opts){
        return "<tr class='data'><td colspan=" . $this->numFields() . " $td_opts>$text</td></tr>";
    }
}
