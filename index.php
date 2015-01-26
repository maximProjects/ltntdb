<?php
/*
Scripts get data from http://www.plius.lt/imports?domo_xml_import
Export to db delete ol from db and uncoment 119 and 127 lines
*/
function xmlToArray($xml, $root = true) {
	if (!$xml->children()) {
		return (string)$xml;
	}
 
	$array = array();
	foreach ($xml->children() as $element => $node) {
		$totalElement = count($xml->{$element});
 
		if (!isset($array[$element])) {
			$array[$element] = "";
		}
 
		// Has attributes
		if ($attributes = $node->attributes()) {
			$data = array(
				'attributes' => array(),
			);
 			if (!count($node->children())){
				$data['value'] = (string)$node;
			} else {
				$data = array_merge($data, xmlToArray($node, false));
			}
			foreach ($attributes as $attr => $value) {
				$data['attributes'][$attr] = (string)$value;
			}
 
			if ($totalElement > 1) {
				$array[$element][] = $data;
			} else {
				$array[$element] = $data;
			}
		// Just a value
		} else {
			if ($totalElement > 1) {
				$array[$element][] = xmlToArray($node, false);
			} else {
				$array[$element] = xmlToArray($node, false);
			}
		}
	}
 
	if ($root) {
		return array($xml->getName() => $array);
	} else {
		return $array;
	}
}
function itemArr($arr)
{
	foreach($arr as $item)
	{
		if(!$item['item'])
		{
			foreach($item as $item)
			{
				if(!$item['item'])
				{
					foreach($item as $item)
					{
						if($item['item']){
							$arr = $item['item'];
						}
					}	
				}
			}	
		}
	}
	return $arr;
}

function writeGyv($id){
	$source = "http://domo.plius.lt/importhandler?datacollector=1&fk_placereg_adm_units_id=".$id."&get_fk_placereg_settlements_id=1";
	echo "SAV ID = ".$id." source= ".$source."<br>";
	$xmlstr = file_get_contents($source);
	$xmlcont = new SimpleXMLElement($xmlstr);
	$gyv_arr = xmlToArray($xmlcont);
	$gyv_arr = itemArr($gyv_arr);
	if(!$gyv_arr[0]){
		$gyv_arr= array(0=>$gyv_arr);
	}
	/* savyvaldybes ty mysql */
	foreach($gyv_arr as $gyv){
		echo "Saqv_id = ".$id." Gyvenciete: ID = ".$gyv['id']." Name = ".$gyv['title']."<br>";	
		$sql = "INSERT INTO gyv (id, sav_id, name) VALUES ('".$gyv['id']."', '".$id."','".mysql_real_escape_string($gyv['title'])."')";
		mysql_query($sql) or die(mysql_error()); 
		
	}
}
$servername = "localhost";
$username = "root";
$password = "";

$conn = mysql_connect($hostname, $username, $password);

$selected = mysql_select_db("ltntdb",$conn); 

?> 
<html>
	<head>
		<meta charset="UTF-8">
	</head> 
	<body>
		<?php
		$source = "http://domo.plius.lt/importhandler?datacollector=1&get_fk_placereg_adm_units=1";
		$xmlstr = file_get_contents($source);
		$xmlcont = new SimpleXMLElement($xmlstr);
		$sav_arr = xmlToArray($xmlcont);
		$sav_arr = itemArr($sav_arr);
		/* savyvaldibiu import */
		foreach($sav_arr as $sav)
		{
			$sql = "INSERT INTO sav (id, name) VALUES ('".$sav['id']."', '".mysql_real_escape_string($sav['title'])."')";
			//mysql_query($sql) or die(mysql_error()); 
			//echo "Savyvaldybe: ID = ".$sav['id']." Name = ".$sav['title']."<br>";	
			
		}

		/* gyvenvieciu import  */		
		foreach($sav_arr as $sav)
		{
			//writeGyv($sav['id']);
		}



		?>
	</body>
</html>
