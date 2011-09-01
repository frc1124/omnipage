//Non administrative calendar javascript

	$(document).ready(function(){
		$(".eventTip").hide(); //hide event tip
		$("#exportRange").hide();
		$(".calHour").val("HH").focus(function(){
			if($(this).val() == "HH"){
				$(this).val("");
				}
			});
		$(".calMin").val("MM").focus(function(){
			if($(this).val() == "MM"){
				$(this).val("");
				}
			});
		$("#exportSingle").hide();
		$("#eventSelect").hide();
		$(".eventLink").parent().hover( //show event tip popups
			function(){
				$(this).children('div').show();
			},
			function(){
				$(this).children('div').hide();
			});
	});
