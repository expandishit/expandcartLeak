window.addEventListener('load', function () {
    var time_duration = 200;
    var input_search_element = $('input[name=search]');
    var current_keyword = null;
    var timeout_interval = null;
    var lableb_style = document.createElement('style');
    lableb_style.innerHTML = `
        .lableb-results ,  div form .lableb-results {
            display:none;
            background:#FFF;
            position: absolute;
            width: 100%;
            padding: 5px;
            z-index: 999999;
            overflow-y: auto;
            max-height: 300px;
            top: 35px;
        }
        p .lableb-results , div .lableb-results{
            display:none;
            background:#FFF;
            position: absolute;
            width: 100%;
            padding: 5px;
            z-index: 999999;
            overflow-y: auto;
            max-height: 100px;
            top: 90px;
        }
        form#search-form div .lableb-results{
            display:none;
            background:#FFF;
            position: absolute;
            width: 100%;
            padding: 5px;
            z-index: 999999;
            overflow-y: auto;
            max-height: 300px;
            top: 90px;
        }
        .lableb-results .search-result {
            padding: 5px 0;
            overflow:hidden;
            cursor:pointer;
            transition: all .3s ease;
        }

        .lableb-results .search-result:hover {
            background-color: #F1F1F1;
        }

        .lableb-results .image-holder {
            float:left;
            width:25%;
            padding: 0 5px;
        }

        .lableb-results .image-holder img {
            max-width: 100%;
            float: left;
        }

        .lableb-results .content-holder {
            float:left;
            width:75%;
        }

        .lableb-results p {
            font-size:12px;
        }

        .lableb-results .highlight {
            background: yellow;
        }
    `;
    document.body.appendChild(lableb_style);
    input_search_element.on('keyup', function () {
        $(".lableb-results").remove(); // to prevent search results overlay side search box results
        if ($(this).parent().find(".lableb-results").length === 0) {
            $(this).parent().append('<div class="lableb-results"></div>');
        }
        current_keyword = $(this).val();
        if (current_keyword == '') {
            return;
        }
     
        clearTimeout(timeout_interval);
        timeout_interval = setTimeout(function () {
            $.ajax({
                method: 'GET',
                url: window.origin + '/index.php?route=module/lableb/autoComplete',
                data: {
                    search_text: current_keyword
                },
                success: function (response) { 
                    response = JSON.parse(response);
    
                     var data = '';
                     var showAll = '';
                    if (response.products) {
                        $.each(response.products, function (index, value) {
                            data += `
                            <div class="search-result" data-link="${value.link}">
                                <div class="image-holder">
                                    <img src="${value.image}" height="40" width="40"/>
                                </div>
                                <div class="content-holder">
                                    <strong>${ value.name}</strong>
                                    <p>${value.description ? value.description.slice(0, 100) + '...' : ''}</p>
                                    <span class="label label-success">${value.price ? value.price : ''}</span>
                                </div>
                            </div>
                            <hr>
                            `;
                        });
                    }
                    $('.fast-finder-results').hide();
                    $('.lableb-results').html(data).css('display', 'block');
                }
            });
        }, time_duration);
    });

    input_search_element.on('blur', function () {
        var element = $(this);
        setTimeout(function () {
            element.parent().find('.lableb-results').hide();
        }, 250);
    });

    input_search_element.on('focus', function () { 
        $(this).parent().find('.lableb-results').show();
       
    });

    $(document).on('click', '.search-result', function () {
        window.location.href = $(this).data('link');
    });

    $('.button-search').bind('click', function() {
        url = $('base').attr('href') + 'index.php?route=product/search' ;
        var search = $('input[name="search"]:visible').val();
        if (search) {
            url += '&lableb_search=' + encodeURIComponent(search);
        }
        location = url;
    });

    input_search_element.on('keydown', function (e) {
        if (e.which == 13 || e.keyCode == 13) {
            //code to execute here
            url = $('base').attr('href') + 'index.php?route=product/search' ;
            var search = $('input[name="search"]:visible').val();
            var side_search_keyword = $('.search-params input[name=\'search\']').val();
            if (search) {
                url += '&lableb_search=' + encodeURIComponent(search);
            }else if(side_search_keyword){
                url += '&lableb_search=' + encodeURIComponent(side_search_keyword);
            }else{
                url += '&lableb_search=' + encodeURIComponent('*');
            }
            location = url;
        }
    });

});
