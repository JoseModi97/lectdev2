// Capitalize first letter in a string
function capitaliseFirstLetter(string){
    console.log(string.charAt(0).toUpperCase() + string.slice(1));
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Delete marks
function deleteMarks(e){
    e.preventDefault();
    let url = $(this).attr('href');
    let id = $(this).attr('data-id');
    let confirmMsg = "This marks will be removed permanently from the system. Are you sure you want to proceed?";
    krajeeDialog.confirm(confirmMsg, function (result) {
        if (result) {
            $('#app-is-loading-modal-title').html('<p class="text-center font-weight-bold"> Deleting marks</p>');
            $('#app-is-loading-modal').modal('show');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    'id': id,
                },
                dataType: 'json',
                encode: true
            })
                .done(function (response) {
                    console.log(response);
                    if (response.status === 500) {
                        $('#app-is-loading-message')
                            .html('<p class="text-danger"> Delete failed. <br/>' +
                                response.message + '</p>');
                    }
                })
                .fail(function () {});
        } else {
            krajeeDialog.alert('Operation cancelled');
        }
    });
}




