<? 
   
  //pathinfo($file);
    
//first we tell php the filename

$file = './apps/complaints/word/ack2.rtf';

// then we try to open the file, using the r mode, for reading only 

$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 

// read file contents 

$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 

$complaintNumber = "dsfjshdfsjdfhasljfhsafsdf";

$sapCustomer = "sap sap sap sap sap sap";

$data = str_replace('**ID**',$complaintNumber,$data);

$data = str_replace('**SAP Customer**',$sapCustomer,$data);

// close file 

fclose($fp);

// print file contents 

print "The data in the file is \"".$data."\"";



// Save the file here

$fpSaveFile = './apps/complaints/word/test.rtf';

$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file!'); 

fwrite($fpSave, $data); 

fclose($fpSave);

?>