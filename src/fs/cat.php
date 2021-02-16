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
  echo $response = "Start processing files with  =>  '$ground_zero_file'. \n";

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
    
    echo ++$count . "/" . $list_length . " || ";  // iterate and display progress
    
    // create path directory for current file
    if ( $path != '' && !is_dir("../" . $dir . "/" . $path) ) {
      mkdir( "../" . $dir . "/" . $path, 0777, true );
    } 

    // extension description : z-pre-proccessed
    if ( copy("../$path$file", "../$dir/$path$file.zprep") ) {

      echo "Cat copied '$file' to |[ 'src/$dir/$path' ]|. \n";  // task msg

      // check if last
      if ( $count < $list_length ) {

        // next path and file
        $path = $file_list[$count]['path'] === 'root' ? '' :  $file_list[$count]['path'] . "/";
        $file = $file_list[$count]['file'];
        _cat( $file, $path, $dir, $count, $file_list, $list_length );   // next
      } else {

        echo "**** Cat done! ^-^ ****";   // copycat finished msg
      }
    } else { 

      // error message, details given in the warning on the previous line
      echo "**!!  Cat failed to copy '$file', check file path and name. If one already exists in 'src/$dir/$path' it needs be rewriteble  !!**. \n";
    }
  } _cat( $current_file, $current_path, $zcm_dir, $count, $file_list, $list_length ); // init run

?>