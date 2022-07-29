        $(document).ready(function(){
           
            // console.log(db_values.length)
            // console.log(type.length)
            let i = 1;  

            check_type(type);            

            $('div#attribute-values').on('click', 'button#add',function(e){
               e.preventDefault();

               //Validate existing rows..
               if(!validate()) return;

               //Adding new row..
               i++;             
               var templateScript = Handlebars.compile($('#value-row-template').html());
               var context        = {"index" : i};
               var html           = templateScript(context);
               $('#dynamic_field tbody').append(html);
            });  


            $(document).on('click', '.btn_remove', function(){  
               var button_id = $(this).attr("id");   
               $('#row'+button_id+'').remove();  
            }); 


            $('select#type').bind('change', function() {
                check_type($('option:selected', this).attr('data-value'));
            });


            function add_db_values(){
                db_values.forEach((item, index)=>{
                   val=keys_array[index];

                    if($('#dynamic_field tbody tr').length <= 0){

                       var html = Handlebars.compile($('#first-row-template').html());
                       $('#dynamic_field tbody').append(html);

                       item.forEach((item_name, index_name)=>{
                            $('#dynamic_field tbody tr#row'+i).find('#attr-name'+item_name['language_id']).val(item_name['name'])
                       });

                    }
                    else{
                       i++;

                       var templateScript = Handlebars.compile($('#value-row-template').html());
                       var context        = {"index" : val};
                       var html           = templateScript(context);
                       $('#dynamic_field tbody').append(html);

                       item.forEach((item_name, index_name)=>{
                            $('#dynamic_field tbody tr#row'+val).find('#attr-name'+item_name['language_id']).val(item_name['name'])
                       });
                    }

                });
            };


            function check_type(type='text'){
                if(type == 'text' || type == ''){
                    $('#attribute-values').hide();
                    $('#dynamic_field tbody tr').remove();
                    i = 1;
                }
                else{
                    $('#attribute-values').show();
                    //if table empty
                    if($('#dynamic_field tbody tr').length <= 0){
                        if(db_values != null && db_values.length > 0){
                            add_db_values();
                            return;                          
                        }
                        $('#dynamic_field tbody').append( Handlebars.compile($('#first-row-template').html()) );
                    }
                }
            };

            function validate(){
                //reset 
                $('input.attr-value').css('border-color','#ddd');

                var c = 0;

                $('input.attr-value').each(function() { 

                    if($(this).val().length < 4){
                        c++;
                        $(this).css('border-color','red');

                        //switch tabs
                        var tabid = $(this).closest('div.tab-pane').attr('id');
                        tabid2 = tabid.substring(0, tabid.length - 1);

                        $('div.tab-pane[id^="'+ tabid2 +'"]').removeClass('active');
                        $('div#'+tabid).addClass('active');

                        $('a[href^="#'+ tabid2 +'"]').closest('li').removeClass('active');
                        $('a[href="#'+ tabid +'"]').closest('li').addClass('active');
                    }


                });

                if(c > 0) return false;
                else return true;
            };



            // Glyph script
            $("#glyphiconModal div.search-div #myInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#icons .fa-hover").filter(function() {
                  $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });


            $('#icons .fa-hover a').click(function(){
              $('#icons .fa-hover a').removeClass('clicked');
              $(this).toggleClass('clicked');
              return false; //to stop propagation
            });


            $('#glyphiconModalSaveButton').click(function(){
                changeGlyphicon();
            });

            $('#icons .fa-hover').dblclick(function(){
                changeGlyphicon();
            });

            const changeGlyphicon = ()=>{
                var class_name = $('#icons .fa-hover a.clicked i').attr('class');
                $('#glyphicon-span').val(class_name);
                $('#glyphicon-i').removeClass();
                $('#glyphicon-i').addClass(class_name);
                $('#glyphiconModal').modal('toggle');
            }

        });
