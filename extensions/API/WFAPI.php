<?php

class WFAPI extends API{

    function WFAPI(){
        $this->addGET("name", false, "The User Name of the researcher to get.  The name must be in the format First.Last", "Eleni.Stroulia");
        $this->addGET("id", false, "The ID of the researcher to get", "3");
        $this->addGET("type", false, "The type of user to get", PNI);
        $this->addGET("format", false, "The format of the output(can either be 'xml' or 'json').  If this value is not provided, then xml is assumed", "xml");
	}

  function processParams($params){
    if(isset($_GET['id'])){
      $_GET['name'] = $_GET['id'];
    }
    $i = 0;
	  foreach($params as $param){
	    if($i != 0){
	      if(strtoupper($param) == PNI){
	        $_GET['type'] = PNI;
	      }
	      else if(strtoupper($param) == CNI){
	        $_GET['type'] = CNI;
	      }
	      else if(strtoupper($param) == HQP){
	        $_GET['type'] = HQP;
	      }
	      else if(strtoupper($param) == "STAFF"){
	        $_GET['type'] = STAFF;
	      }
	      else if(strtoupper($param) == "XML"){
	        $_GET['format'] = "XML";
	      }
	      else if(strtoupper($param) == "JSON"){
	        $_GET['format'] = "JSON";
	      }
	      else if(!isset($_GET['name']) && is_numeric($param)){
	        $person = Person::newFromId($param);
	        if($person != null && $person->getName() != null){
	          $_GET['name'] = $person->getName();
	        }
	      }
	      else if(!isset($_GET['name'])){
	        $_GET['name'] = $param;
	      }
	    }
	    $i++;
	  }
  }

	function doAction(){
	    $start = microtime(true);
        if(!isset($_GET['format']) || $_GET['format'] == "XML"){
	        header("Content-type: text/xml");
	    }
	    else if($_GET['format'] == "JSON"){
	        header("Content-type: text/json");
	    }
	    $cache = new WFCache($this);
		echo $cache->getCache();
		exit;
	}
	
	function outputXML($people){
	    global $wgScriptPath, $wgServer;
	    $xml = "<researchers>\n";
	    foreach($people as $person){
            $name = $person->splitName();
            $xml .= "\t<researcher type=\"{$person->getType()}\" id=\"{$person->getId()}\">\n";
            $xml .= "\t\t<firstname>{$name['first']}</firstname>\n";
            $xml .= "\t\t<lastname>{$name['last']}</lastname>\n";
            $xml .= "\t\t<projects>\n";
            foreach($person->getProjects() as $project){
                $leader = " leader='false'";
                $coLeader = " coleader='false'";
                $leaders = $project->getLeaders();
                $coLeaders = $project->getCoLeaders();
                if(count($leaders) > 0){
                    foreach($leaders as $l){
                        if($l->getId() == $person->getId()){
                            $leader = " leader='true'";
                        }
                    }
                }
                if(count($coLeaders) > 0){
                    foreach($coLeaders as $l){
                        if($l->getId() == $person->getId()){
                            $coLeader = " coleader='true'";
                        }
                    }
                }
                $xml .= "\t\t\t<project id=\"{$project->getId()}\" name=\"{$project->getName()}\" $leader $coLeader />\n";
            }
            $xml .= "\t\t</projects>\n";
            if($person->isHQP()){
                $xml .= "\t\t<supervisors>\n";
                foreach($person->getSupervisors() as $supervisor){
                    $xml .= "\t\t\t<supervisor id='{$supervisor->getId()}'>{$supervisor->getName()}</supervisor>\n";
                }
                $xml .= "\t\t</supervisors>\n";
            }
            else{
                $xml .= "\t\t<hqps>\n";
                foreach($person->getHQP() as $hqp){
                    $xml .= "<hqp id='{$hqp->getId()}'>{$hqp->getName()}</hqp>\n";
                }
                $xml .= "\t\t</hqps>\n";
            }
            $xml .= "\t</researcher>";
		}
		$xml .= "</researchers>\n";
		return $xml;
	}
	
	function outputJSON($people){
        global $wgScriptPath, $wgServer;
        $json = array();
        foreach($people as $person){
            $name = $person->splitName();
            $projects = array();
            foreach($person->getProjects() as $project){
                $leader = "false";
                $coLeader = "false";
                $leaders = $project->getLeaders();
                $coLeaders = $project->getCoLeaders();
                if(count($leaders) > 0){
                    foreach($leaders as $l){
                        if($l->getId() == $person->getId()){
                            $leader = "true";
                        }
                    }
                }
                if(count($coLeaders) > 0){
                    foreach($coLeaders as $l){
                        if($l->getId() == $person->getId()){
                            $coLeader = "true";
                        }
                    }
                }
                $projects[] = array("id" => $project->getId(), "name" => $project->getName(), "leader" => $leader, "coleader" => $coLeader);
            }
            $p = array("type" => $person->getType(),
                       "id" => $person->getId(),
                       "firstname" => $name['first'],
                       "lastname" => $name['last'],
                       "projects" => $projects
                        );
            if($person->isHQP()){
                $supervisors = array();
                foreach($person->getSupervisors() as $supervisor){
                    $obj = array();
                    $obj['name'] = $supervisor->getName();
                    $obj['id'] = $supervisor->getId();
                    $supervisors[] = $obj;
                }
                $p["supervisors"] = $supervisors;
            }  
            else{
                $hqps = array();
                foreach($person->getHQP() as $hqp){
                    if($hqp->isRole(HQP)){
                        $obj = array();
                        $obj['name'] = $hqp->getName();
                        $obj['id'] = $hqp->getId();
                        $hqps[] = $obj;
                    }
                }
                $p["hqps"] = $hqps;
            }    
            $json[] = $p;
        }
        return json_encode($json);
	}
	
	function isLoginRequired(){
		return false;
	}
}

class WFCache extends SerializedCache{
    
    var $api;
    
    function WFCache($api){
        $this->api = $api;
        parent::SerializedCache(implode("", $_GET), "wf");
    }
    
    function run(){
        $all = false;
		if(!isset($_GET['name'])){
			$all = true;
		}
		if($all){
            if(isset($_GET['type'])){
                if($_GET['type'] == STAFF){
                    $people = array();
                    $people = Person::getAllStaff();
                }
                else{
                    $people = Person::getAllPeople($_GET['type']);
                }
            }
            else {
		        $people = Person::getAllPeople();
		    }
		}
		else{
            $person = Person::newFromName($_GET['name']);
            if($person == null || $person->getName() == null){
                return "There is no user by the name of \"{$_GET['name']}\"";
            }
            $people = array($person);
        }
        // Determining what format to output
        if(isset($_GET['format']) && strtoupper($_GET['format']) == "JSON"){
            $output = $this->api->outputJSON($people);
        }
        else if(isset($_GET['format']) && strtoupper($_GET['format']) == "XML"){
            $output = $this->api->outputXML($people);
        }
        else if(isset($_GET['format'])){
            return "Format \"{$_GET['format']}\" not supported";
        }
        else{
            $output = $this->api->outputXML($people);
        }
        return $output;
    }
}

?>
