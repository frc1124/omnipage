<?PHP
/* *********************************************************************************************
   * This code is licensed under the MIT License                                               *
   * Please see the license.txt file in the /omni directory for the full text                  *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php     *
   * Copyright (c) 2011 Avon Robotics                                                          *
   *********************************************************************************************
   
   **********************************************************************************************
   *  Skin parser                                                                                *
   *  Version 0.1                                                                               *
   *  Developed by Matt Howard and Phil Lopreiato                                               *
   *  Receives fields to replace as indexed array and name of template, returns parsed template *
   **********************************************************************************************/

function parseSkin($fields,$loc,$cases = array()){
	global $currentSkin,$editable,$user,$root_path;
	$skinPath = mysql_fetch_array(mysql_query("SELECT `path`,`id` FROM `skins` WHERE `name` = '".mysql_real_escape_string($currentSkin)."'"));
	if(file_exists($root_path."/skins/".$currentSkin."/".$loc.".html")){
		$source = file_get_contents($root_path."/skins/".$skinPath['path']."/".$loc.".html");
	}else{
		$source = getGhost($skinPath['id'],$loc);
	}
	//parse IFs
	$cases["EDITABLE"]=$editable;
	$cases["LOGGEDIN"]=($user->data["user_id"]!=ANONYMOUS);
	
	foreach($cases as $case=>$bool){
		if($bool==true&&$bool!="0"){
			$source = preg_replace("%\[\[IF $case\]\]"."([^\[\[]+)"."\[\[END IF\]\]%s","$1",$source);
			$source = preg_replace("%\[\[IF NOT $case\]\]"."([^\[\[]+)"."\[\[END IF\]\]%s","",$source);
			}
		else{
			$source = preg_replace("%\[\[IF $case\]\]"."([^\[\[]+)"."\[\[END IF\]\]%s","",$source);
			$source = preg_replace("%\[\[IF NOT $case\]\]"."([^\[\[]+)"."\[\[END IF\]\]%s","$1",$source);
			}
		}
	//replace fields
	$search = array("{{skinRoot}}","{{currentSkin}}");
	$replace = array("/omni/skins/".$currentSkin,$currentSkin);
	foreach($fields as $key => $value){
		$search[] = "{{".$key."}}";
		$replace[] = $value;
		}
	return str_replace($search, $replace, $source);
}

function getGhost($skin,$file){
	global $root_path;
	$skinData = mysql_fetch_array(mysql_query("SELECT `path`,`parent` FROM `skins` WHERE `id` = '".mysql_real_escape_string($skin)."'"));
	if(file_exists($root_path."/skins/".$skinData['path']."/".$file.".html")){
		return file_get_contents($root_path."/skins/".$skinData['path']."/".$file.".html");
	}else if($skinData['parent'] != -1){
		return getGhost($skinData['parent'],$file);
	}else{
		return file_get_contents($root_path."/error.php?errorCode=404");
	}
}

?>