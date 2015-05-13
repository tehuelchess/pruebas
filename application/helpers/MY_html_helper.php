<?php

function matrix_to_html($matrix){
    $html='<table>';
    foreach($matrix as $row){
        $html.='<tr>';
        foreach($row as $data){
            $html.='<td>'.$data.'</td>';
        }
        $html.='</tr>';
    } 
    $html.='</table>';
    return $html;
}