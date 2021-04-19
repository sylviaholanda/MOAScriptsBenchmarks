<?php

set_time_limit(92000000000000);//
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

ini_set('memory_limit', '2G');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);



function getListElementsDirectory($base_directory){

	$elements_list = array();

	if ($handle = opendir($base_directory)) {

		/* Esta é a forma correta de varrer o diretório */
		while (false !== ($element = readdir($handle))) {
			 
			if(is_dir($base_directory.$element)){

			if($element != "." && $element != "..")
				array_push($elements_list, $element);				
						
			}
			
		}

		closedir($handle);
	}

	sort($elements_list);

	return $elements_list;
}


function getListElementsDirectoryFile($base_directory_destine, $filter=array("data")){
	
	$files_list = array();
	
	if ($handle = opendir($base_directory_destine)) {

	    /* Esta é a forma correta de varrer o diretório */
	    while (false !== ($file = readdir($handle))) {
	        
			if(!is_dir($base_directory_destine.$file)){
				
				if($file != "." && $file != ".."){
					
					if(in_array(substr($file, strrpos($file,".")+1), $filter) ){
					
						array_push($files_list, $file);
					}
					
				}
				
			}	
			
			

	    }
	
	    closedir($handle);
	}
	
	sort($files_list);
	
	return $files_list;
}


function readFilename($filename){
		
	$result=array();
	
	$handle = fopen($filename, "r");
	
	if ($handle) {
		
		while (($buffer = fgets($handle, 4096)) !== false) {
	        $result[] = $buffer;
	    }
		
		fclose($handle);
	}
	
	return $result;
}


function save_file($data="", $filename=""){

	$handle = fopen($filename, "w") or die("Unable to open file!");

	fwrite($handle, $data);

	fclose($handle);
}


function basefilename($filename = ""){
    return preg_replace("/\.[^.]+$/", "", $filename);
}













	$base_directory =  dirname(__FILE__);
	
	if(!isset($_GET['classifier']))
		$classifier = "nb";
	else
		$classifier = $_GET['classifier'];
		
		
	$addm = false;
	$addm_files_data = array();
	
	if(is_dir($base_directory	. DIRECTORY_SEPARATOR . 'addm'))
	{
		$file_elements = getListElementsDirectoryFile(
													$base_directory
													. DIRECTORY_SEPARATOR 
													. 'addm'
													. DIRECTORY_SEPARATOR
													, array("data"));
			
		foreach($file_elements as $element_file){
		    $filename = $base_directory
    		    . DIRECTORY_SEPARATOR
    		    . 'addm'
		        . DIRECTORY_SEPARATOR
		        . $element_file;		        
		        
	        if(is_file($filename)) {
		        
                $data = readFilename($filename);
                
                $addm_lines = array();
                
                foreach($data as $key=>$item){
                    
                    if(substr(trim($item),0,1)!="#" && trim($item) != ""){
                        $addm_lines[] = trim($item);
                    }
                }
                
                $addm_files_data[strtolower(basefilename($element_file))] = $addm_lines;
                $addm = true;
		    }
		    
		}
		
	}
		
		
	$base_directory_datasets = $base_directory.DIRECTORY_SEPARATOR
	."datasets".DIRECTORY_SEPARATOR;
	//.$methodology
	//.DIRECTORY_SEPARATOR;
		
	$dataset_directory_elements = getListElementsDirectory(
	    $base_directory_datasets);
	
	
	
	//leitura dos métodos
	$base_directory_methods = $base_directory.DIRECTORY_SEPARATOR
									."methods"
									.DIRECTORY_SEPARATOR;
	
	$classifiers = getListElementsDirectory(
											$base_directory_methods);
				
	foreach($classifiers as $index=>$classifier){
		
		//leitura dos métodos
		$base_directory_methods = $base_directory.DIRECTORY_SEPARATOR
									."methods"
									.DIRECTORY_SEPARATOR
									.$classifier.DIRECTORY_SEPARATOR;
		
		
		
		$methods_list = array();
		
		$file_elements = getListElementsDirectoryFile(
				$base_directory_methods
				, array("data"));
			
		foreach($file_elements as $element_file){
				
			$data = readFilename($base_directory_methods
					.$element_file);
				
			$lines = array();
				
			foreach($data as $key=>$item){
		
				if(substr(trim($item),0,1)!="#" && trim($item) != ""){
					$lines[] = trim($item);
				}
			}
				
			$methods_list[] = array("file"=>$element_file,
					"name"=>substr($element_file,0, strrpos($element_file,".")),
					"methods"=>$lines
		
			);
		}
		
		

//		if(!isset($_GET['methodology']))
//			$methodology = "sw";
//		else
//			$methodology = $_GET['methodology'];
			
				
		// leitura das datasets
			

									
		$dataset_list = array();
		
		$script_generate = array();
		
		
		foreach($dataset_directory_elements as $element){
			
			$file_elements = getListElementsDirectoryFile(
														$base_directory_datasets
														.$element, array("data"));
			$dataset_list = array();
			
			foreach($file_elements as $element_file){
				
				$data = readFilename($base_directory_datasets
										.$element
										.DIRECTORY_SEPARATOR
										.$element_file);
				
				$lines = array();
				
				foreach($data as $key=>$item){
				
					if(substr(trim($item),0,1)!="#" && trim($item) != ""){
						$lines[] = trim($item);
					}
				}
				
				
				
				
				$dataset_list[] = array("directory"=>$element, 
										"file"=>$element_file,
										"datasets"=>$lines
										//"name"=>$element."-".substr($element_file,0, strrpos($element_file,"."))
										);
			}
			
// 			if($classifier=="nb"){
// 			 var_dump($dataset_list);
// 			 exit();
// 			}

			
			$filename_dataset = $element;
			
			
			foreach($methods_list as $method){
					
				$filename_method = "";				
					
				foreach($method as $method_key=>$method_item){
			
			
					if($method_key == "methods"){
						
												
						foreach($dataset_list as $dataset){
							
							$directory = $dataset['directory'];
							$count_datasets = 0;	
							foreach($dataset["datasets"] as $dataset_item){							
								
								foreach($method_item as $method_script){
									
									if($addm == true)
									{
										if($directory != 'real')
										{
										    
											if(strpos($method_script,'%ADDM%')!==false)
											{
											    
											    if(isset($addm_files_data[strtolower($filename_method)])){
											        
												    $method_script = str_replace('%ADDM%',
												                $addm_files_data[strtolower($filename_method)][$count_datasets],
																$method_script);	
											    }
											}
											
											$script_generate[] = array(
												"filename_method"=>$filename_method,
												"directory_dataset"=>$filename_dataset,
												"script"=>$method_script." ".$dataset_item
											);
										}
										else
										{
											if(strpos($method_script,'%ADDM%')===false)
											{
												$script_generate[] = array(
													"filename_method"=>$filename_method,
													"directory_dataset"=>$filename_dataset,
													"script"=>$method_script." ".$dataset_item
												);	
											}											
										}
										
									}
									else
									{
										$script_generate[] = array(
											"filename_method"=>$filename_method,
											"directory_dataset"=>$filename_dataset,
											"script"=>$method_script." ".$dataset_item
										);
									}
									
									
									
								}
								
								$count_datasets++;	
							}
							
								
						}
// 						if($classifier=="nb"){
// 						  var_dump($script_generate);exit();
// 						}
						
					}else{
							
						if($method_key == "name")
							$filename_method = $method_item;
							//var_dump($methods_list);exit();
					}
					
					
				}
				
			}
			
			//break;
// 			var_dump($script_generate);exit();
			
			
		}
		
		
// 		if($classifier=="nb"){
// 		  var_dump($script_generate);exit();
// 		}
		
		$base_directory_output = $base_directory
				. DIRECTORY_SEPARATOR
				. "output"
			    . DIRECTORY_SEPARATOR
			//	. $classifier
				//. DIRECTORY_SEPARATOR;
		;
			
		foreach($dataset_directory_elements as $newfolder)
    	{
    	    if(!is_dir($base_directory_output . $newfolder))
    	    {    	        
    	        mkdir($base_directory_output . $newfolder, 0777, true);
    	        chmod($base_directory_output . $newfolder, 0777);
    	        
    	        foreach($classifiers as $newfolder2)
    	        {

    	            if(!is_dir($base_directory_output . $newfolder 
    	                . DIRECTORY_SEPARATOR . $newfolder2))
    	            {
    	                mkdir($base_directory_output . $newfolder
    	                    . DIRECTORY_SEPARATOR . $newfolder2, 0777, true);
    	                
    	                chmod($base_directory_output . $newfolder
    	                    . DIRECTORY_SEPARATOR . $newfolder2, 0777);    	                
    	            }
    	        }
    		}
    	}
	
				
				
//     	if(!is_dir($base_directory_output)){
				
// 			mkdir($base_directory_output, 0777, true);
// 			chmod($base_directory_output, 0777);
// 		}
		
	
		
		//criar os arquivos
		$data = "";
		//count($script_generate)
		
		$dataset_last_name = null;
		$method_last_name = null;
		
		
		for($i = 0; $i < count($script_generate); $i++ ){
			

			if(!isset($dataset_last_name))
				$dataset_last_name = $script_generate[$i]["directory_dataset"];
			
			if(!isset($method_last_name))
				$method_last_name = $script_generate[$i]["filename_method"];
			
			
				
			if($dataset_last_name == $script_generate[$i]["directory_dataset"]){
				//sem mudanças
				
				
				if($method_last_name == $script_generate[$i]["filename_method"]){
					//sem mudanlas
				
				}else{
					
					$filename = $base_directory_output
					           . DIRECTORY_SEPARATOR
					           . $dataset_last_name
					           . DIRECTORY_SEPARATOR
			                   . $classifier
			                   . DIRECTORY_SEPARATOR
					           . $method_last_name
							//. "-"
							//. $methodology							
// 							. "-"
// 							. $dataset_last_name							
							.".data";
												
					if(file_exists($filename))
						unlink($filename);
											
					save_file($data, $filename);
						
					$data = "";
					
					$method_last_name = $script_generate[$i]["filename_method"];
					
				}
				
				
				//exit($script_generate[$i]["filename_method"]);
				
				if($data == "")
					$data = $script_generate[$i]["script"];
				else
					$data .= "\n\n".$script_generate[$i]["script"];
				
			}else{
				//ocorreu mudança
				
				
				if($data != ""){
					
					
					//var_dump($dataset_last_name."/".$method_last_name);exit();
					
				    $filename = $base_directory_output
				    . DIRECTORY_SEPARATOR
				    . $dataset_last_name
				    . DIRECTORY_SEPARATOR
				    . $classifier
				    . DIRECTORY_SEPARATOR
				    . $method_last_name
				    .".data";
					
// 				    $filename = $base_directory_output
// 				    .$dataset_last_name
// 				    . "-"
// 				        . $index
// 				        . "-"
// 				            . $classifier
// 				            . "-"
// 				                . $method_last_name
// 				                //. "-"
// 				    //. $methodology
// 				    // 							. "-"
// 				    // 							. $dataset_last_name
// 				    .".data";
						
					if(file_exists($filename))
						unlink($filename);
							
					save_file($data, $filename);
					
					$data = "";
							
					$method_last_name = $script_generate[$i]["filename_method"];
					
					$data = "";
				}
				
				
				
				$data = $script_generate[$i]["script"];
					
				$dataset_last_name = $script_generate[$i]["directory_dataset"];
				
			}
			
						
		}

		
		
		if($data != ""){
		
		
			//var_dump($dataset_last_name."/".$method_last_name);exit();
		  
		    $filename = $base_directory_output
		    . DIRECTORY_SEPARATOR
		    . $dataset_last_name
		    . DIRECTORY_SEPARATOR
		    . $classifier
		    . DIRECTORY_SEPARATOR
		    . $method_last_name
		    .".data";
		    
		    
// 		    $filename = $base_directory_output
// 		    .$dataset_last_name
// 		    . "-"
// 		        . $index
// 		        . "-"
// 		            . $classifier
// 		            . "-"
// 		                . $method_last_name
// 		                //. "-"
// 		    //. $methodology
// 		    // 							. "-"
// 		    // 							. $dataset_last_name
// 		    .".data";
				
			if(file_exists($filename))
				unlink($filename);
		
			save_file($data, $filename);
			
		}
	
	
		
	
	}
	
		$element = array();
		
		foreach($dataset_directory_elements as $newfolder)
    	{
    	    if(is_dir($base_directory_output . $newfolder))
    	    {    	        
    	        $element[] = substr($newfolder,strrpos($newfolder, "/"));
    		}
    	}
	
	$s = dirname(__FILE__);
	$s = explode("/",$s);
	$s = $s[count($s)-2] . $s[count($s)-1] . ".zip";

	$filename =  $s;//"benkmark20180827.zip";

	$dir = $base_directory_output
		   ;
	
	if (file_exists($dir . $filename)) 
	{
		unlink($dir . $filename);
	}

	// create zip
	create_zipfile($dir, $filename, $element);
                    
 
    header('Content-Type: application/force-download');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Content-Length: ' . filesize("output/".$filename));
    readfile("output/".$filename);
    
    
function create_zipfile($dir, $filename, $element)
{
    $zip = new ZipArchive();

    if ($zip->open($dir . $filename, ZIPARCHIVE::CREATE) !== TRUE) {
        exit("cannot open <$filename>\n");
    }

    // $dir = $dir = Properties::getBase_directory_destine($application)
    // .$application->getUser()
    // .DIRECTORY_SEPARATOR
    // .$application->getParameter("folder");

    foreach ($element as $key => $item) {

        if (is_dir($item)) {} else {
            if (is_file($dir . $item . ".data")) {
                $item .= ".data";
            }
        }

        
        if (is_file($dir . $item)) {

            $dir_ = preg_replace('/[\/]{2,}/', DIRECTORY_SEPARATOR, $dir . $item);

            // $folder_last = substr($dir_, strlen($dir));

            // echo $folder_last;

            // exit();

            $zip->addFile($dir . $item, $item);
        } else {

            $dir_ = preg_replace('/[\/]{2,}/', DIRECTORY_SEPARATOR, $dir . $item . DIRECTORY_SEPARATOR);

            $dirs = array(
                $dir_
            );

            while (count($dirs)) {

                $dir_ = current($dirs);

                // echo $dir_;
                //
                // $folder_last = substr($dir_, 0, strrpos($dir_, DIRECTORY_SEPARATOR));
                // $folder_last = substr($folder_last, strrpos($folder_last, DIRECTORY_SEPARATOR));

                $folder_last = substr($dir_, strlen($dir));

                // echo $folder_last."<br>";

                // exit();

                if (is_dir($dir_)) {

                    $zip->addEmptyDir($folder_last);
                } else {}

                $dh = opendir($dir_);
                while ($file = readdir($dh)) {

                    if ($file != '.' && $file != '..') {

                        // echo $folder_last.$file."<br>";

                        if (is_file($dir_ . $file)) {

                            // var_dump($dir_.$file);

                            // echo $item.DIRECTORY_SEPARATOR.$file;

                            // exit("--");

                            $zip->addFile($dir_ . $file, $folder_last . $file);
                        } else { // if (is_dir($file)){

                            $dirs[] = $dir_ . $file . DIRECTORY_SEPARATOR;
                        }
                    }
                }
                closedir($dh);
                array_shift($dirs);
            }
        }

        // $folder = $application->getParameter("folder");

        // }
        // }

        // echo $item."<br>";
    }

    $zip->close();
}

                    
    
?>
