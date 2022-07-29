;(function($){$.fn.ecCountDown = function( opts ) {
	 	return this.each(function() { 
			new  $.ecCountDown( this, opts ); 
		});
 	 }
	$.ecCountDown = function( obj, opts ) {
		var defaults = {
			target_date : "", /*date format: m/d/Y G:i:s*/
            id_day  : '#cd_day-' + 62,
            id_hour  : '#cd_hour-' + 62,
            id_minute  : '#cd_min-' + 62,
            id_second  : '#cd_sec-' + 62,
            
            label_day : '#cd_day',
            label_hour : '#cd_hour',
            label_minute : '#cd_minute',
            label_second : '#cd_second',
			
			label_day_value : 'Day',
            label_hour_value : 'Hours',
            label_minute_value : 'Mins',
            label_second_value : 'Secs',
            show_empty_day : false,
            count_step : -1,/*count down*/
            ////////callbacks
			callback			: function() {  },	//this callback is invoked when the image on a slide has completely loaded
		};
		var opts = $.extend(defaults, opts || {});
		var element = obj;
		var count_step = Math.ceil(opts.count_step);
		var date_end = new Date(opts.target_date);
		var date_now = new Date();
		if( this.count_step > 0 ) {
			date_diff = new Date(date_now-date_end);
		}
		else {
			date_diff = new Date(date_end-date_now);
		}
		var gseconds = Math.floor(date_diff.valueOf()/1000);

		countDownSecond();

	    function countDownSecond() {
	            
	        if( gseconds > 0){
	            gseconds--;
	            
	            setTimeout(function() {
	                if(opts.callback != ''){
	                	opts.callback.call(this);
	                }else{
	                    showTime();
	                }
	            }, 0);
	            
	            setTimeout(function() {
	                countDownSecond();
	            }, 1000);
	        }
	    }
	    
	    function showTime() {
	    
	        var seconds = Math.floor(gseconds);
	        var minutes = Math.floor(seconds / 60);
	        var hours = Math.floor(minutes / 60);
	        var days = Math.floor(hours / 24);
	        
	        hours %= 24;
	        minutes %= 60;
	        seconds %= 60;
	        
	        var str_days = wrapperTagSpan ( insertOneZero(days) );
	        var str_hours = wrapperTagSpan ( insertOneZero(hours) );
	        var str_minutes = wrapperTagSpan ( insertOneZero(minutes) );
	        var str_seconds = wrapperTagSpan ( insertOneZero(seconds) );
	        
	        if ($(opts.label_day)){
	            if(opts.label_day_value){
	                
	                $(opts.label_day).html(opts.label_day_value);
	            }else{
	                $(opts.label_day).html('Days');
	            }
	        }
	            
	        if ($(opts.label_hour)) {
	            if(opts.label_hour_value){
	                $(opts.label_hour).html(opts.label_hour_value);
	            }else{
	                $(opts.label_hour).html('Hours');
	            }
	        }
	            
	        if ($(opts.label_minute)){
	            if(opts.label_minute_value){
	                $(opts.label_minute).html(opts.label_minute_value);
	            }else{
	                $(opts.label_minute).html('Minutes');
	            }
	        }
	            
	        if ($(opts.label_second)){
	            if(opts.label_second_value){
	                $(opts.label_second).html(opts.label_second_value);
	            }else{
	                $(opts.label_second).html('Seconds');
	            }
	        }
	            
	        if($(opts.id_day)) $(opts.id_day).html(str_days);
	        if($(opts.id_hour)) $(opts.id_hour).html(str_hours);
	        if($(opts.id_minute)) $(opts.id_minute).html(str_minutes);
	        if($(opts.id_second)) $(opts.id_second).html(str_seconds);
	            
	        if(days <= 0 && !opts.show_empty_day){
	            if ($(opts.label_day)) $(opts.label_day).html('');
	            if($(opts.id_day)) $(opts.id_day).html('');
	        }
	    }
	    function insertOneZero(value) {
		        var result = '';
		            
		        if(value < 10){
		            result += '0' + value;
		        }else{
		            result += value;
		        }
		        
		        return result;
		    }
		    
		function wrapperTagSpan(string) {
		        var result = '';
		        
		        string.toString();
		        
		        for(var i=0; i<string.length; i++) {
		            result += "<span>" + string.charAt(i)+"</span>";
		        }

		        return result;
		    }
	    
 	 }
})(jQuery);