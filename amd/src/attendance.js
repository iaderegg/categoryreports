// Standard license block omitted.
/*
 * @package    report_categoryreports
 * @copyright  2018 iadergg@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * @module report_categoryreports/attendance
 */
define(['jquery', 
        'report_categoryreports/jszip',
        'report_categoryreports/pdfmake',
        'report_categoryreports/jquery.dataTables',
        'report_categoryreports/dataTables.buttons',
        'report_categoryreports/buttons.html5',
        'report_categoryreports/buttons.flash',
        'report_categoryreports/buttons.print',
        'report_categoryreports/bootstrap',
        'report_categoryreports/sweetalert'
        ],
        function($, jszip, pdfmake, dataTables, buttons, html5, flash, print, bootstrap, sweetalert) {
    return {
        init: function(){

            var self = this;
            $('.program_select').on('change', {self: self}, function(){self.load_courses(self, $(this))});

            $('#btn_filter_attendance').on('click', {self: self}, function(){
                self.get_attendance(self, $('#course_select').val());
            });

            $('#btn_filter_grades').on('click', {self: self}, function(){
                self.get_grades(self, $('#course_select_grader').val());
            });

        },load_courses: function(object_function, element){

            var data = {
                'function': 'get_courses',
                'id_category': element.val()
            };

            $.ajax({
                type: "POST",
                data: data,
                url: "../managers/attendance_server_proc.php",
                success: function(msg) {
                    var select_courses = '';
                    $('.course_select').html('');
                    if(Object.keys(msg).length > 0){
                        for (var i in msg){
                            select_courses += "<option value='"+msg[i].id+"'>"+msg[i].shortname+"</option>";
                        };
                        $('.course_select').append(select_courses);
                    }else{
                        $('.course_select').append("<option value=''><i>No registra cursos</i></option>");
                    }
                    
                },
                dataType: "json",
                cache: "false",
                error: function(msg) {
                    console.log(msg);
                },
            });

        },get_attendance: function(object_function, courseid){
            var data = {
                'function': 'get_attendance',
                'courseid': courseid
            };

            $.ajax({
                type: "POST",
                data: data,
                url: "../managers/attendance_server_proc.php",
                success: function(msg){
                    $('#div_table').html('');
                    $('#div_table').fadeIn(1000).append('<table id="table_attendance" class="display" cellspacing="0" width="100%"></table>');
                    $('#table_attendance').DataTable(msg.table);

                    $('#div_course_title_attendance').html('');
                    $('#div_course_title_attendance').append('<a href="/moodle/course/view.php?id='+courseid+'\"><h4>'+msg.course_fullname+'</h4></a>');
                },
                dataType: "json",
                cache: "false",
                error: function(msg) {
                    console.log(msg);
                },
            });
        },get_grades: function(object_function, courseid){
            var data = {
                'function': 'get_grader',
                'courseid': courseid
            };

            $.ajax({
                type: "POST",
                data: data,
                url: "../managers/grades_api.php",
                success: function(msg){
                    $('#div_grader').html('');
                    $('#div_grader').fadeIn(1000).append('<table id="table_grader" class="display" cellspacing="0" width="100%"></table>');
                    $("#table_grader").DataTable(msg.table);

                    $('#div_course_title_grades').html('');
                    $('#div_course_title_grades').append('<a href="/moodle/course/view.php?id='+courseid+'\"><h4>'+msg.course_fullname+'</h4></a>');
                },
                dataType: "json",
                cache: "false",
                error: function(msg) {
                    console.log(msg);
                },
            });
        }
    };
});