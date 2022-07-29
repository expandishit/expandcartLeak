window.addEventListener('load', function () {
    var timeoutDuration = 200;
    var inputSearchElement = $('input[name=search]');
    var currentKeyword = null;
    var timeout = null;
    var fastFinderStyle = document.createElement('style');
    fastFinderStyle.innerHTML = `
        .fast-finder-results {
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

        .fast-finder-results .search-result {
            padding: 5px 0;
            overflow:hidden;
            cursor:pointer;
            transition: all .3s ease;
        }

        .fast-finder-results .search-result:hover {
            background-color: #F1F1F1;
        }

        .fast-finder-results .image-holder {
            float:left;
            width:25%;
            padding: 0 5px;
        }

        .fast-finder-results .image-holder img {
            max-width: 100%;
            float: left;
        }

        .fast-finder-results .content-holder {
            float:left;
            width:75%;
        }

        .fast-finder-results p {
            font-size:12px;
        }

        .fast-finder-results .highlight {
            background: yellow;
        }

        .fast-finder-results .content-holder .label {
            margin: 0 3px;
        }
    `;
    document.body.appendChild(fastFinderStyle);
    inputSearchElement.on('keyup', function () {
        if ($(this).parent().find(".fast-finder-results").length === 0) {
            $(this).parent().append('<div class="fast-finder-results"></div>');
        }
        currentKeyword = $(this).val();
        if (currentKeyword == '') {
            return;
        }
        currentKeyword = currentKeyword.replace(/[٠١٢٣٤٥٦٧٨٩]/g, function(d) {
            return d.charCodeAt(0) - 1632;
        });
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            $.ajax({
                method: 'POST',
                url: window.origin + '/index.php?route=module/fast_finder/search',
                data: {
                    search_text: currentKeyword
                },
                success: function (response) {
                    response = JSON.parse(response);
                    var data = '';
                    var showAll = '';
                    if (response.no_results) {
                        //Comment no results message so user can click search button
                        //data = `<p class="text-center">${response.no_results}</p>`;
                    } else {
                        $.each(response.data, function (index, value) {
                            var regexPattern = new RegExp(currentKeyword, 'ig');
                            if(value.special !== ''){
                                price_html = `<span class="label label-success">${value.special ? value.special : ''}</span>
                                <span class="label label-danger"><del class="search-del">${value.price ? value.price : ''}</del></span>`;
                            }else{
                                price_html = `<span class="label label-success">${value.price ? value.price : ''}</span>`;
                            }
                            data += `
                            <div class="search-result" data-link="${ value.link }">
                                <div class="image-holder">
                                    <img src="${ value.image }" height="40" width="40"/>
                                </div>
                                <div class="content-holder">
                                    <strong>${ value.name.replace(regexPattern, '<strong class="highlight">'+currentKeyword+'</strong>') }</strong>
                                    <p>${value.description ? value.description.slice(0, 100).replace(regexPattern, '<strong class="highlight">'+currentKeyword+'</strong>') + '...' : '' }</p>
                                    `+price_html+`
                                    <span class="label label-default">${value.quantity ? value.quantity : ''}</span>
                                </div>
                            </div>
                            <hr>
                            `; 
                        });
                        showAll = `<p class="text-center"><a href="${response.show_all.link}"><i class="fa fa-search"></i> ${response.show_all.text}</a></p>`;
                    }
                    data += showAll;
                    $('.fast-finder-results').html(data).css('display', 'block');
                }
            });
        }, timeoutDuration);
    });

    inputSearchElement.on('blur', function () {
        var element = $(this);
        setTimeout(function () {
            element.parent().find('.fast-finder-results').hide();
        }, 250);
    });

    inputSearchElement.on('focus', function () {
        $(this).parent().find('.fast-finder-results').show();
    });

    $(document).on('click', '.search-result', function () {
        window.location.href = $(this).data('link');
    });

});
