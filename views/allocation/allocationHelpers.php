<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Lecturer allocations and management of allocation requests
 * @todo Move each functionality back to its respective view file
 */

/* @var string $deptCode */

use yii\bootstrap5\Modal;
use yii\helpers\Url;

echo $this->render('_lecturerAssign', ['deptCode' => $deptCode]);
echo $this->render('_externalLecturerAssign', ['deptCode' => $deptCode]);



?>
<!-- END LECTURER ASSIGN/MANAGE/REQUEST -->

<!-- START PAGE CSS AND JS -->
<?php
Modal::begin([
    'title' => '<b>Allocated Lecturers</b>',
    'id' => 'modal',
    'size' => 'modal-md',
    'options' => ['data-backdrop' => "static", 'data-keyboard' => "false"],
]);
echo "<div id='modalContent'></div>";
Modal::end();

// PHP to JS variables
$allocateLecturerAction = Url::to(['/allocation/allocate-request-lecturer']);
$courseDetailsAction = Url::to(['/allocation/course-details']);

$deptCoursesScript = <<< JS
    var requestId;
    var marksheetId;
    var courseType;
    var internalLecturer = true;
    var externalLecturer = false;

    // Get course details 
    const getCourseDetails = function(){
        $('.content-loader').html('');
        $('.content-loader').removeClass('alert-danger');
        $('.content-loader').html('<h5 class="text-center text-primary" style="font-size: 100px;">'
         + '<i class="fas fa-spinner fa-pulse"></i></h5>');
        let courseDetailsAction = '$courseDetailsAction';
        let queryData = {
            'marksheetId' : marksheetId,
        };
        $.ajax({
            type : 'POST',
            url : courseDetailsAction,
            data : queryData,
            dataType : 'json',
            encode : true             
        })
        .done(function(response){
            $('.content-loader').html('');
            if(response.status === 200){
                $('.allocate-lecturer-loader').html('');
                $('.lecturer-allocation-marksheet-id').html(response.data.marksheetId);
                $('.lecturer-allocation-course-name').html(response.data.courseName);
                $('.lecturer-allocation-course-code').html(response.data.courseCode); 
            }else{
                $('.content-loader').addClass('alert-danger');
                $('.content-loader').html('<p>' + response.message + '</p>');
            }
        })
        .fail(function(){});
    }

    // Display modal form to:
    // Allocate a course lecturer from within the department or 
    // Request for a course lecturer from another department or both.
    function assignLecturer(){
        requestId = $(this).attr("data-id");
        marksheetId = $(this).attr("data-marksheetId");
        courseType = $(this).attr("data-type");  
        getCourseDetails();
        $('#allocate-course-lecturers-form').trigger("reset");
        $('.select-lecturers').show();
        $('.select-departments').hide();
        $('#allocate-course-lecturers-modal').modal('show');
        // Track the course lecturer source
        $('input[type="checkbox"]').click(function(e){
            let lecturerType = $(this).val();
            if($(this).prop("checked") == true){
                if(lecturerType == 'internal'){
                    $('.select-lecturers').show();
                    internalLecturer = true;
                }
                if(lecturerType == 'external'){
                    $('.select-departments').show();
                    externalLecturer = true;
                }
            }
            else if($(this).prop("checked") == false){
                if(lecturerType == 'internal'){
                    $('.select-lecturers').hide();
                    internalLecturer = false;
                }
                if(lecturerType == 'external'){
                    $('.select-departments').hide();
                    externalLecturer = false;
                }
            }
        });
    }

    $(document).on('click', '.assign-lecturer', function(e){
        e.preventDefault();
        assignLecturer.call(this);
    });

    // Allocate a course lecturer from within the department or
    // Request for a course lecturer from another department or both.
    $('#submit-internal-lecturers-or-requests').click(function(e){
        e.preventDefault();
        let formAction = '$allocateLecturerAction';
        let formData = {
            'requestId'         : requestId,
            'marksheetId'       : marksheetId,
            'courseType'        : courseType,
            'externalLecturer'  : externalLecturer,
            'internalLecturer'  : internalLecturer,
            '_csrf'             : $('input[type=hidden][name=_csrf]').val(),
            'lecturers'         : $('#lecturer-assigned').val(),
            'department'        : $('#service-dept').val()
        };
        
        if(!externalLecturer && !internalLecturer){
            alert('Atleast one lecturer must be provided or a servicing department specified.');
        }
        if(formData.externalLecturer && formData.department == ''){
            alert('If you are requesting for a lecturer from another department, the servicing department must be provided.');
        }
        if(formData.internalLecturer && formData.lecturers == 0){
            alert('If the lecturer(s) come from your department, you must provide atleast one.');
        }

        let confirmMsg = '';
        if(externalLecturer && internalLecturer){
            confirmMsg = 'Are you sure you want to allocate the selected lecturer(s) and request for another?';
        }else if(externalLecturer){
            confirmMsg = 'Are you sure you want to request for this course\'s lecturer?';
        }else if(internalLecturer){
            confirmMsg = 'Are you sure you want to allocate the selected lecturer(s)?';
        }

        if(externalLecturer || internalLecturer){
            if(confirm(confirmMsg)){
                $('.content-loader')
                .html('<h5 class="text-center text-primary" style="font-size: 100px;">'
                 + '<i class="fas fa-spinner fa-pulse"></i></h5>');
                $.ajax({
                    type        :   'POST',
                    url         :   formAction,
                    data        :   formData,
                    dataType    :   'json',
                    encode      :   true             
                })
                .done(function(response){
                    $('.content-loader').html('');
                    if(response.status === 500){
                        $('.content-loader').addClass('alert-danger');
                        $('.content-loader').html('<p>' + response.message + '</p>');
                    }else{
                        $('#allocate-course-lecturers-modal').on('hidden.bs.modal', function () {
                            window.location.reload();
                        }).modal('hide');
                    }
                })
                .fail(function(data){});
            }else{
                alert('Operation was cancelled.');
            }
        }
        else{
            alert('No lecturer source has been selected.');
        }
    });

    // Display modal form to allocate a course lecturer for another department 
    function assignExternalLecturer(e){
        e.preventDefault();
        $('#allocate-external-lecturers-form').trigger("reset");
        requestId = $(this).attr("data-id");
        marksheetId = $(this).attr("data-marksheetId");
        courseType = $(this).attr("data-type");
        getCourseDetails();
        $('#allocate-external-lecturers-modal').modal('show');
    }
 
    $(document).on('click', '.assign-external-lecturer', function(e){
        assignExternalLecturer.call(this, e);
    });

    // Track the lecturer request status
    $('#external-lecturer-status').on('change', function(){
        let statusName = this.value; 
        if(statusName == 'NOT APPROVED'){
            $('.select-external-lecturers ').hide();
        }else{
            $('.select-external-lecturers ').show();
        }
    });

    // Allocate a course lecturer for an external department
    $('#submit-external-lecturers').click(function(e){
        e.preventDefault();
        let formAction = '$allocateLecturerAction';
        let statusName = $('#external-lecturer-status').val();
        let remarks = $('#external-lecturer-remarks').val();
        let lecturers = $('#external-lecturer-allocated').val();
        let _csrf = $('input[type=hidden][name=_csrf]').val();
        let formData = {
            'requestId'         : requestId,
            'marksheetId'       : marksheetId,
            'courseType'        : courseType,
            '_csrf'             : _csrf,
            'lecturers'         : lecturers,
            'status'            : statusName,
            'remarks'           : remarks
        };
        if(statusName == ''){
            alert('You must provide a status for this request.');
        }
        if(statusName == 'NOT APPROVED' && remarks == ''){
            alert('You must provide remarks for any denied request.');
        }
        if(statusName == 'APPROVED' && lecturers.length == 0){
            alert('You must provide atleast one lecturer for any approved request.');
        }

        let confirmMsg = '';
        if(statusName == 'NOT APPROVED'){
            confirmMsg = 'Are you sure you want to deny this request?';
        }else { 
            confirmMsg = 'Are you sure you to allocate the selected lecturer(s)?';
        }

        if(confirm(confirmMsg)){
            $('.content-loader')
                .html('<h5 class="text-center text-primary" style="font-size: 100px;">'
                    + '<i class="fas fa-spinner fa-pulse"></i></h5>');
            $.ajax({
                type        :   'POST',
                url         :   formAction,
                data        :   formData,
                dataType    :   'json',
                encode      :   true             
            })
            .done(function(response){
                $('.content-loader').html('');
                    if(response.status === 500){
                        $('.content-loader').addClass('alert-danger');
                        $('.content-loader').html('<p>' + response.message + '</p>');
                    }else{
                        $('#allocate-external-lecturers-modal').on('hidden.bs.modal', function () {
                            window.location.reload();
                        }).modal('hide');
                    }
            })
            .fail(function(data){});
        }else{
            alert('Operation was cancelled.');
        } 
    });

    // Load modal
    const loadModal = function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        $('#modal').modal('show');
        $('#modalContent').html('<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>');
        $.get(url, function(data) {
            $('#modalContent').html(data);
        });
    }

    // View details for a requested course
    $(document).on('click', '.view-course-request', function(e){
        loadModal.call(this, e);
    });

    // Manage lecturers assigned to a course
    $(document).on('click', '.manage-lecturer', function(e){
        loadModal.call(this, e);
    });

    // Remove lecturers assigned to a course
    $(document).on('click', '.remove-lecturer', function(e){
        loadModal.call(this, e);
    });
JS;
$this->registerJs($deptCoursesScript, \yii\web\View::POS_READY);
