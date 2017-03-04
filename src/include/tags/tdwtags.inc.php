<?php

	Class tdwtags extends TagLibrary {
		
		
		function dummy(){
			
		}
		
		function creatediv($name, $data, $pars) {
			
			
			if ($data != "") {
				$content = "<div style=\"border: 1px solid red; padding: 20px; border-radius: 4px; height: 80px; width: 300px\">$data</div>";
			} else {
				$content = "<!-- message normally goes here! -->\n";
			}
			return $content;
			
		}
		
		
		
		function prettydate($name, $data, $pars) {
			
			$y = substr($data, 0, 4);
			$m = substr($data, 4, 2);
			$d = substr($data, 6, 2);
			
			return "$d/$m/$y";
			
		}
		
		function getselect($name, $data, $pars) {
			
			$content = "<select name=\"{$name}\">\n";
			foreach($data as $key => $value) {
				$content .= "  <option>{$value['script']} ({$value['name']})</option>\n";
			}
			$content .= "</select>\n";
			return $content;
		}
		
		function getradio($name, $data, $pars) {
				
			$content = "";
			foreach($data as $key => $value) {
				$content .= "  <input type=\"radio\" name=\"{$name}\" value=\"{$value['script']}\">{$value['script']} ({$value['name']})<br>\n";
			}
			
			return $content;
		}
		
		
		
	}

?>