<?php

	$name = "Jason";
	$email = "jason.matthews@scapa.com";

?>

<?php
    
        $to = "$name <$email>"; 
        $from = "Jason Matthews <jason.matthews@scapa.com>"; 
        $subject = "Scapa Ltd - Document Enclosed"; 
    
        $fileatt = "/home/dev/apps/complaints/word/files/ack38079.rtf";
        $fileatttype = "application/rtf"; 
        $fileattname = "ack38079.rtf";
    
        $headers = "From: $from";
        
?>

<?php
    
        $file = fopen( $fileatt, 'rb' ); 
        $data = fread( $file, filesize( $fileatt ) );
        fclose( $file );
        
?>

<?php
    
        $semi_rand = md5( time() ); 
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
    
        $message = "Hi Jason, This is a email sent from the complaints system with an attachment.";
        
        $headers .= "\nMIME-Version: 1.0\n" . 
                    "Content-Type: multipart/mixed;\n" . 
                    " boundary=\"{$mime_boundary}\"";
    
        $message = "This is a multi-part message in MIME format.\n\n" . 
                "--{$mime_boundary}\n" . 
                "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . 
                "Content-Transfer-Encoding: 7bit\n\n" . 
                $message . "\n\n";
    
        $data = chunk_split( base64_encode( $data ) );
                 
        $message .= "--{$mime_boundary}\n" . 
                 "Content-Type: {$fileatttype};\n" . 
                 " name=\"{$fileattname}\"\n" . 
                 "Content-Disposition: attachment;\n" . 
                 " filename=\"{$fileattname}\"\n" . 
                 "Content-Transfer-Encoding: base64\n\n" . 
                 $data . "\n\n" . 
                 "--{$mime_boundary}--\n"; 
                 
?>

<?php
        
        if( mail( $to, $subject, $message, $headers ) ) {
         
            echo "<p>The email was sent.</p>"; 
         
        }
        else { 
        
            echo "<p>There was an error sending the mail.</p>"; 
         
        }
    
    

?>