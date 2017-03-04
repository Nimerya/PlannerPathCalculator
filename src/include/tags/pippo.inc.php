<?php

	Class pippo extends TagLibrary {
		
		
		function dummy(){
			
		}
		
		function format($name, $data, $pars) {
			
			
			
			$y = substr($data, 0, 4);
			$m = substr($data, 4, 2);
			$d = substr($data, 6, 2);
			
			return "$d/$m/$y";
			
		}
		
		function extended($name, $data, $pars) {
		    
		    
		    $y = substr($data, 0, 4);
		    $m = substr($data, 4, 2);
		    $d = substr($data, 6, 2);
		    
		    return date("D F d, Y",mktime(0, 0, 0, $m, $d, $y));
		}
		
		function cut($name, $data, $pars) {
		    
		    $data = wordwrap($data, 280, "|");
		    $pieces = explode("|",$data);
		    
		    return $pieces[0]." [...]";
		}
		
		
		
		
		
		
		
	}

?>