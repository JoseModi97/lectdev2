(function ($, axios) {
    const form = $('#course-analysis-filters-form');

    if (!form.length || typeof axios === 'undefined') {
        return;
    }

    const config = window.courseAnalysisConfig || {};
    const urls = config.urls || {};
    const selected = config.selected || {};

    const $academicYear = $('#academic-year');
    const $programme = $('#programme');
    const $level = $('#level-of-study');
    const $group = $('#group');
    const $semester = $('#semester');

    const state = {
        academicYear: selected.academicYear || '',
        programme: selected.degreeCode || '',
        level: selected.levelOfStudy || '',
        group: selected.group || '',
        semester: selected.semester || '',
    };

    function clearSelect($select) {
        $select.find('option').not(':first').remove();
        $select.val('');
    }

    function setValidation() {
        if (typeof $.fn.validate !== 'function') {
            return;
        }

        form.validate({
            errorElement: 'span',
            errorClass: 'help-block',
            highlight: function (element) {
                if (!$(element).hasClass('novalidation')) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                }
            },
            unhighlight: function (element) {
                if (!$(element).hasClass('novalidation')) {
                    $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                }
            },
            errorPlacement: function (error, element) {
                if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else if (element.prop('type') === 'radio' && element.parent('.radio-inline').length) {
                    error.insertAfter(element.parent().parent());
                } else if (element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
                    error.appendTo(element.parent().parent());
                } else {
                    error.insertAfter(element);
                }
            }
        });
    }

    function populateAcademicYears() {
        if (!urls.academicYears) {
            return Promise.resolve();
        }

        return axios.get(urls.academicYears)
            .then(function (response) {
                const academicYears = response.data.academicYears || {};
                clearSelect($academicYear);

                Object.keys(academicYears).forEach(function (key) {
                    $academicYear.append(new Option(academicYears[key], key));
                });

                if (state.academicYear && Object.prototype.hasOwnProperty.call(academicYears, state.academicYear)) {
                    $academicYear.val(String(state.academicYear));
                } else {
                    state.academicYear = '';
                }
            })
            .catch(console.error);
    }

    function populateProgrammes() {
        if (!urls.programmes) {
            return Promise.resolve();
        }

        return axios.get(urls.programmes)
            .then(function (response) {
                const programmes = response.data.programmes || [];
                clearSelect($programme);

                programmes.forEach(function (programme) {
                    $programme.append(new Option(
                        programme.DEGREE_CODE + ' - ' + programme.DEGREE_NAME,
                        programme.DEGREE_CODE
                    ));
                });

                const exists = programmes.some(function (programme) {
                    return String(programme.DEGREE_CODE) === String(state.programme);
                });

                if (state.programme && exists) {
                    $programme.val(String(state.programme));
                } else {
                    state.programme = '';
                }
            })
            .catch(console.error);
    }

    function populateLevels() {
        clearSelect($level);
        clearSelect($group);
        clearSelect($semester);

        if (!urls.levels || !state.academicYear || !state.programme) {
            return Promise.resolve();
        }

        return axios.get(urls.levels, {
            params: {
                year: state.academicYear,
                degreeCode: state.programme
            }
        })
            .then(function (response) {
                const levels = response.data.levels || [];

                levels.forEach(function (level) {
                    const optionValue = level.LEVEL_OF_STUDY;
                    const optionLabel = level.levelOfStudy ? level.levelOfStudy.NAME.toUpperCase() : optionValue;
                    $level.append(new Option(optionLabel, optionValue));
                });

                const exists = levels.some(function (level) {
                    return String(level.LEVEL_OF_STUDY) === String(state.level);
                });

                if (state.level && exists) {
                    $level.val(String(state.level));
                    return populateGroups();
                }

                state.level = '';
                return Promise.resolve();
            })
            .catch(console.error);
    }

    function populateGroups() {
        clearSelect($group);
        clearSelect($semester);

        if (!urls.groups || !state.academicYear || !state.programme || !state.level) {
            return Promise.resolve();
        }

        return axios.get(urls.groups, {
            params: {
                year: state.academicYear,
                degreeCode: state.programme,
                level: state.level
            }
        })
            .then(function (response) {
                const groups = response.data.groups || [];

                groups.forEach(function (group) {
                    const optionValue = group.GROUP_CODE;
                    const optionLabel = group.group ? group.group.GROUP_NAME : optionValue;
                    $group.append(new Option(optionLabel, optionValue));
                });

                const exists = groups.some(function (group) {
                    return String(group.GROUP_CODE) === String(state.group);
                });

                if (state.group && exists) {
                    $group.val(String(state.group));
                    return populateSemesters();
                }

                state.group = '';
                return Promise.resolve();
            })
            .catch(console.error);
    }

    function populateSemesters() {
        clearSelect($semester);

        if (!urls.semesters || !state.academicYear || !state.programme || !state.level || !state.group) {
            return Promise.resolve();
        }

        return axios.get(urls.semesters, {
            params: {
                year: state.academicYear,
                degreeCode: state.programme,
                level: state.level,
                group: state.group
            }
        })
            .then(function (response) {
                const semesters = response.data.semesters || [];

                semesters.forEach(function (semester) {
                    const description = semester.semesterDescription ? semester.semesterDescription.SEMESTER_DESC : '';
                    const optionLabel = semester.SEMESTER_CODE + (description ? ' - ' + description : '');
                    $semester.append(new Option(optionLabel, semester.SEMESTER_CODE));
                });

                const exists = semesters.some(function (semester) {
                    return String(semester.SEMESTER_CODE) === String(state.semester);
                });

                if (state.semester && exists) {
                    $semester.val(String(state.semester));
                } else {
                    state.semester = '';
                }
            })
            .catch(console.error);
    }

    $academicYear.on('change', function () {
        state.academicYear = $(this).val();
        state.level = '';
        state.group = '';
        state.semester = '';
        populateLevels();
    });

    $programme.on('change', function () {
        state.programme = $(this).val();
        state.level = '';
        state.group = '';
        state.semester = '';
        populateLevels();
    });

    $level.on('change', function () {
        state.level = $(this).val();
        state.group = '';
        state.semester = '';
        populateGroups();
    });

    $group.on('change', function () {
        state.group = $(this).val();
        state.semester = '';
        populateSemesters();
    });

    $semester.on('change', function () {
        state.semester = $(this).val();
    });

    form.on('reset', function () {
        state.academicYear = '';
        state.programme = '';
        state.level = '';
        state.group = '';
        state.semester = '';
        setTimeout(function () {
            clearSelect($academicYear);
            clearSelect($programme);
            clearSelect($level);
            clearSelect($group);
            clearSelect($semester);
            populateAcademicYears().then(populateProgrammes);
        });
    });

    setValidation();

    populateAcademicYears()
        .then(populateProgrammes)
        .then(function () {
            if (state.academicYear && state.programme) {
                return populateLevels();
            }
            return Promise.resolve();
        })
        .then(function () {
            if (state.level) {
                return populateGroups();
            }
            return Promise.resolve();
        })
        .then(function () {
            if (state.group) {
                return populateSemesters();
            }
            return Promise.resolve();
        })
        .catch(console.error);
})(jQuery, window.axios);
