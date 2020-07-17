/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

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