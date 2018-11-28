<?php

function get_course_name($id_course){
    global $DB;

    $sql_query = "SELECT fullname
    FROM  {course}
    WHERE id = $id_course";

    $fullname_course = $DB->get_record_sql($sql_query)->fullname;

    return $fullname_course;
}