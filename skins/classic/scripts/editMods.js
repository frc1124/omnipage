$(document).ready(function(){
		//set "edit" click event
		$("#startEdit").click(function() {
			//destroy "edit" click event
			$("#startEdit").unbind('click');
			//make "edit" box big
			$("#startEdit").animate({width:'200px',height:'150px'});
			$("#startEdit").html("<label>Rearrange</label> <input type=\"checkbox\" id=\"sortToggle\">"+
			"<button id=\"updateSortButton\" onclick=\"updateSort()\">Save</button>\n<br>"+
			"<button onclick=\"renderEdit();\">Edit Content</button><br>"+
			"<button id=\"addModuleToggle\">+ Add Module</button><br>"+
			"<div id=\"moduleBox\" style=\"position:relative;\">Loading...</div>");
			$("#addModuleToggle").click(modBoxToggle);
			$("#moduleBox").hide();
			$("#sortToggle").change(function(){
				if($("#sortToggle:checked").val()!=null){
					$(".module").css("border","dashed #ccc 2px");
					$(".modSort").sortable({disabled:false});
					$(".modSort").disableSelection();
				}
				else{
					$(".module").css("border","");
					$(".modSort").sortable({disabled:true});
					$(".modSort").enableSelection();
				}
				return true;
				});
		});
		});
		//use AJAX to get box's edits
		
function renderEdit(){
	$("DIV").each(function(){
		if($(this).attr("id").substr(0,4)=="mod_"){
		$(this).hover(function(){$(this).css("backgroundColor","#ddddff")},function(){$(this).css("backgroundColor","")});
		$(this).click(function(){
			editObj=this;
			$(editObj).html("Loading...");
			var data = $(this).attr("id").split("_");
			$.get("/omni/ajax/modEdit.php",
			{pageId:data[1],instanceId:data[2]},
			function(data){
				$(editObj).html(data);
				$("DIV").each(function(){
					if($(this).attr("id").substr(0,4)=="mod_")
						$(this).unbind("click").unbind("mouseover").unbind("mouseout");
						$(this).css("backgroundColor","");
				});
			})
		});
					
			}
		});
}
		//update reordered content
function updateSort(){
	newOrder = new Array();
	$("DIV").each(function(){
		if($(this).attr("id").substr(0,4)=="mod_"){
		pageIdd = $(this).attr("id").split("_")[1];
		var instanceId = $(this).attr("id").split("_")[2];
		newOrder[newOrder.length]=instanceId;
		}
	});
	$("#updateSortButton").html("Loading...");
	$.get("/omni/ajax/resort.php",
	{order:newOrder.join(","),pageId:pageIdd},
	function(){
		$("#updateSortButton").html("Done");
		});
	}
	
function modBoxToggle(){
	$("#moduleBox").toggle();
	if($("#moduleBox").html()=="Loading...")
	$().get("/omni/ajax/addMod.php",
	{mode:"list"},
	function(data){
		$("#moduleBox").html(data);
		})
	}