// Price update on option values change -->

var oldValueRemoved = false;

//Relational options reset children in case of parent changed
function resetRelationalChilds(id){
    $('.relational_option_' + id).html('<option value="">'+text_select+'</option>');
    $('#relational_option_hidden_' + id).html('');
    $('.relational_option_' + id).trigger('change');

    var child = $('.relational_option_'+id).attr('data-relationalchild');
    if(child)
        resetRelationalChilds(child);

}///////////////////////

function getVariationPrice(product_id, sl_id = 0, main_op_id = 0, opts_relational_status = 0) {

    //relational options load
    relationalOptions(sl_id, main_op_id, opts_relational_status);
    //////////////////////

    let ov_ids = [];
    Array.prototype.forEach.call($('.opt-select-variation'), (elm) => {
        if (elm.type == 'radio') {
            if ($(elm).prop('checked')) {
               ov_ids.push($(elm).data('option-value-id'));
            }
        } else {
            ov_ids.push(($(elm)).children("option:selected").data('option-value-id'));
        }
    });
    ov_ids = ov_ids.filter(val => val != undefined || val != null).join();

    $.ajax({
        url: 'index.php?route=product/product/getProductVaritionsByOvIds',
        type: 'post',
        data: {product_id, ov_ids},
        dataType: 'json',
        success: function(data) {
            let product_variation_price = data.product_variation.product_price;

            if (!product_variation_price) {
                return;
            }

            const symLeft  = $('#currency_symbols_left').val();
            const symRight = $('#currency_symbols_right').val();

            $('#price_value').val(product_variation_price);
            $('.price_display').html(symLeft + product_variation_price + symRight);

        }
    });

    // getProductVaritionsByOvIds
}

//relational options load
function  relationalOptions(sl_id, main_op_id, opts_relational_status){
    if(opts_relational_status){
        var opt_val = $('.relational_option_'+main_op_id).val();

        var rel_child = $('.relational_option_'+main_op_id).attr('data-relationalchild');
        if(rel_child)
            resetRelationalChilds(rel_child);

        //Collect option parents
        var rel_child_parents = $('.relational_option_'+rel_child).attr('data-relationalparents');
        var parentValues = '';

        if(rel_child_parents){
            var parentIds = rel_child_parents.split(',');

            for (i = 0; i < parentIds.length; i++) {
                if(parentIds[i]){
                    var currVal = $('.relational_option_'+parentIds[i]).val();
                    parentValues += currVal+',';
                }
            }
        }

        //If option has a children then load that children values
        if(rel_child && opt_val != ''){
            var product_id = $('#product_id').val();
            $('#relational_option_loading-'+rel_child).fadeIn();
            $.ajax({
                url: 'index.php?route=product/product/getRelationalOpts',
                type: 'post',
                data: {'product_id':product_id, 'option_id':main_op_id, /*'opt_v_id' : op_id,*/ 'pr_op_id':sl_id, 'child_opt':rel_child, 'parents_values':parentValues},
                dataType: 'json',
                success: function(json) {
                    if(json['options']) {
                        $('.relational_option_' + rel_child).html(json['options']);
                        $('#relational_option_hidden_' + rel_child).html(json['hiddens']);
                    }else {
                        $('.relational_option_' + rel_child).html('');
                        $('#relational_option_hidden_' + rel_child).html('');
                    }
                    $('#relational_option_loading-'+rel_child).fadeOut();
                }
            });
        }
    }
}

function getSelectPrice(op_id, sl_id, main_op_id = 0, opts_relational_status = 0){

    //relational options load
    relationalOptions(sl_id, main_op_id, opts_relational_status);
    //////////////////////////

    var oldSelectPrice  = $('#old_select_price-'+sl_id).val();
    var oldSelectprefix = $('#old_select_prefix-'+sl_id).val();

    if(oldSelectPrice != '' && oldSelectprefix != ''){
        if(oldSelectprefix == '+'){
            priceUpdate(oldSelectPrice, '-', 'radio');
        }else if(oldSelectprefix == '-'){
            priceUpdate(oldSelectPrice, '+', 'radio');
        }
    }

    //op_id = empty in case of '--select--' selected
    if(op_id){
        var newSelectPrice  = $('#select-price-'+op_id).val();
        var newSelectprefix = $('#select-prifex-'+op_id).val();
        $('#old_select_price-'+sl_id).val(newSelectPrice);
        $('#old_select_prefix-'+sl_id).val(newSelectprefix);
        priceUpdate(newSelectPrice, newSelectprefix, 'select');
    }else{
        $('#old_select_price-'+sl_id).val('');
        $('#old_select_prefix-'+sl_id).val('');
    }
}

function getRadioPrice(pr, prx, sl_id, typ){
    //product option id || product option value id -- products  builds
    if(sl_id.indexOf('|') != -1){
        var sl_id_arr = sl_id.split('|');
        sl_id = sl_id_arr[0];
        product_option_value_id = sl_id_arr[1];
        product_type = 'builds';
        quantity = $('#qty_'+sl_id).val();
    }else{
        product_type = 'product_option';
        quantity = 1;
    }
   
    var oldRadioPrice  = $('#old_radio_price-'+sl_id).val();
    var oldRadioprefix = $('#old_radio_prefix-'+sl_id).val();
    
    if( oldRadioPrice !='' && oldRadioprefix !='' && 
        (product_type == 'builds' && product_option_value_id !=  $('#radio_prefix_id-'+sl_id).val())
    ){
        //this step for updating radioPrice and radioPrefix with the newely selected options
        if(oldRadioprefix == '+'){
            priceUpdate(oldRadioPrice, '-', 'radio',quantity);
        }else if(oldRadioprefix == '-'){
            priceUpdate(oldRadioPrice, '+', 'radio',quantity);
        }
    }

    $('#old_radio_price-'+sl_id).val(pr);
    $('#old_radio_prefix-'+sl_id).val(prx);
    if(product_type == 'builds' && (product_option_value_id != $('#radio_prefix_id-'+sl_id).val() ) ){
        //calculate price 
        $('#radio_prefix_id-'+sl_id).val(product_option_value_id);
        priceUpdate(pr, prx, 'radio',quantity);
        $('#option_quantity-'+sl_id).val(quantity);
    }else if(product_type == 'builds' && (product_option_value_id == $('#radio_prefix_id-'+sl_id).val())){
        if(quantity != $('#option_quantity-'+sl_id).val()){
            //in case user select the same option with different quantity
            if(quantity > $('#option_quantity-'+sl_id).val()){
                difference = quantity - $('#option_quantity-'+sl_id).val();
                priceUpdate(pr, '+', 'radio',difference);
            }else if(quantity < $('#option_quantity-'+sl_id).val()){
                difference = $('#option_quantity-'+sl_id).val() - quantity;
                priceUpdate(pr, '-', 'radio',difference);
            }
            $('#option_quantity-'+sl_id).val(quantity);
        }else{
            //products builds in case remove a previously selected option
            $('#radio_prefix_id-'+sl_id).val('');
            $('#old_radio_price-'+sl_id).val('');
            $('#old_radio_prefix-'+sl_id).val('');
            $('#option_quantity-'+sl_id).val('');
            priceUpdate(pr, '-', 'radio',quantity);
        }
    }else if(product_type == 'product_option'){
        // not products builds -- any product with options from any other store
        if(oldRadioprefix == '+'){
            priceUpdate(oldRadioPrice, '-', 'radio',quantity);
        }else if(oldRadioprefix == '-'){
            priceUpdate(oldRadioPrice, '+', 'radio',quantity);
        }
        priceUpdate(pr, prx, 'radio',quantity);
    }
    

}

function getCheckboxPrice(pr, prx,chkd){
    if(chkd){
        if(prx == '+'){
            priceUpdate(pr, '+', 'checkbox');
        }else if(prx == '-'){
            priceUpdate(pr, '-', 'checkbox');
        }
    }else{
        if(prx == '+'){
            priceUpdate(pr, '-', 'checkbox');
        }else if(prx == '-'){
            priceUpdate(pr, '+', 'checkbox');
        }
    }
}

// Printing document get cover name and price-->
function setCoverName(nm, pr){
    var currentPrice = $('#price_value').val();
    $('#option-value-cover-name').val(nm);

    // let oldCoverPrice  = $('#old_radio_price-cover').val();
    // if(oldCoverPrice !== ''){
    //     priceUpdate(oldCoverPrice, '-', 'cover');
    // }

    if(pr !== ''){
        $('#old_radio_price-cover').val(pr);
        //priceUpdate(pr, '+', 'cover');   
    }else{
        $('#old_radio_price-cover').val('');
    }     
    calculateFinalPrice();
}

//// pages number calculation
function setPagesNumber(){
   calculateFinalPrice();
}

/////////////////////////

function priceUpdate(pr, prx, ty,quantity=1){
    
    var option_pr = 0;
    var option_pr_without_tax = 0;
    let productOptionOriginalPrice = 0;
    let productOptionOriginalPriceWithoutTax = 0;
    //Oprions price comes in this formate "price_to_apply|price_without_tax", in case no tax applied both values will be the same
    if (pr.indexOf('|') != -1) {
        var pr_arr = pr.split('|');
        pr = pr_arr[0];
        option_pr_without_tax = pr_arr[1];
        productOptionOriginalPrice = pr_arr[2];
        productOptionOriginalPriceWithoutTax = pr_arr[3];
    } else {
        option_pr_without_tax = pr;
        productOptionOriginalPrice = pr;
        productOptionOriginalPriceWithoutTax = pr;
    }
   
    if(quantity){
        pr = pr*quantity;
    }

    var currentPrice = $('#price_value').val();
    var currentOriginal = $('#original_value').val();
    if (currentOriginal !== undefined){
        //to clear currentOriginal price from curreny that make error nan
        currentOriginal=Number(currentOriginal.replace(/[^0-9\.]+/g,""));
    }

    var tax =   $('#tax_value').val();
    //let newPrice     = 0;
    var displayPrice     = currentPrice;
    var displayOriginal     = currentOriginal;

    if(prx == '+'){

        displayPrice = parseFloat(currentPrice) + parseFloat(pr);
        displayOriginal = parseFloat(currentOriginal) + parseFloat(productOptionOriginalPriceWithoutTax);
        displayTax  =  parseFloat(tax) + parseFloat(option_pr_without_tax);
        $('#price_value').val(displayPrice);
        $('#def_price_value').val(displayPrice);
        $('#original_value').val(displayOriginal);
        $('#tax_value').val(displayTax);
        $('.price-box__old').html((parseFloat($('#price-box__old').data("initial")) + parseFloat(productOptionOriginalPriceWithoutTax)).toFixed(2))
        oldValueRemoved = true;
        // if(typ !== 'cover')
        //     displayPrice = newPrice * pagesNum;
        // else
        //     displayPrice = (parseFloat(currentPrice) * pagesNum) + parseFloat(pr);

    }else if(prx == '-'){

        displayPrice     = parseFloat(currentPrice) - parseFloat(pr);
        displayOriginal = parseFloat(currentOriginal) - parseFloat(productOptionOriginalPriceWithoutTax);
        displayTax   = parseFloat(tax) -parseFloat(option_pr_without_tax) ; 
        $('#price_value').val(displayPrice);
        $('#def_price_value').val(displayPrice);
        $('#original_value').val(displayOriginal);
        $('#tax_value').val(displayTax);
        if (oldValueRemoved) {
            $('.price-box__old').html(parseFloat($('#price-box__old').data("initial")).toFixed(2))
            oldValueRemoved = false;
        }

        // if(typ !== 'cover')
        //     displayPrice = newPrice * pagesNum;
        // else
        //     displayPrice = (parseFloat(currentPrice) * pagesNum) - parseFloat(pr);

    }

    calculateFinalPrice();
}

function calculateFinalPrice(pricedef){
    if($('#def_price_value').length && pricedef){
        var price = $('#def_price_value').val();
    }else{
        var price = $('#price_value').val();     
    }
    // var price = $('#price_value').val();
    var displayPrice = 0;
    var originalPrice = $('#original_value').val();

    //in case of printing pages
    var pagesNum = $('#option-print_pages').val();

    if(parseInt(pagesNum) <= 0 || pagesNum == undefined || pagesNum == ''){
        pagesNum = 1;
        $('#option-print_pages').val(1);
    }

    var coverPrice  = $('#old_radio_price-cover').val();
    if(coverPrice && coverPrice !== ''){
        displayPrice = (parseFloat(price) * parseInt(pagesNum)) + parseFloat(coverPrice);
        displayOriginalPrice = (parseFloat(originalPrice) * parseInt(pagesNum)) + parseFloat(coverPrice);
    }else{
        displayPrice = (parseFloat(price) * parseInt(pagesNum));
        displayOriginalPrice = (parseFloat(originalPrice) * parseInt(pagesNum));
    }

    ////
    var symLeft  = $('#currency_symbols_left').val();
    var symRight = $('#currency_symbols_right').val();

	var thousand_point = ',';
	if($('#thousand_point').val() != undefined){
     thousand_point = $('#thousand_point').val();
	}

    var currency_decimal_places = $("#currency_decimal_places").val() ?? 2;
    $('.price_display').html(isNaN(displayPrice) ? '' : symLeft + numberWithThousandPoint(displayPrice.toFixed(currency_decimal_places),thousand_point) + symRight);
    $('.original_display').html(isNaN(displayOriginalPrice) ? '' : symLeft + numberWithThousandPoint(displayOriginalPrice.toFixed(currency_decimal_places),thousand_point) + symRight);

    if($('#tax_value').length > 0){
        var tax = parseFloat($('#tax_value').val());
        $('#tax_value_display').html(isNaN(tax) ? '' : symLeft + numberWithThousandPoint(tax.toFixed(currency_decimal_places),thousand_point) + symRight);
    }
}

function numberWithThousandPoint(num,thousand_point) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousand_point);
}

/////////////////////////////////////////////