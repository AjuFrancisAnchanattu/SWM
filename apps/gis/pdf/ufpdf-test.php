<?php
define('FPDF_FONTPATH', $_SERVER['DOCUMENT_ROOT'] . '/apps/pricing/pdf/font/');
include_once('ufpdf.php');

$pdf = new UFPDF();
$pdf->Open();
$pdf->SetTitle("UFPDF is Cool.");
$pdf->SetAuthor('Steven Wittens');
$pdf->AddFont('Arial', '', 'arial.php');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 32);
$pdf->Write(12, "UFPDF is Cool.\n");
$pdf->Write(12, "sdf");
$pdf->Write(12, "sdf");
$pdf->Close();
$pdf->Output();

?>