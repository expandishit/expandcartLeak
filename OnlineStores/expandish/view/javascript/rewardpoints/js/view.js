/**
 * Created by ANH To on 9/30/14.
 */
var sider_range,
    view_on_checkout,
    view_on_payment_methods,
    view_on_shipping_methods;


$(document).ready(function(){
    /** Work with default checkout order */
    RewardPoints.Views.onPaymentMethods =   Backbone.View.extend({
        el: $("#payment-method"),
        events: {
            "click input[name=payment_method]"    :   "onClickPaymentMethod"
        },
        initialize: function(){
            var self  = this;
            if($("#payment-method").length > 0){
                $("#payment-method").bind("DOMSubtreeModified", function(){
                    if($("#loaded_block_rewardpoints_discout").length == 0){
                        $("body").prepend("<input type='hidden' id='loaded_block_rewardpoints_discout'>");
                        self.loadBlockRewardPoints();
                    }
                });
            }
        },
        onClickPaymentMethod: function(ev){
            var self = this;
            $.ajax({
                type: 'POST',
                url : 'index.php?route=rewardpoints/index/quickUpdatePaymentMethod',
                data: $('#payment-method input[type=\'radio\']:checked'),
                dataType: 'json',
                success: function(data){
                    self.$el.find(".buttons").show();
                    self.loadBlockRewardPoints({keep_valued: false});
                }
            });
            return true;
        },
        loadBlockRewardPoints: function(params){
	        view_on_checkout.setElement($("#payment-method"));
            RewardPoints.on("event:after_load_block_rewardpoint", view_on_checkout.buidSlider);

            var self = this;
            $("#payment-method").find(".box_rewardpoints").remove();
            $.ajax({
                type: 'GET',
                url : 'index.php?route=rewardpoints/index/getBlockRewardPointsDiscount',
                dataType: 'json',
                success: function(data){
                    $("#payment-method").find(".checkout-content").prepend(data.html);
                    if(typeof params == 'undefined' || params.keep_valued == true){
                        /** Remove checked on radio to trigger event */
                        self.$el.find("input[type=radio]").removeAttr('checked');
                        self.$el.find(".buttons").hide();
                    }
                    RewardPoints.trigger("event:after_load_block_rewardpoint", data);
                }
            });
        }
    });

    RewardPoints.Views.onShippingMethods =   Backbone.View.extend({
        el: $("#shipping-method"),
        events: {
            "click  #shipping-method .checkout-heading a"   :   "onClickModify"
        },
        initialize: function(){
        },
        onClickModify: function(ev){
            $("body").find("#loaded_block_rewardpoints_discout").remove();
        },
    });

    RewardPoints.Views.onCheckout   =   Backbone.View.extend({
        events:{
            "click .use_maxium"                 :   "clickUseMaxiumPoint",
            "click .box_check_use_maxium span"  :   "clickOnTextmaxium",
            "keypress #input-with-keypress"     :   "onKeypressValue",
            "blur #input-with-keypress"         :   "onBlurValue",
            "focus #input-with-keypress"        :   "onFocusValue"
        },
        initialize: function(){
            this.loadBlockRewardPoints();
            RewardPoints.on("event:after_load_block_rewardpoint", this.buidSlider.bind(this));
        },
        clickOnTextmaxium: function(ev){
            var parent = $(ev.target).closest('div');
            if(parent.find('input').is(':checked')){
                parent.find('input').removeAttr('checked');
                sider_range.val(0);
            }else{
                parent.find('input').attr('checked', true);
                sider_range.val(parent.find('input').val());
            }
            RewardPoints.trigger("event:after_change_on_slider", {});
        },
        clickUseMaxiumPoint: function(ev){
            var self = this;
            if($(ev.target).is(':checked')){
                sider_range.val($(ev.target).val());
            }else{
                sider_range.val(0);
            }
            RewardPoints.trigger("event:after_change_on_slider", {});
        },
        onChangeSlider: function(){
	        if(sider_range == undefined) return false;
            sider_range.attr('disabled', true);
            if(parseInt(sider_range.val()) != parseInt($(".use_maxium").val())){
                $(".use_maxium").removeAttr('checked');
            }else{
                $(".use_maxium").attr('checked', true);
            }

			if($("#payment-method").length == 0){
				var loading = '<div style="text-align: center; min-height: 26px;"><img src="expandish/view/javascript/rewardpoints/image/loading.gif"/> Updating cart...</div>';

				if($(".cart-total").length > 0){
					$(".cart-total").find("*").hide();
					$(".cart-total").prepend(loading);
				}
			}else if($("#payment-method").length > 0){
				var loading = '<a><img src="expandish/view/javascript/rewardpoints/image/loading.gif"> Updating points...</a>';

				if($("#payment-method").find(".checkout-heading").length > 0){
					$("#payment-method").find(".checkout-heading").append(loading);
				}
			}

	        if($(".cart-total").length > 0){
		        $(".cart-total").find("*").hide();
		        $(".cart-total").prepend(loading);
	        }
            $.ajax({
                type:   'POST',
                url:    'index.php?route=rewardpoints/index/updateRedeemPoint',
                data:   'reward_point='+sider_range.val(),
                dataType: 'json',
                success: function(data){
                    sider_range.removeAttr('disabled');
                    if($(".cart-total").length > 0){
                        $(".cart-total").html(data.html);
                    }else{
	                    /** Only reload if current page is checkout/cart */
	                    if($("#payment-method").length == 0){
		                    location.reload();
		                    return false;
	                    }
                    }

	                if($("#payment-method").length > 0){
		                if($("#payment-method").find(".checkout-heading").length > 0){
			                $("#payment-method").find(".checkout-heading").find("a").remove();
		                }
	                }
                },
                complete: function(){
                    sider_range.removeAttr('disabled');
                },
                error: function(){
                    sider_range.removeAttr('disabled');
                }
            });
        },
        onKeypressValue: function(ev){
            if(ev.keyCode == 13){
                sider_range.val($(ev.target).val());
                RewardPoints.trigger("event:after_change_on_slider", {});
            } //Enter
        },
        onBlurValue: function(ev){
	        if(sider_range != undefined)
                sider_range.val($(ev.target).val());
            RewardPoints.trigger("event:after_change_on_slider", {});
        },
        buidSlider: function(data){
            var self    =   this;
            if(data == null) return;
            var data_slider = data.data_slider;
            RewardPoints.on("event:after_change_on_slider", this.onChangeSlider);
            RewardPoints.off("event:after_load_block_rewardpoint");
            if($("#range").length > 0){
                sider_range = $('#range');
                var input = $('#input-with-keypress');
                if(data_slider.max == 0){
                    sider_range.attr('disabled', true);
                }
	            sider_range.noUiSlider({
		            start: data_slider.start,
		            step: data_slider.step,
		            margin: 10,
		            direction: 'ltr',
		            connect: "lower",
		            // Configure tapping, or make the selected range dragable.
		            behaviour: 'tap-drag',

		            // Full number format support.
		            format: wNumb({
			            mark: '.',
			            decimals: 0
		            }),

		            // Support for non-linear ranges by adding intervals.
		            range: {
			            'min': data_slider.min,
			            'max': data_slider.max
		            }
	            });

	            sider_range.noUiSlider_pips({
		            mode: 'values',
		            values: data_slider.rule_slider,
		            density: 4
	            });

	            sider_range.Link('lower').to(input);

	            sider_range.on({
		            change: function(){
			            RewardPoints.trigger("event:after_change_on_slider", {});
		            }
	            });

	            RewardPoints.trigger("event:after_build_slider", {});
            }
        },
        loadBlockRewardPoints: function(){
            var self = this;
            $.ajax({
                type: 'GET',
                url : 'index.php?route=rewardpoints/index/loadBlockRewardPoints',
                dataType: 'json',
                success: function(data){
	                if($(".cart-total").length > 0){
		                /** Default theme or custom still keep this class */
		                $(".cart-total").before(data.html_block);
		                $(".cart-total").html(data.html_cart_total);
	                }

                    self.setElement($(".box_rewardpoints"));
                    RewardPoints.trigger("event:after_load_block_rewardpoint", data);
                }
            });
        }
    });

    view_on_checkout = new RewardPoints.Views.onCheckout();
    view_on_payment_methods = new RewardPoints.Views.onPaymentMethods();
    view_on_shipping_methods = new RewardPoints.Views.onShippingMethods;
});
