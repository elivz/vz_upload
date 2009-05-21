<?php
if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = str_replace('//','/',$_GET['folder'] . '/');
	$targetFile =  $_FILES['Filedata']['name'];
			
	// Create the directory if it doesn't exist
	//mkdir(str_replace('//','/',$targetPath), 0755, true);
	
    // Make sure the filename is unique
    $targetFile = getUnique($targetFile,$targetPath);
    
	move_uploaded_file($tempFile, $targetPath.$targetFile);
}
	
echo $targetFile;

// Check if the file already exists and, if so,
// increments a number at the end until it is unique
function getUnique($fileName,$path)
{
    $baseName = preg_replace('/^(.*)\.[^.]+$/', '\\1', $fileName);
    $extension = preg_replace('/^.*(\.[^.]+$)/', '\\1', $fileName);
    $i = 1;
    
    while (file_exists($path.$fileName)) {
        $fileName = $baseName . '_' . $i . $extension;
        $i++;
    }
    
    return $fileName;
}
?>