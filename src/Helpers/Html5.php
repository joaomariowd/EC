<?php
namespace EC\Helpers;

class Html5 {
    static function FormPAlert($errors, $spacer = NULL){
        
        $spacer = is_null($spacer) ? 'mt-2' : $spacer;

        $r = "<p class='alert alert-warning $spacer'>";

        foreach ($errors as $key=>$error) {
            $r .= $key . ' ' . $error[0] . '<br>';
        }

        $r .= '</p>';

        return $r;
    }
    
    static function inputs ($label = NULL, $id, $type, $obj = NULL, $placeholder = NULL) {
        $value = (is_object($obj)) ? $obj->$id : NULL;
        $label = (is_null($label)) ? NULL : "<label for = '$id'>$label</label>";
        return
            "<div class = 'form-group'>" .
            $label .
            "<input type = '$type' class = 'form-control' id = '$id' name = '$id' placeholder = '$placeholder' value= '$value'>" .
            "</div>";
    }

    static function button ($title, $type, $class = NULL) {
        return "<div class='form-group'><button type='$type' class='btn $class'>$title</button></div>";
    }
}