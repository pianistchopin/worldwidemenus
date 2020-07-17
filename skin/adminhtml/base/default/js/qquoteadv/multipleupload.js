
function newUploadRow(incrementId){
    var table = document.getElementById("fileUpload").getElementsByTagName('tbody')[0];
    var newRow = table.insertRow(-1);
    newTextCell('file_title_' + (incrementId + 1));
    newFileCell('file_path_' + (incrementId + 1));

    function newTextCell(name) {
        var newCell  = newRow.insertCell(-1);
        newCell.innerHTML = '<input maxlength="150" class="file_title" id="'+name+'" name="'+name+'" type="text" style="min-width: 175px; margin-top:5px;" />';
    }

    function newFileCell(name) {
        var newCell  = newRow.insertCell(-1);
        newCell.innerHTML = "<input type='file' id='"+name+"' name='"+ name +"' style=\"min-width: 175px; margin-top:5px;\"'>";
    }

    fileRowNumber = (incrementId+1);
}

//
//function checkUploadTitle() {
//    if ($('url_path').value != "" || $('file_path').value != "") {
//        if (!$('file_title').hasClassName('required-entry')) {
//            $('file_title').addClassName('required-entry');
//        }
//        var xForm = new varienForm('edit_form', '');
//        if (xForm.validator && !xForm.validator.validate()) {
//            $('loading-mask').hide();
//            return false;
//        }
//    } else {
//        if ($('file_title').hasClassName('required-entry')) {
//            $('file_title').removeClassName('required-entry');
//        }
//    }
//    return true;
//}
