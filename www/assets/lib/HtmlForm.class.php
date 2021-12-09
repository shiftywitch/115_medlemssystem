<?php

class HtmlForm {
    static function inputText($key, $label, $placeholder, $value, $required = true, $extraArgs = ""){
        return HtmlForm::input("text", $key, $label, $placeholder, $value, $required, $extraArgs);
    }

    static function inputEmail($key, $label, $placeholder, $value, $required = true){
        return HtmlForm::input("email", $key, $label, $placeholder, $value, $required);
    }

    static function inputTall($key, $label, $placeholder, $value, $required = true, $min = null, $max = null){
        $htmlExtra = "";
        if($min !== null){ $htmlExtra .= "min='$min' "; }
        if($max !== null){ $htmlExtra .= "max='$max'"; }
        return HtmlForm::input("number", $key, $label, $placeholder, $value, $required, $htmlExtra);
    }

    static function inputDate($key, $label, $value, $required = true, $min = null, $max = null){
        $htmlExtra = "";

        if($min == "today"){ $htmlExtra .= "min='".date('Y-m-d')."'"; }
        elseif($min !== null){ $htmlExtra .= "min='$min' "; }

        if($max == "today"){ $htmlExtra .= "max='".date('Y-m-d')."'"; }
        elseif($max !== null){ $htmlExtra .= "max='$max'"; }

        return HtmlForm::input("date", $key, $label, "YYYY-mm-dd", $value, $required, $htmlExtra);
    }
    static function inputSelect(string $key, string $label, $value, array $dataSet, bool $required = true){
        $htmlExtra = "";

        foreach ($dataSet as $assoc => $option){
            $htmlExtra .= "  <option value='$assoc' ".($assoc == $value?'selected':'').">$option</option>\n";
        }

        return HtmlForm::input("select", $key, $label, "YYYY-mm-dd", $value, $required, $htmlExtra);
    }

    private static function input($type, $key, $label, $placeholder, $value, $required = true, $htmlExtra = ""): string {
        if($required){
            $label .= " *";
        }

        $str = "<label for='$key' class='form-label'>$label</label>";

        switch ($type){
            case "select":
                $str .= "<select class='form-select' name='$key' id='$key' ".($required?"required":'').">".$htmlExtra."</select>";
                break;
            case "email":
                $str .= "<input type='email' class='form-control' name='$key' id='$key' placeholder='$placeholder' value='$value' ".($required?"required":'')." ".$htmlExtra.">";
                break;
            case "number":
                $str .= "<input type='number' class='form-control' name='$key' id='$key' placeholder='$placeholder' value='$value' ".($required?"required":'')." ".$htmlExtra.">";
                break;
            case "date":
                $str .= "<input type='date' class='form-control' name='$key' id='$key' placeholder='$placeholder' value='$value' ".($required?"required":'')." ".$htmlExtra.">";
                break;
            case "text":
            default:
                $str .= "<input type='text' class='form-control' name='$key' id='$key' placeholder='$placeholder' value='$value' ".($required?"required":'')." ".$htmlExtra.">";
                break;
        }

        return $str;
    }
}