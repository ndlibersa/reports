<?php
function minify_output($buffer)
{
    $search = array(
        '/\>[^\S ]+/s',
        '/[^\S ]+\</s',
        '/(\s)+/s'
    );
    $replace = array(
        '>',
        '<',
        '\\1'
    );
    if (preg_match("/\<html/i",$buffer) == 1 && preg_match("/\<\/html\>/i",$buffer) == 1) {
        $buffer = preg_replace($search, $replace, $buffer);
    }
    return $buffer;
}
?>
