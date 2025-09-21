var academicYearsUrl = '$getAcademicYearsUrl';
var programmesUrl = '$getProgrammesUrl';
var levelsUrl = '$getLevelsOfStudyUrl';
var semestersUrl = '$getSemesterUrl';
var groupsUrl = '$getGroupsUrl';
var academicYear = '';
var programmeCode = '';
var levelOfStudy = '';
var group = '';

// https://stackoverflow.com/questions/18754020/bootstrap-with-jquery-validation-plugin
$("#course-analysis-filters-form").validate({
    errorElement: "span",
    errorClass: "help-block",
    highlight: function (element, errorClass, validClass) {
        // Only validation controls
        if (!$(element).hasClass('novalidation')) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        }
    },
    unhighlight: function (element, errorClass, validClass) {
        // Only validation controls
        if (!$(element).hasClass('novalidation')) {
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
    },
    errorPlacement: function (error, element) {
        if (element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        }
        else if (element.prop('type') === 'radio' && element.parent('.radio-inline').length) {
            error.insertAfter(element.parent().parent());
        }
        else if (element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
            error.appendTo(element.parent().parent());
        }
        else {
            error.insertAfter(element);
        }
    }
});

// Get academic years
axios.get(academicYearsUrl)
.then(function (response) {
    var academicYears = response.data.academicYears;
    Object.keys(academicYears).forEach(function(key) {
        $('#academic-year').append($('<option>', { 
            value: key,
            text : academicYears[key]
        }));
    });
})
.catch(error => console.error(error));

// Get programmes
axios.get(programmesUrl)
.then(function (response){
    var programmes = response.data.programmes;
    programmes.forEach(programme => {
        $('#programme').append($('<option>', {
            value: programme.DEGREE_CODE,
            text: programme.DEGREE_CODE + ' - ' + programme.DEGREE_NAME
        }));
    });
})
.catch(error => console.error(error));

// Read seleceted academic year
$('#academic-year').on('change', function(e) {
    academicYear = $(this).val();
    if(academicYear !== '' && programmeCode !== ''){
        getLevelsOfStudy();
    }
});

// Read selected programme
$('#programme').on('change', function (e){
    programmeCode = $(this).val();
    if(academicYear !== '' && programmeCode !== ''){
        getLevelsOfStudy();
    }
});

// Read selected study level
$('#level-of-study').on('change', function (e){
    levelOfStudy = $(this).val();
     if(academicYear !== '' && programmeCode !== '' && levelOfStudy !== ''){
        getGroups();
    }
});

// Read selected group
$('#group').on('change', function (e){
    group = $(this).val();
     if(academicYear !== '' && programmeCode !== '' && levelOfStudy !== '' && group !== ''){
        getSemesters();
    }
});

// Get levels of study 
getLevelsOfStudy = function (){
    $('#level-of-study').find('option').not(':first').remove();
    axios.get(levelsUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode
        }
    })
    .then(response => {
        var levels = response.data.levels;
        levels.forEach(level => {
            $('#level-of-study').append($('<option>', {
                value: level.LEVEL_OF_STUDY,
                text: level.levelOfStudy.NAME.toUpperCase()
            }));
        })
    })
    .catch(error => console.error(error));
}

// Get students groups 
getGroups = function (){
    $('#group').find('option').not(':first').remove();
    axios.get(groupsUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode,
            level: levelOfStudy
        }
    })
    .then(response => {
        var groups = response.data.groups;
        groups.forEach(group => {
            $('#group').append($('<option>', {
                value: group.GROUP_CODE,
                text: group.group.GROUP_NAME
            })); 
        });
    })
    .catch(error => console.error(error));
}

// Get semesters 
getSemesters = function (){
    $('#semester').find('option').not(':first').remove();
    axios.get(semestersUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode,
            level: levelOfStudy,
            group: group
        }
    })
    .then(response => {
        var semesters = response.data.semesters;
        semesters.forEach(semester => {
            $('#semester').append($('<option>', {
                value: semester.SEMESTER_CODE,
                text: semester.SEMESTER_CODE + ' - ' + semester.semesterDescription.SEMESTER_DESC
            })); 
        })
    })
    .catch(error => console.error(error));
}