//move this outsite the form after dom loading
document.observe('dom:loaded', function(){
    $$('body')[0].appendChild($('customProductEdit'));
});

function newCustomProductRow(button, productName){
    if(button.className != 'disabled') {
        var table = document.getElementById("qquoteadv_product_table");
        var rows = table.getElementsByTagName('tbody').length + 1;
        var tbodies = document.getElementById("qquoteadv_product_table").getElementsByTagName('tbody');
        var tbdoyClass = 'even';
        var lastTbody = false;
        if (tbodies.length > 0) {
            lastTbody = tbodies[tbodies.length - 1]
            if (lastTbody.classList.contains('even')) {
                tbdoyClass = 'odd';
            }
        }
        var newRow = table.insertRow(rows);
        newRow.className = 'border' + ' ' + tbdoyClass;
        newRow.id = 'customProductRow';
        button.disable = true;
        button.className = 'disabled';

        function newCustomProductCell(rowClass, buttonElement) {
            console.log(buttonElement.readAttribute('id'));
            var newCell = newRow.insertCell(-1);
            newCell.innerHTML = '' +
                '<div class="item-text" style="margin: 0 0 10px 0;">' +
                '<h5 class="title"><a href="#">'+productName+'</a></h5>' +
                $('customProductEdit').innerHTML +
                '</br></br><button title="Create" type="adminhtml/widget_button" onclick="event.preventDefault(); addCustomProduct()" style=""><span><span><span>Create</span></span></span></button>  ' +
                '<button title="Create" type="adminhtml/widget_button" onclick="$('+buttonElement.readAttribute('id')+').className = \'enabled\'; event.preventDefault(); $(\'customProductRow\').remove(); " style=""><span><span><span>Cancel</span></span></span></button>' +
                '</div>';
            newCell.className = rowClass;
        }

        function newEmptyCell(value, rowClass) {
            var newCell = newRow.insertCell(-1);
            newCell.innerHTML = value;
            newCell.className = rowClass;
        }

        function newCommentCell(rowClass) {
            var newCell = newRow.insertCell(-1);
            newCell.innerHTML = '<textarea disabled rows="4" style="width:95%;">Please create the product before adding a description</textarea>';
            newCell.className = rowClass;
        }

        function newCostPriceCell(rowClass) {
            var newCell = newRow.insertCell(-1);
            newCell.innerHTML = '<span class="price price-cost" id="cost-quote-product-custom"><div style="height:25px;" id="price-cost-54">' +
                'N/A</div></span>';
            newCell.className = rowClass;
        }

        function newQtyCell(rowClass) {
            var newCell = newRow.insertCell(-1);
            newCell.innerHTML = '<div style="height:25px;"><input type="number" style="width:40px;" name="qty" value="1"></div>';
            newCell.className = rowClass;
        }

        function newPriceCell(rowClass) {
            var newCell = newRow.insertCell(-1);
            newCell.innerHTML = '<span class="price price-original" id="price-quote-product-custom"><span class="price">-</span></span>';
            newCell.className = rowClass;
        }

        function newProposalPriceCell(rowClass) {
            var newCell = newRow.insertCell(-1);
            newCell.innerHTML = '<span class="price price-original" id="price-quote-product-custom"><span class="price">-</span></span>';
            newCell.className = rowClass;
        }

        function newMarginCell(rowClass) {
            var newCell = newRow.insertCell(-1);
            newCell.innerHTML = '<div style="height:25px;" id="margin-54">0 %</div>';
            newCell.className = rowClass;
        }

        newEmptyCell('', 'a-center');
        newEmptyCell('quote-product-custom', '');
        newCustomProductCell('', button);
        newCommentCell('a-center');
        newCostPriceCell('a-right');
        newPriceCell('a-center');
        newProposalPriceCell('a-center');
        newQtyCell('a-right');
        newPriceCell('a-center');
        newMarginCell('a-right');
    }
}

