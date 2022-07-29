/**
 * Created by ANH To on 9/30/14.
 */

var view_action_on_conditions;
var view_action_on_transactions;
$(document).ready(function(){
    RewardPoints.Views.Transactions = Backbone.View.extend({
        el: $(".transactions"),
        events:{
            "click .update_status"     :   "updateStatus",
            "mouseenter .transaction_status": "enterUpdateStatus",
            "mouseleave .transaction_status": "leaveUpdateStatus"
        },
        leaveUpdateStatus: function(ev){
            var container_status = $(ev.target);
            container_status.find(".text_status").show();
            container_status.find(".selection_status").hide();
            container_status.find(".action_status").hide();
        },
        enterUpdateStatus: function(ev){
            var container_status = $(ev.target);
            container_status.find(".text_status").hide();
            container_status.find(".selection_status").show();
            container_status.find(".action_status").show();
        },
        updateStatus: function(ev){
            var container_status = $(ev.target).closest(".container_status");
            var value = container_status.find(".reward_status option:selected").val();
            var text =  container_status.find(".reward_status option:selected").text();
            var customer_reward_id = $(ev.target).attr('data');

            $.ajax({
                type: "POST",
                url:  $("#url_post_status").val(),
                data: "customer_reward_id="+customer_reward_id+"&status="+value,
                success: function(data){
                    container_status.find(".text_status").show();
                    container_status.find(".selection_status").hide();
                    container_status.find(".action_status").hide();
                    container_status.find(".text_status").text(text);
                }
            });
        }
    });
    RewardPoints.Views.Conditions   =   Backbone.View.extend({
        el: $("#rule_conditions_fieldset"),
        events:{
            "click .rule-param > .label"                :   "clickOnfirstParam",
            "change .rule-param > .element select"      :   "changeOnSelect",
            "click .rule-param-remove"                  :   "clickParamRemove",
            "blur .rule-param > .element input"         :   "blurOninput"
        },
        initialize: function(){
            this.rsCounter();
        },
        rsCounter: function(){
            var element = $('.rule-param-children');
            /** Reset counter */
            var total_condition = element.find("li").length - 1;
            var incr_counter = 1;
            var counter  = parseInt($("#rule_counter").val());
            var sub_counter  = parseInt($("#rule_sub_counter").val());
            element.find("li").each(function(){
                if($(this).find(".rule-param-new-child").length == 0){
                    $(this).find("input:hidden, select").each(function(){
                        var text_counter = counter + "--" + incr_counter;

                        var new_id = $(this).attr('id').replace(/([0-9]+)--([0-9]+)/, text_counter);
                        var new_name = $(this).attr('name').replace(/([0-9]+)--([0-9]+)/, text_counter);
                        $(this).attr('id', new_id);
                        $(this).attr('name', new_name);
                    });

                    incr_counter++;
                }
            });
            $("#rule_sub_counter").val(incr_counter);
        },
        clickOnfirstParam: function(ev){
            var parent = $(ev.target).closest('.rule-param');
            parent.find('.label').hide();
            parent.find('.element').show();
        },
        clickParamRemove: function(ev){
            $(ev.target).closest("li").remove();
            this.rsCounter();
        },
        blurOninput: function(ev){
            var parent = $(ev.target).closest('.rule-param');
            parent.find('.label').text(($(ev.target).val() != "" ? $(ev.target).val() : "...")).show();
        },
        changeOnSelect: function(ev){
            var _self = this;
            var parent = $(ev.target).closest('.rule-param');
            var text = $(ev.target).find("option:selected").text();
            var value = $(ev.target).find("option:selected").val();

            if($(ev.target).attr('id').indexOf('new_child') == -1){
                parent.find('.label').text(text);
                parent.find('.label').show();
                parent.find('.element').hide();
            }else{
                var parent_children = $(ev.target).closest('.rule-param-children');
                parent.find('.label').show();
                parent.find('.element').hide();

                $(ev.target).find("option").removeAttr('selected');

                var template = _.template($("#rule-condition-li-wait").html());
                parent_children.find('.rule-param-new-child').closest('li').before(template());

                var counter  =  parseInt($("#rule_sub_counter").val());

                $.ajax({
                    type:   'POST',
                    url:    $('#rule_url_post').val(),
                    data:   'value='+value,
                    success:    function(data){
                        var data = $.parseJSON(data);

                        var template = _.template($("#rule-condition-operator").html());
                        var html_operator  = template({counter: counter,operator: data.operator});

                        var template = _.template($("#rule-condition-value").html());
                        var html_options_value  = template({counter: counter, type: data.type, values: data.values});

                        var template = _.template($("#rule-condition-li").html());

                        var html = template({
                            counter: counter,
                            condition_model: data.model,
                            condition_label: data.label,
                            condition_operator: html_operator,
                            condition_value_selected: '...',
                            condition_options_value: html_options_value
                        });

                        _self.rsCounter();

                        parent_children.find(".rule-param-wait").remove();
                        parent_children.find('.rule-param-new-child').closest('li').before(html);
                    }
                });
            }
        }
    });
    RewardPoints.Views.Window   =   Backbone.View.extend({
        el: $(window),
        events:{
            "click body"     :   "clickOnbody"
        },
        initialize: function(){
            $("#apply_rule").val('');
        },
        clickOnbody: function(ev){
            if($(ev.target).closest(".rule-param").length != 1){
                $(".rule-param .element").each(function(){
                    if($(this).css('display') != 'none'){
                        var parent = $(this).closest('.rule-param');
                        parent.find('.label').show();
                        parent.find('.element').hide();
                    }
                });
            }
        }
    });

	RewardPoints.Views.Behavior     =   Backbone.View.extend({
		el: $(".behavior-customer-rule"),
		events: {
			"change  #actions"  :   "onChangeBehaviorType"
		},
		onChangeBehaviorType: function(ev){
			var parent = $(ev.target).closest('td');
			if($(ev.target).val() == '2'){
				parent.find(".note").find("#post_review").show();
			}else{
				parent.find(".note").find("span").hide();
			}
		}
	});
    new RewardPoints.Views.Window();
    new RewardPoints.Views.Behavior();
    view_action_on_conditions = new RewardPoints.Views.Conditions();
    view_action_on_transactions = new RewardPoints.Views.Transactions();
});
