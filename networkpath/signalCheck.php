<?php
define('DEVICES', serialize(array('A','B','C','D','E','F')));
$filePath=isset($_SERVER['argv'])?$_SERVER['argv']:'';
$csvFile=isset($filePath[1])?$filePath[1]:'';
if($filePath){
if(file_exists($csvFile)) {
echo "Enter devices with Signal:".PHP_EOL;
// function to read from the command line
function readDeviceSignal()
{
        $fr=fopen("php://stdin","r");   
        $input = trim(fgets($fr));        
        fclose ($fr);                   
        return $input;                  
}
function createSignalSplit($signalSplit,$deviceOne,$deviceTwo,$nextCheck,$newArr,$firstSpottedPos,$firstCheck,$firstExtraArr,$signalReversed){
		if($signalReversed==false){
			foreach ($signalSplit as $key => $val){
					
					if(in_array($deviceOne,$val) || $nextCheck==$val[0]){
						if($firstCheck==false || $nextCheck==$val[0]) {
						array_push($newArr,$val);
						if(in_array($deviceOne,$val))
							$firstSpottedPos=$key;
						$nextCheck=$val[1];
						$firstCheck=true;
						
						if($nextCheck==$deviceTwo)
							break;
						}else{
						array_push($firstExtraArr,$val);
						}
					}
				}
		}else{
			$againCheck='';
			foreach ($signalSplit as $key => $val){
					
					if((in_array($deviceOne,$val) && ($deviceOne==$val[1])) || $nextCheck==$val[1] || $againCheck==$val[1]){
						if($firstCheck==false || $nextCheck==$val[1]) {
						array_push($newArr,$val);
						if(in_array($deviceOne,$val) && ($deviceOne==$val[1]))
							$firstSpottedPos=$key;
						$nextCheck=$val[0];
						$againCheck=$val[1];
						$firstCheck=true;
						
						if($nextCheck==$deviceTwo)
							break;
						}else{
						array_push($firstExtraArr,$val);
						}
					}
				}
			 }
			return array('newArr'=>$newArr,'firstExtraArr'=>$firstExtraArr,'firstSpottedPos'=>$firstSpottedPos,'firstCheck'=>$firstCheck,'deviceOne'=>$deviceOne,'deviceTwo'=>$deviceTwo,'nextCheck'=>$nextCheck,'signalReversed'=>$signalReversed);
}

function refineSignalCheck($signalVal,$SignalStrength,$signal,$OutputStr,$signalSplit,$deviceOne,$deviceTwo,$nextCheck,$newArr,$firstSpottedPos,$firstCheck,$firstExtraArr,$signalReversed){
			$newArrCount = count($signalVal['newArr']);
			foreach($signalVal['newArr'] as $key => $val){
				if($SignalStrength<$signal){
					if($key==$newArrCount - 1){
						$OutputStr[]=$signalVal['newArr'][$key][0];
						$OutputStr[]=$signalVal['newArr'][$key][1];
						$SignalStrength+=$signalVal['newArr'][$key][2];
						if($SignalStrength<=$signal){
						echo implode(" => ",$OutputStr)." => ".$SignalStrength;
						}
					}
					else{
						$OutputStr[]=$signalVal['newArr'][$key][0];
						$SignalStrength+=$signalVal['newArr'][$key][2];
					}
					if($SignalStrength>$signal){
						if(count($signalVal['firstExtraArr'])>=1){
							foreach($signalVal['firstExtraArr'] as $key=>$val){
								unset($signalSplit[$signalVal['firstSpottedPos']]);
								$signalVal = createSignalSplit($signalSplit,$deviceOne,$deviceTwo,$nextCheck,$newArr,$firstSpottedPos,$firstCheck,$firstExtraArr,$signalReversed);
								/*print_r($signalVal);die;
							print_r($signalSplit);echo '\n';
							print_r($signalVal['newArr']);echo '\n';//die;
							print_r($signalVal['firstExtraArr']);echo '\n';
							print_r($signalVal['firstSpottedPos']);echo '\n';
							die;*/
							}
						}else{
						echo "Path not found.";
						exit;
						}
					}
				}else{
					print_r($signalVal['firstExtraArr']);echo '\n';
					echo "Path not founds.";
					exit;
				}
			}
}
$deviceSignal = readDeviceSignal();
if ($deviceSignal == 'QUIT') {
   exit;
}
else{
   
   $inputParams= explode(' ',$deviceSignal);
    if(count($inputParams)==3)
	{
	   $retvalOne=false;
	   $retvalTwo=false;
	   $retvalThree=false;
	   $deviceOne=$inputParams[0];
	   $deviceTwo=$inputParams[1];
	   $signal=$inputParams[2];
	   $devices=unserialize(DEVICES);
	   if(ctype_alpha($deviceOne) && (strlen($deviceOne)==1) && ctype_upper($deviceOne) && in_array($deviceOne,$devices)){
		   $retvalOne=true;
	   }
	    if(ctype_alpha($deviceTwo) && (strlen($deviceTwo)==1) && ctype_upper($deviceTwo) && in_array($deviceTwo,$devices)){
			$retvalTwo=true;
	   }
	    if(is_numeric($signal)){
			$retvalThree=true;
	   }
	   if($retvalOne && $retvalTwo &&  $retvalThree)
	   {
			$fopen = fopen($csvFile, "r") or die("Unable to open file!");
			$fread = fread($fopen,filesize("$csvFile"));
			fclose($fopen);
			$remove = "\n";
			$signalSplit = explode($remove, $fread);
			$signalReversed=false;
			if(strcmp($deviceOne,$deviceTwo)>0){
			$reversed_signals=array_reverse($signalSplit);
			$signalSplit=$reversed_signals;
			$signalReversed=true;
			}
			foreach ($signalSplit as $key => $val)
			{
				$split= explode(",",$val); 
				$signalSplit[$key]= $split;
			}
			
			$newArr=array();
			$firstExtraArr=array();
			$firstSpottedPos=0;
			$nextCheck='';
			$firstCheck=false;
			$lastCheck=false;
			$postfirstCheckArr=array();
			$signalVal=array();
			if($signalReversed){
				$a=1;
				$b=0;
			}else{
				$a=0;
				$b=1;
			}

			$signalVal = createSignalSplit($signalSplit,$deviceOne,$deviceTwo,$nextCheck,$newArr,$firstSpottedPos,$firstCheck,$firstExtraArr,$signalReversed);
			$newArrCount = count($signalVal['newArr']);
			$OutputStr=array();
			$SignalStrength=0;
			foreach($signalVal['newArr'] as $key => $val){
				if($SignalStrength<$signal){
						if($key==$newArrCount - 1){
						$OutputStr[]=$signalVal['newArr'][$key][$a];
						$OutputStr[]=$signalVal['newArr'][$key][$b];
						$SignalStrength+=$signalVal['newArr'][$key][2];
						if($SignalStrength<=$signal){
						echo implode(" => ",$OutputStr)." => ".$SignalStrength;
						}
					}
					else{
						$OutputStr[]=$signalVal['newArr'][$key][$a];
						$SignalStrength+=$signalVal['newArr'][$key][2];
					}
					if($SignalStrength>$signal){
						if(count($signalVal['firstExtraArr'])>=1){
							foreach($signalVal['firstExtraArr'] as $key=>$val){
								unset($signalSplit[$signalVal['firstSpottedPos']]);
								$signalVal = createSignalSplit($signalSplit,$deviceOne,$deviceTwo,$nextCheck,$newArr,$firstSpottedPos,$firstCheck,$firstExtraArr,$signalReversed);
								refineSignalCheck($signalVal,$SignalStrength=0,$signal,$OutputStr='',$signalSplit,$signalVal['deviceOne'],$signalVal['deviceTwo'],$signalVal['nextCheck'],$signalVal['newArr'],$signalVal['firstSpottedPos'],$signalVal['firstCheck'],$signalVal['firstExtraArr'],$signalVal['signalReversed']);
							die;
							}
						}else{
							echo "Path not found.";
						exit;
						}
					}
				}else{
					echo "Path not founds.";
					exit;
				}
			}
	   }
	   else
	   {
		  echo "Invalid Inputs";
	   }
	}
	else
	   {
		  echo "Invalid Inputs";
	   }
 }
}else{
		echo "CSV File path missing or does not exit.";
	}
}
?>
