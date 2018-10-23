<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CLC
 *
 * @author     Iader E. Garcia Gómez
 * @package    report_category_reports
 * @copyright  2017 Iader E. Garcia Gomez <iadergg@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once(__DIR__ . '/../../../config.php');
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/user/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';


function get_global_grade_book($id_curso){

    global $USER;

    $USER->gradeediting[$id_curso] = 1;

    $context = context_course::instance($id_curso);

    $gpr = new grade_plugin_return(array('type' => 'report', 'plugin' => 'user', 'courseid' => $id_curso));
    $report = new grade_report_grader($id_curso, $gpr, $context);

    $report->load_users();
    $report->load_final_grades();

    // Contiene la mitad de las columnas de la tabla
    $left_rows = $report->get_left_rows(false);
    $right_rows = $report->get_right_rows(false);

    $array_report = array();
    $array_rows = array();
    $array_columns = array();

    // Títulos de las columnas
    $array_columns[0] = array('title' => "Apellido(s)/Nombre(s)", 'name' => "fullname", 'data'=>"fullname");
    $array_columns[1] = array('title' => "Email", 'name' => "email", 'data'=>"email");

    for($i = 0; $i < count($right_rows[1]->cells); $i++){

        $column_object = array();

        $doc = new DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);

        $doc->loadHTML($right_rows[1]->cells[$i]->text);

        libxml_use_internal_errors($internalErrors);

        $domListTitles = $doc->getElementsByTagName('span');

        if($domListTitles->length > 0){

            $column_title = $domListTitles[0]->nodeValue;

        }else{
            $domListTitles = $doc->getElementsByTagName('a');

            if($domListTitles->length > 0){
                $column_title = $domListTitles[0]->nodeValue;
            }
        }

        $column_object['title'] = $column_title;
        $column_object['name'] = "item".$i;
        $column_object['data'] = "item".$i;

        array_push($array_columns, $column_object);
    }

    // Cuerpo del informe
    for($i = 3; $i < count($left_rows) - 1; $i++) {

        $array_user = array();
        $array_user['fullname'] = $left_rows[$i]->cells[0]->text;
        $array_user['email'] = $left_rows[$i]->cells[3]->text;

        for($j = 0; $j < count($right_rows[$i]->cells); $j++){

            $grade_item = '';

            $doc = new DOMDocument('1.0', 'UTF-8');
            $internalErrors = libxml_use_internal_errors(true);

            $doc->loadHTML($right_rows[$i]->cells[$j]->text);

            libxml_use_internal_errors($internalErrors);

            $domListOption = $doc->getElementsByTagName('option');

            if($domListOption->length > 0) {
                for($k = 0; $k < $domListOption->length; $k++){
                    if($domListOption->item($k)->hasAttribute('selected') &&
                        $domListOption->item($k)->getAttribute('selected') === "selected"){

                        $grade_item = $domListOption->item($k)->nodeValue;
                    }
                }
            } else {
                $domListInput = $doc->getElementsByTagName('input');

                if($domListInput->length > 0){
                    $grade_item = $domListInput[0]->getAttribute('value');
                }
            }

            if($grade_item == '') {
                $grade_item = 'N.R.';
            }

            $array_user['item'.$j] = $grade_item;

        }

        array_push( $array_rows, $array_user);

    }

    $report_grade = report_grade($array_columns, $array_rows);

    return $report_grade;

}

function report_grade($columns, $data){

    $data = array(
        "bsort" => false,
        "data"=> $data,
        "columns" => $columns,
        "select" => "false",
        "fixedHeader"=> array(
            "header"=> true,
            "footer"=> true
        ),
        "scrollX" => true,
        "scrollCollapse" => true,
        "language" =>
            array(
                "search"=> "Buscar:",
                "oPaginate" => array (
                    "sFirst"=>    "Primero",
                    "sLast"=>     "Último",
                    "sNext"=>     "Siguiente",
                    "sPrevious"=> "Anterior"
                ),
                "sProcessing"=>     "Procesando...",
                "sLengthMenu"=>     "Mostrar _MENU_ registros",
                "sZeroRecords"=>    "No se encontraron resultados",
                "sEmptyTable"=>     "Ningún dato disponible en esta tabla",
                "sInfo"=>           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty"=>      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered"=>   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix"=>    "",
                "sSearch"=>         "Buscar:",
                "sUrl"=>            "",
                "sInfoThousands"=>  ",",
                "sLoadingRecords"=> "Cargando...",
                "oAria"=> array(
                    "sSortAscending"=>  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending"=> ": Activar para ordenar la columna de manera descendente"
                )
            ),
        "autoFill"=>"true",
        "dom"=> "lifrtpB",
        "tableTools"=>array(
            "sSwfPath"=>"../../style/swf/flashExport.swf"
        ),
        "buttons"=>array(
            array(
                "extend" => "print",
                "text" => 'Imprimir',

            ),
            array(
                "extend" => "csv",
                "text" => 'CSV',
            ),
            array(
                "extend" => "excel",
                "text" => 'Excel',
                "className" => 'buttons-excel',
                "filename" => 'Export excel',
                "extension" => '.xls'
            ),
        )
    );

    return $data;

}