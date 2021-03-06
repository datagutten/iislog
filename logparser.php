<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>IIS Log parser</title>
</head>
<body>
<?php
$output='html';
require 'config.php';

function filterlink($key,$value,$symbol=' *',$urldecode=false)
{
	$get=$_GET; //Make a temporary copy of $_GET
	$get['filter'][$key]=$value;
	$filterstring=http_build_query($get,'','&amp');
	if($urldecode)
		$filterstring=urldecode($filterstring);
	$filterlink="<a href=\"?$filterstring\">$symbol</a>";

	return $filterlink;
}
if(!isset($_GET['server'])) //No server specified, show server selection
{
	echo "<p>Select server:</p>\n";
	foreach(array_keys($logpath) as $server)
	{
		echo "<a href=\"?server=$server\">$server</a><br />\n";
	}
}
elseif(!isset($logpath[$_GET['server']])) //Server not found
{
		echo "Invalid server: {$_GET['server']}\n";
}
else
{
	if(!isset($_GET['date']))
		$date=date('ymd');
	else
		$date=date('ymd',strtotime($_GET['date']));
	
	$logfile=$logpath[$_GET['server']]."/u_ex$date.log";
		if(file_exists($logfile))
			$data=file_get_contents($logfile);
		else
			echo "Log file not found: $logfile";
}

if(isset($data)) //If data is loaded, show data
{
	echo "<table border=\"1\">\n";
	
	foreach (explode("\r\n",trim($data)) as $line=>$request)
	{
	
		if(substr($request,0,1)!='#') //Do not show comment lines
		{
			foreach (explode(" ",$request) as $fieldkey=>$value) //Get the fields
			{
				$fields[$key=$fieldnames[$fieldkey]]=$value; //Name the field
				if(isset($_GET['filter'][$key]) && $_GET['filter'][$key]!=$value)
					continue 2;

			}
			
			if(isset($header)) //Check if there is a new header
			{	
				echo $header;
				unset($header);
			}
			if($output=='html')
			{
				echo "<tr>\n";
				//unset($fields['date']);
				foreach ($fields as $fieldkey=>$field)
				{
					$filterlink=filterlink($fieldkey,$field);
					if($field=='-')
						$filterlink='';

					echo "\t<td>".urldecode($field)." $filterlink</td>\n";
				}
				echo "</tr>\n";
			}
			else
				print_r($fields);
			
			//if($fields[$key]['sc-status']==404)
	
		}
		elseif(substr($request,0,7)=='#Fields') //Update the field definiton
		{
			$fieldnames=explode(" ",substr($request,9));
			$cols=count($fieldnames); //Get the field count
			if($output=='html')
			{
	
				$header="<tr>\n";
				foreach ($fieldnames as $fieldname)
					$header.="\t<td>".htmlentities(urldecode($fieldname))."</td>\n";
				$header.="</tr>\n";
			}
		}
	
	}
}
echo "</table>\n";
if(isset($_GET['filter']))
{
	unset($_GET['filter']);
	echo "<a href=\"?".http_build_query($_GET)."\">Clear filters</a>\n";
}
?>
</body>
</html>