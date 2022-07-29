			
			var option_prefix = "option";
			// if ($('[name^=option_oc\\[]').length) {
			// 	option_prefix = "option_oc";
			// }
			var option_prefix_length = option_prefix.length;
			
      // в список может быть включего главное изображений, надо его учесть
			// poipImageAdditional().find('a').each(function() {
			// 	var img_src = '';
			// 	if ($(this).attr('data-zoom-image') && poip_theme_name=='mattimeo') {
			// 		img_src = $(this).attr('data-zoom-image');
			// 	}else if ($(this).attr('data-image')) {
			// 		img_src = $(this).attr('data-image');
			// 	} else if ($(this).attr('href') && $(this).attr('href').substr(0,1) != "#") {
			// 		img_src = $(this).attr('href');
			// 	}
				
			// 	if (img_src) {

			// 		var img_found = false;
			// 		for (var i=0;i<poip_images.length;i++) {
			// 			if (img_src == poip_images[i]['popup'] || decodeURIComponent(img_src) == poip_images[i]['popup']) {
			// 				img_found = true;
			// 				break;
			// 			}
			// 		}
			// 		if (!img_found) {
			// 			poip_images.unshift({"product_id":"<?php echo $product_id; ?>","product_image_id":["-1"],"popup":"","main":"","thumb":""});
			// 			poip_images[0].popup = img_src;
			// 			poip_images[0].main = img_src;
			// 			poip_images[0].thumb = img_src;
			// 		}
			// 	}
			// });
			
      
			function poip_get_product_option_id_from_name(name) {
				return name.substr(option_prefix_length+1, name.indexOf(']')-(option_prefix_length+1) )
			}
			
      // 1 - без checkbox, 2 - только чекбокс


      function poip_get_product_option_id_from_name(name) {
            return name.substr(option_prefix_length+1, name.indexOf(']')-(option_prefix_length+1) )
        }
            

      function get_selected_values(checkbox_variant, product_option_id) {
			
        var values = [];
        
        if (!checkbox_variant || checkbox_variant==1) {
          var options = $("select[name^="+option_prefix+"\\[]");
          for (var i=0; i<options.length; i++) {
						var current_product_option_id = poip_get_product_option_id_from_name($(options[i]).attr('name'));
						if ( (!product_option_id && $.inArray(current_product_option_id, poip_product_option_ids) != -1)
							|| (product_option_id && product_option_id == current_product_option_id) ) {
							
							if (options[i].value) {
								values.push(options[i].value);
							}
						}
          }
          
          var options = $("input[type=radio][name^="+option_prefix+"\\[]:checked");
          for (var i=0; i<options.length; i++) {
						var current_product_option_id = poip_get_product_option_id_from_name($(options[i]).attr('name'))
						if ( (!product_option_id && $.inArray(current_product_option_id, poip_product_option_ids) != -1)
							|| (product_option_id && product_option_id == current_product_option_id) ) {
							
							if (options[i].value) {
								values.push(options[i].value);
							}
						}
          }
        }
        
        if (!checkbox_variant || checkbox_variant==2) {
          var options = $("input[type=checkbox][name^="+option_prefix+"\\[]:checked");
          for (var i=0; i<options.length; i++) {
						var current_product_option_id = poip_get_product_option_id_from_name($(options[i]).attr('name'));
						if ( (!product_option_id && $.inArray(current_product_option_id, poip_product_option_ids) != -1)
							|| (product_option_id && product_option_id == current_product_option_id) ) {
							
							if (options[i].value) {
								values.push(options[i].value);
							}
						}
          }
        }
        
        return values;
        
      }
      
      function poip_get_global_visible_images() {
        // изображения которые должны быть видны до применения фильтра
        var global_visible_images = [];
        var images_by_settings = [];
        var selected_values = get_selected_values(); 
        
        for (var i=0; i<poip_images.length; i++) {
					
          if (poip_images[i]['product_image_id']) { // стандартное доп.изображение
            global_visible_images.push(poip_images[i]['popup']);
          } else {
            for (var product_option_id in poip_options_settings) {
              if (poip_options_settings[product_option_id]['img_use'] == 1) { // вкл изображения всех значений
                global_visible_images.push(poip_images[i]['popup']);
              } else if (poip_options_settings[product_option_id]['img_use'] == 2) { // вкл изображения только выбранных значений
                for (var j=0; j<selected_values.length; j++) {
                  if ($.inArray(selected_values[j], poip_images[i]['product_option_value_id'])!=-1) {
                    global_visible_images.push(poip_images[i]['popup']);
                  }
                }
              }
            }
          }
          
        }
        
        return global_visible_images;
        
      }
      
      function poip_array_intersection(arr1, arr2) {
        
        var match = [];
        
        $.each(arr1, function (i, val1) {
          if ($.inArray(val1, arr2) != -1) {
            match.push(val1);
          }
        });
        
        return match;
      }
      
      function poip_change_available_images(product_option_id) {
        
				
		if ($.inArray(product_option_id, poip_product_option_ids)==-1) {
          return;
        }
        
        var global_visible_images = poip_get_global_visible_images();
				
				var current_visible_images = poip_get_global_visible_images();
				
				for (var i in poip_product_option_ids) {
					var current_product_option_id = poip_product_option_ids[i];
					
					var current_product_option_selected_values = get_selected_values(0, current_product_option_id);
					
					if (poip_options_settings[current_product_option_id]['img_limit'] && poip_options_settings[current_product_option_id]['img_use']
						&& current_product_option_selected_values.length ) {
						
						var images_to_show = [];
						for (var j in poip_images) {
							if (poip_images[j]['product_option_value_id'] && poip_images[j]['product_option_value_id'].length) {
								for (var copsv_i in current_product_option_selected_values) {
									if ($.inArray(current_product_option_selected_values[copsv_i], poip_images[j]['product_option_value_id']) !== -1
										&& $.inArray(poip_images[j]['popup'], images_to_show) == -1 ) {
										images_to_show.push(poip_images[j]['popup']);
									}
								}
							}
						}
						current_visible_images = poip_array_intersection(current_visible_images, images_to_show);
					}
					
				}
				
				if (current_visible_images.length == 0) {
                    current_visible_images = global_visible_images;
				}
				
				//poip_set_visible_images(current_visible_images);
				
				return current_visible_images;
				
				/*
				
        if ($.inArray(product_option_id, poip_product_option_ids)==-1) {
          return;
        }
        
        var global_visible_images = poip_get_global_visible_images();
        var selected_values = get_selected_values(1);
        var selected_checks = get_selected_values(2);
        
        
        if (poip_options_settings[product_option_id]['img_limit'] && poip_options_settings[product_option_id]['img_use']) {
        
          // соберем все изображения подходящие для чекбоксов
          var images_for_checks = [];
          
          if (selected_checks.length) {
            for (var j=0; j<poip_images.length; j++) {
              var image_ok_all = true;
              for (var i=0; i<poip_product_option_ids.length; i++) {
                var check_option_id = poip_product_option_ids[i];
                
                if ($('input[type=checkbox][name^='+option_prefix+'\\['+check_option_id+'\\]]:checked').length) {
                  var image_ok_one = false;
                  $('input[type=checkbox][name^='+option_prefix+'\\['+check_option_id+'\\]]:checked').each( function() {
                    image_ok_one = (image_ok_one || ($.inArray($(this).val(), poip_images[j]['product_option_value_id'])!=-1));
                  });
                  image_ok_all = image_ok_all && image_ok_one;
                }
              }
              
              if (image_ok_all) {
                images_for_checks.push(poip_images[j]['popup']);
              }
            }
          }
          
          // из изображений для чекбоксов отфильтруем остальные
          var images_to_show = [];
          if (selected_values.length ) {
          
            for (var i=0;i<poip_images.length;i++) {
              if (poip_images[i]['product_option_value_id']) {
                var show_image = true;
                for (var j=0;j<selected_values.length;j++) {
                  if ($.inArray(selected_values[j], poip_images[i]['product_option_value_id'])==-1) {
                    show_image = false;
                    break;
                  }
                }
                if (show_image) {
                  if (!selected_checks.length || $.inArray(poip_images[i]['popup'], images_for_checks)!=-1 ) {
                    images_to_show.push(poip_images[i]['popup']);
                  }
                }
              }
            }
            
          } else {
            if (selected_checks.length) {
              images_to_show = images_for_checks;
            }
          }
          
          if (images_to_show.length == 0) {
            images_to_show = global_visible_images;
          } else {
            images_to_show = poip_array_intersection(images_to_show, global_visible_images);
          }
          
        } else {
          images_to_show = global_visible_images;
          
        }
        
        poip_set_visible_images(images_to_show);
        
        
        return images_to_show;
				*/
      }

      function poip_set_visible_images(images) {
        
                // here is the correct place of work !!
                

				// << pav fashion theme compatibility
				if (1==1) {
					
					var image_additional_carousel = $('#image-additional-carousel, #image-additional .carousel-inner');
					
					// first time - copy all images to hidden element
					if ( !$('#hidden-carousel').length ) {
					
						// count elements per item
						var images_per_item = Math.max(3, $('#image-additional').find('.item').first().find('a').length);
						
						$("#image-additional").after("<div style='display:none' id='hidden-carousel' images_per_item='"+images_per_item+"'></div>");
						
						// , #image-additional .carousel-inner - for quickview
						image_additional_carousel.find('a').each( function(){
							$('#hidden-carousel').append( poip_outerHTML($(this)) );
						});
					};
						
					// add visible images to carousel
					var pg_html = "";
					var pg_added = [];
					var anchors_cnt = 0;
					var images_per_item = $('#hidden-carousel').attr('images_per_item');
					$('#hidden-carousel').find('a').each( function(){
						// Учтем возможность спец символов типа пробела %20
						if ($.inArray( $(this).attr('href'), images) != -1 || $.inArray(decodeURIComponent($(this).attr('href')), images) != -1) {
							if ($.inArray($(this).attr('href'), pg_added) == -1) { // чтобы изображения не дублировались
							
								if (anchors_cnt%images_per_item==0) {
									if (anchors_cnt>0) pg_html = pg_html + "</div>";
									pg_html = pg_html + "<div class='item'>";
								}
							
								// show
								pg_html = pg_html + poip_outerHTML($(this));
								pg_added.push($(this).attr('href'));
								//pg_html = pg_html + poip_outerHTML($(this).parent());
								
								anchors_cnt++;
							}
						}
					});
					if (pg_html != "") {
						pg_html = pg_html + "</div>";
					}
					//console.debug(pg_html);
					
					if (pg_html != image_additional_carousel.html()) {
					
						// refresh prev/next
						//var prev_next_html = "";
						//$("#image-additional").find(".carousel-control").each(function () {
						//	prev_next_html+= poip_outerHTML($(this));
						//});
						//$("#image-additional").find(".carousel-control").remove();
						//$("#image-additional").append(prev_next_html);
					
						image_additional_carousel.html(pg_html);
						if (image_additional_carousel.find('.item').length>1) {
							$("#image-additional").find(".carousel-control").show();
						} else {
							$("#image-additional").find(".carousel-control").hide();
						}
						
						
						
						$('#image-additional .item:first').addClass('active');
						//$('#image-additional').carousel({interval:false})
						
						//images_to_mouseover();
						// if (poip_settings['img_hover']) {
						// 	$("#image-additional").find('a').mouseover(function(){
						// 		poip_image_mouseover(this);
						// 	});
						// }
						
					}
					
					return;
				}
				// >> pav fashion theme compatibility
				
				
				// more compatible
				var shown_img = [];
				$('div.image-additional').find('a').each( function(){
                //$('div.image-additional').children('a').each( function(){
                // Учтем возможность спец символов типа пробела %20
                if (($.inArray( $(this).attr('href'), images) != -1 || $.inArray(decodeURIComponent($(this).attr('href')), images) != -1) && $.inArray( $(this).attr('href'), shown_img) == -1) {
                    $(this).show();
                                shown_img.push($(this).attr('href'));
                } else {
                    $(this).hide();
                }
                });
				
        
      }
      
      //TODO: Review
      function poip_change_images(option) {
		//debugger;	
        var product_option_id = option.name.substr(option_prefix_length+1, option.name.indexOf(']')-(option_prefix_length+1) );
        
		if ($.inArray(product_option_id, poip_product_option_ids)==-1) {
          return;
        }
				
        images_to_show = poip_change_available_images(product_option_id);
		return images_to_show;
        var value = option.value;
		var selected_values = get_selected_values();
				
				
        // сначала по сочетанию опций
        // если используется фильтрация - то берем первое изображение из прошедших фильтр, иначе первое изображение опции
        var main_image_switched = false;
				if (images_to_show && ((poip_options_settings[product_option_id]['img_limit'] && poip_options_settings[product_option_id]['img_use'])
															|| (value && $.inArray(value, selected_values)==-1)) ) { //если отменили выбор опции, то тоже показываем первую из доступных картинок
        //if (images_to_show && poip_options_settings[product_option_id]['img_limit'] && poip_options_settings[product_option_id]['img_use']) {
          for (var i=0;i<poip_images.length;i++) {
            if (images_to_show[0] == poip_images[i]['popup']) {
              var main_image_switched = true;
            }
          }
        }
				
        if (!main_image_switched) {
				
          // потом по выбранной опции
          
          if (value && $.inArray(value, selected_values)!=-1) {
          
            // смена главного изображения
            if (poip_options_settings[product_option_id] && poip_options_settings[product_option_id]['img_change'] ) {
            
              if (poip_images_by_options[value]) {
              
                image = poip_images_by_options[value][0]['image'];
                
                for (var i=0;i<poip_images.length;i++) {
                  if (image == poip_images[i]['image']) {
                    poip_main_image().attr('src', poip_images[i]['main']);
                    poip_main_image().closest('a').attr('href', poip_images[i]['popup']);
				
                    break;
                  }
                }
              }
  
            }
          }
        }
        
        // отображение картинок под опцией
        if (poip_options_settings[product_option_id] && poip_options_settings[product_option_id]['img_option'] ) {
          if (!$('product_option_images'+product_option_id).length) {

            // у чекбоксов может быть много значений
            if ($(option).prop('tagName')=='INPUT' && $(option).prop('type')=='checkbox' ) {
              var values = [];
              $('input[type=checkbox][name^='+option_prefix+'\\['+product_option_id+'\\]]:checked').each( function() {
                values.push($(this).val());
              });
            } else {
              var values = [value];
            }
            
            $('#option-images-'+product_option_id).remove();
            if (!$('#option-images-'+product_option_id).length) {
              $('#option-'+product_option_id).append('<div id="option-images-'+product_option_id+'"></div>');
            }
            
            $('#option-images-'+product_option_id).html('');
            for (var i=0; i<poip_images.length; i++) {
              for (var j=0; j<values.length; j++) {
                if (poip_images[i]['product_option_value_id'] && $.inArray(values[j], poip_images[i]['product_option_value_id']) != -1) {
                  var html_image = '<a href="'+poip_images[i]['popup']+'" class="image-additional" style="margin: 5px;"><img src="'+poip_images[i]['thumb']+'" ></a>';
                  $('#option-images-'+product_option_id).append(html_image);
                }
              }
            }

          }
        }
        
      }

      function poip_option_value_selected(option) {

        var images_to_show = poip_change_images(option);

        var uniqueImages = [];
        $.each(images_to_show, function(i, el){
            if($.inArray(el, uniqueImages) === -1) uniqueImages.push(el);
        });



        // Create the new HTML
        var galleryContent = [];
        for(i=0;i<uniqueImages.length;i++){
         galleryContent = galleryContent + "<li><a href=\""+uniqueImages[i]+"\"  data-image=\" "+uniqueImages[i]+" \" data-standard=\""+uniqueImages[i]+"\"><img src=\""+uniqueImages[i]+"\"></a></li>"
        }
        $('.product-list-thumb').empty();
        $('.product-list-thumb').html("<ul class=\"thumbnails kt-owl-carousel\" data-margin=\"10\" data-nav=\"true\" " + galleryContent + "</ul>");

        // add active style for the first thumbnail
        $('.product-list-thumb ul li:first-child a').addClass('selected');

        //pass the first image to the zoom preview 
        $('.product-image a ').attr("href",images_to_show[0]);
        $('.product-image a img').attr("src",images_to_show[0]);


        function init_carousel(){
          $('.kt-owl-carousel').each(function(){
            var config = $(this).data();
            //config.navText = ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'];
            var animateOut = $(this).data('animateout');
            var animateIn = $(this).data('animatein');
  
            if(typeof animateOut != 'undefined' ){
              config.animateOut = animateOut;
            }
            if(typeof animateIn != 'undefined' ){
              config.animateIn = animateIn;
            }
            var owl = $(this);
            owl.owlCarousel(config);
            $(this).find('.owl-item').removeClass('last-item');
            $(this).find('.owl-item.active').last().addClass('last-item');
  
            var t = $(this);
            owl.on('changed.owl.carousel', function(event) {
              var item      = event.item.index;
              t.find('.owl-item').removeClass('last-item');
              setTimeout(function(){
                  t.find('.owl-item.active').last().addClass('last-item');
              }, 100);
              
            })
          });
      }
      init_carousel();

        // show and hide of gallery arrows
        if(uniqueImages.length < 3){ $('.owl-next ,.owl-prev').hide();}
        else{ 
            $('.owl-next ,.owl-prev').show();
        }

        // Zoom
        if($('.easyzoom').length >0){
            // Instantiate EasyZoom instances
            var $easyzoom = $('.easyzoom').easyZoom();

            // Get an instance API
            var api1 = $easyzoom.filter('.easyzoom--with-thumbnails').data('easyZoom');

            // Setup thumbnails example
            $('.thumbnails').on('click', 'a', function(e) {
                $(this).closest('.product-list-thumb').find('a').each(function(){
                    $(this).removeClass('selected');
                })
                
                $(this).addClass('selected');

                var $this = $(this);
                e.preventDefault();
                // Use EasyZoom's `swap` method
                api1.swap($this.data('standard'), $this.attr('href'));

            });

            // Setup toggles example
            var api2 = $easyzoom.filter('.easyzoom--with-toggle').data('easyZoom');

            $('.toggle').on('click', function() {
                var $this = $(this);
                if ($this.data("active") === true) {
                    $this.text("Switch on").data("active", false);
                    api2.teardown();
                } else {
                    $this.text("Switch off").data("active", true);
                    api2._init();
                }
            });
        }


      }
			
    function poip_main_image() {
        if (!$('#image').length) {
            if ($('#main-image').length) {
                return $('#main-image'); // theme start compatibility
            }
            if ($('div.product-info div.image a img').length) {
                return $('div.product-info div.image a img'); // theme cosyone compatibility
            }
            if ($('div.row-product a img[itemprop="image"]').length) {
                return $('div.row-product a img[itemprop="image"]'); // theme moneymaker compatibility
            }
        }
        return $('#image'); // by standard default
    }



    function poipCheckEventsForSelects(first_time) {

        $("select[name^="+option_prefix+"\\[]").each(function () {
            var select_events = $(this).data('events');
            var found_poip = false;
            
            // if (select_events && select_events.change) {
            // 	for (var i=0; i<select_events.change.length; i++) {
            // 		if ( (''+select_events.change[i].handler).indexOf('poip_option_value_selected') != -1 ) {
            // 			found_poip = true;
            // 			break;
            // 		}
            // 	}
            // }
            
            if (!found_poip) {
                $(this).change( function(){poip_option_value_selected(this);} );
                // надо вызвать событие, возможно значение селекта было сброшено
                if (!first_time) {
                    poip_option_value_selected(this);
                }
            }
        });
        
    }

    poipCheckEventsForSelects(true);
    
    //$('div.options').click(function(){console.debug(99)});
    $('div.options').click(function(){poipCheckEventsForSelects();});
			
			
      
      $("input[type=radio][name^="+option_prefix+"\\[]").each(function (i) {
        $(this).change(function(){
          poip_option_value_selected(this);
          poip_change_images(this);
        })
      })
      
      $("input[type=checkbox][name^="+option_prefix+"\\[]").each(function (i) {
        $(this).change(function(){
          poip_option_value_selected(this);
          poip_change_images(this);
        })
      })
      
      $(document).ready(function(){
        poip_set_visible_images(poip_get_global_visible_images());
        
        });
        // 		$(document).ready(function(){
        // 			poip_set_visible_images(poip_get_global_visible_images());
        // 			//refresh_colorbox();
                
        //   {% if option_images.ov %}
        //   var poip_ov = '{{ option_images.ov }}';
        //   {% else %}
        //   var poip_ov = false;
        //   {% endif %}
                
                
        // 		if (poip_ov) {
        // 			// journal 2 compatibility
        // 			setTimeout(function() {
        // 				set_option_value(poip_ov);

        // 			},1);
        // 		}
                
        // 		});