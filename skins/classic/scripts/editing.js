//creates editing capabilities like the AJAX CMS
function toggleEditBox(){
	$("#editBox").toggle();
	
	}
function addModule(modId,secondary){
	secondary = secondary==undefined?false:secondary;
	$.get("/omni/ajax/addMod.php",
			{modId:modId,pageId:(secondary?"0":pageId),mode:"add"},
			function(){
			location.reload();
			});
	}
function listModules(){
	$("#listModulesButton").hide();
	$.get("/omni/ajax/addMod.php",
			{mode:"list",pageId:pageId},
			function(data){
			$("#modList").hide().html(data).slideDown();
			$("#modList A").draggable({revert:true});
			});
	}
	
function showMod(page,inst){
	$.get("/omni/ajax/modEdit.php",{mode:"showMod",pageId:page,instanceId:inst},function(data){
		$("#mod_"+page+"_"+inst).html(data);
	});
}
function selectEdit(){
	$("div").each(function(){
		if($(this).attr("id").substr(0,4)=="mod_"){
			$(this).hover(function(){$(this).css("backgroundColor","#ddddff")},function(){$(this).css("backgroundColor","")});
			$(this).click(function(){
				editObj = this;
				$(editObj).html("Loading...");
				var data = $(this).attr("id").split("_");
				var page = data[1];
				var inst = data[2];
				$.get("/omni/ajax/modEdit.php",
				{pageId:data[1],instanceId:data[2],mode:"renderEdit"},
				function(data){
					$(editObj).html(data+"<br/><p><button id='returnToMod' onclick='showMod("+page+","+inst+")'>Cancel</button>");
					$("DIV").each(function(){
						if($(this).attr("id").substr(0,4)=="mod_"){
							$(this).unbind("click mouseenter mouseleave");
							$(this).css("backgroundColor","");
					}});
				});
			});
		}});
	}
function saveMod(pageId,instanceId,properties){
	
	window.pageIdd = pageId;
	window.instanceId = instanceId;
	properties.pageId = pageId;
	properties.instanceId = instanceId;
	properties.mode = "saveMod";
	var jqxhr = $.get("/omni/ajax/modEdit.php",properties,
	function(data){
		$("#mod_"+window.pageIdd+"_"+window.instanceId).html(data);
	});

}
	
function selectDel(){
	$("div").each(function(){
		if($(this).attr("id").substr(0,4)=="mod_"){
			$(this).hover(function(){$(this).css("backgroundColor","#ffdddd")},function(){$(this).css("backgroundColor","")});
			$(this).click(function(){
				confi = confirm("Are you sure you want to delete this module?");
				if(confi){
					delObj = this;
					$(delObj).html("Deleting...");
					var data = $(this).attr("id").split("_");
					$.get("/omni/ajax/modEdit.php",
					{pageId:data[1],instanceId:data[2],mode:"delete"},
					function(data){
						$(delObj).hide();
						$("DIV").each(function(){
							if($(this).attr("id").substr(0,4)=="mod_"){
								$(this).unbind("click mouseenter mouseleave");
								$(this).css("backgroundColor","");
						}});
					});
				}
			});
		}});
	}
	
function selectHistory(){
	$("div").each(function(){
		if($(this).attr("id").substr(0,4)=="mod_"){
			$(this).hover(function(){$(this).css("backgroundColor","#89FF65")},function(){$(this).css("backgroundColor","")});
			$(this).click(function(){
				selObj = this;
				$(selObj).html("Fetching Edits...");
				var data = $(this).attr("id").split("_");
				$.get("/omni/ajax/modHistory.php",
				{pageId:data[1],instanceId:data[2],mode:"getEdits"},
				function(data){
					$(selObj).html(data);
					$("DIV").each(function(){
					$(this).unbind("click mouseenter mouseleave");
					$(this).css("backgroundColor","");
					});
				});
			});
		}});
	}
	
function getEditData(page,inst,id){
	$('#editData').html('Fetching Edit Data...');
	$('#editData').toggle();
	$.get("/omni/ajax/modHistory.php",{pageId:page,instanceId:inst,id:id,mode:"getEditData"},
	function(data){
		$('#editData').html(data);
	
	});
	
}

function revertEdit(pageId, instanceId, editId){
	$.get("/omni/ajax/modHistory.php",{pageId:pageId,instanceId:instanceId,id:editId,mode:"restoreEdit"},
	function(data){
		alert(data);
		showMod(pageId,instanceId);
	});
}

function pageHistory(){
	//$.get("/omni/ajax/modEdit");
}

$(document).ready(function(){
				$("#topMenuContainer").mouseleave(function(){
				$("#secondMenuDiv").slideUp(200);
			});
			
			$("#rearrangeBox").change(function(){
				if($("#rearrangeBox:checked").val()!=null){
					//enable drag & drop
					$(".module").css("border","dashed #ccc 2px").css("padding","0px");
					$(".modSort").sortable({disabled:false});
					$(".modSort").disableSelection();
					}
				else{
					//disable drag & drop
					$(".module").css("border","").css("padding","2px");
					$(".modSort").sortable({disabled:true});
					$(".modSort").enableSelection();
					
					//update on server
					newOrderMain = new Array();
					newOrderSecond = new Array();
					$("DIV").each(function(){
						if($(this).attr("id").substr(0,4)=="mod_"){
							pageIdd = $(this).attr("id").split("_")[1];
							var instanceId = $(this).attr("id").split("_")[2];
							//add to main order array
							if(pageIdd!=0)
							newOrderMain[newOrderMain.length]=instanceId;
							//add to second order array (right column)
							else
							newOrderSecond[newOrderSecond.length]=instanceId;
						}});
					alert(newOrderMain);
					//update main order
					$.get("/omni/ajax/resort.php",
					{order:newOrderMain.join(","),pageId:pageId});
					//update second order
					$.get("/omni/ajax/resort.php",
					{order:newOrderSecond.join(","),pageId:0});
					}
			});
	})