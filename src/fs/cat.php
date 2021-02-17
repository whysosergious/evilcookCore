<?php

  /**
   * We supply a list of targeted components and copy them to a separate directory for processing. =D
   */

  // ZCM directory
  $zcm_dir = 'ZergskiManager';

  $data = json_decode(file_get_contents('php://input'), true);  // object with file queue
  $ground_zero_path = $data['catArray'][0]['path'];   // path of init file
  $ground_zero_file = $data['catArray'][0]['file'];   // name of init file
  $file_list = $data['catArray'];   // decoupling data array
  $list_length = count( $file_list );   // total items in queue
  
  // init message
  // function console_log($output, $with_script_tags = true) {
  //   $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
  // ');';
  //     if ($with_script_tags) {
  //         $js_code = '<script>' . $js_code . '</script>';
  //     }
  //     return $js_code;
  // }
  
  $log[] = "Started processing files with  =>  '$ground_zero_file'";

  // echo "Started processing files with  =>  '$ground_zero_file'. \n";

  // create ZCM directory if none exists
  if(!is_dir("../$zcm_dir")) {

		$dir = mkdir("../$zcm_dir", 0777);
	}

  // tracking file queue
  $current_path = $ground_zero_path === 'root' ? '' : $ground_zero_path . "/" ;
  $current_file = $ground_zero_file;
  $count = 0;

  

  // main copycat function
  function _cat( $file, $path, $dir, $count, $file_list, $list_length ) {
    global $log;
    // ++$count;
    $prep_count = ++$count . "/" . $list_length . " || " ;  // iterate and display progress
    
    // create path directory for current file
    if ( $path != '' && !is_dir("../" . $dir . "/" . $path) ) {
      mkdir( "../" . $dir . "/" . $path, 0777, true );
    } 

    // extension description : z-pre-proccessed
    if ( copy("../$path$file", "../$dir/$path$file.zprep") ) {

      $log[] = $prep_count . "Cat copied '$file' to |[ 'src/$dir/$path' ]|.";  // task msg

      _prepComponent( $count, $file, $file_list[$count-1]['path'], file_get_contents("../$dir/$path$file.zprep") );
      // check if last
      if ( $count < $list_length ) {

        // next path and file
        $path = $file_list[$count]['path'] === 'root' ? '' :  $file_list[$count]['path'] . "/";
        $file = $file_list[$count]['file'];
        _cat( $file, $path, $dir, $count, $file_list, $list_length );   // next
      } else {

        $log[] = "**** Cat done! ^-^ ****";   // copycat finished msg
        _respond();
      }
    } else { 

      // else =)
      $log[] = "**!!  Cat failed to copy '$file', check file path and name. If one already exists in 'src/$dir/$path' it needs be rewriteble  !!**.";
    }
  } _cat( $current_file, $current_path, $zcm_dir, $count, $file_list, $list_length ); // init run

  $response = array();



  function _prepComponent( $count, $file, $path, $code ) {
    global $response;

    $response[ ] = (object) [ "data" => [
      "name" => $file, 
      "path" => $path, 
      "code" => $code,
      "queue" => $count ]
    ];
  }

  function _respond() {
    global $response, $log;

    echo json_encode([ "log" => $log, "content" => $response ]);
  }
?>