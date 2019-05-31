<?php

// Print PDF Invoice Template File

require(INCLUDE_URL.SEP.'fpdf'.SEP.'fpdf.php');

// Xavier Nicolay 2004
// Version 1.01

class PDF extends FPDF
{

// Adds a watermark (Temporary, Estimate, Duplication...)
function temporary()
{
    //$watermark = $balinv;
    $this->SetFont('Arial','B',20);
    $this->SetTextColor(239,241,255);
    $this->Rotate(45,55,190);
    $this->Text(100,190, "COPY" ,0,0, "C");
    $this->Rotate(0);
    $this->SetTextColor(0,0,0);
}

// private variables
var $colonnes;
var $format;
var $angle=0;

// private functions
function RoundedRect($x, $y, $w, $h, $r, $style = '')
{
    $k = $this->k;
    $hp = $this->h;
    if($style=='F')
        $op='f';
    elseif($style=='FD' or $style=='DF')
        $op='B';
    else
        $op='S';
    $MyArc = 4/3 * (sqrt(2) - 1);
    $this->_out(sprintf('%.2f %.2f m',($x+$r)*$k,($hp-$y)*$k ));
    $xc = $x+$w-$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2f %.2f l', $xc*$k,($hp-$y)*$k ));

    $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
    $xc = $x+$w-$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2f %.2f l',($x+$w)*$k,($hp-$yc)*$k));
    $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
    $xc = $x+$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2f %.2f l',$xc*$k,($hp-($y+$h))*$k));
    $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
    $xc = $x+$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-$yc)*$k ));
    $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
    $this->_out($op);
}

function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
{
    $h = $this->h;
    $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c ', $x1*$this->k, ($h-$y1)*$this->k,
                        $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
}
function Rotate($angle,$x=-1,$y=-1)
{
    if($x==-1)
        $x=$this->x;
    if($y==-1)
        $y=$this->y;
    if($this->angle!=0)
        $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
}
function _endpage()
{
    if($this->angle!=0)
    {
        $this->angle=0;
        $this->_out('Q');
    }
    parent::_endpage();
}

// Company
function addCompany( $nom, $address )
{
    $x1 = 10;
    $y1 = 8;
    $test2 = $invoice['invoice_id'];
    //Position from bottom
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial','B',8);
    $length = $this->GetStringWidth( $nom );
    $this->Cell( $length, 2, $nom);
    $this->SetXY( $x1, $y1 + 4 );
    $this->SetFont('Arial','',8);
    $length = $this->GetStringWidth( $address );
    //Coordonnes de la societe
    //$lines = $this->sizeOfText( $address, $length) ;
    $this->MultiCell(40, 4, $address);
}

// Label and number of invoice/estimate
function fact_dev( $label, $num )
{
    $r1  = $this->w - 80;
    $r2  = $r1 + 68;
    $y1  = 6;
    $y2  = $y1 + 2;
    $mid = ($r1 + $r2 ) / 2;

    $text  = $label." ". $num;
    $szfont = 12;
    $loop   = 0;

    while ( $loop == 0 )
    {
       $this->SetFont( "Helvetica", "B", $szfont );
       $sz = $this->GetStringWidth( $text );
       if ( ($r1+$sz) > $r2 )
          $szfont --;
       else
          $loop ++;
    }

    $this->SetLineWidth(0.1);
    $this->SetFillColor(192);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
    $this->SetXY( $r1+1, $y1+2);
    $this->Cell($r2-$r1 -1,5, $text, 0, 0, "C" );
}

// Estimate
function addQuote( $numdev )
{
    $string = sprintf("DEV%04d",$numdev);
    $this->fact_dev( "Quote", $string );
}

// Invoice
function addInvoice( $numfact )
{
    $string = sprintf("",$numfact);
    $this->fact_dev( "", $string );
}

function addDate( $date )
{
    $r1  = 175; //distance from right
    $r2  = $r1 + 25;
    $y1  = 35;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(10,4, "Invoice Date", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(10,5,$date, 0,0, "C");
}

function addClient( $ref )
{
    $r1  = 175; //distance from right
    $r2  = $r1 + 25;
    $y1  = 40;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(10,4, "Customer #", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(10,5,$ref, 0,0, "C");
}

function addPageNumber( $page )
{
    $r1  = $this->w - 130;
    $r2  = $r1 + 19;
    $y1  = 260;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    //$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
    //$this->Line( $r1, $mid, $r2, $mid);
	$this->SetXY(  $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "ARIAL", "B", 6);
    $this->Cell(13,5, "PAGE", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9);
    $this->SetFont( "Helvetica", "", 6);
    //$this->Cell(10,5,$page, 0,0, "C");
    $this->Cell(15,5,''.$this->PageNo().' of {nb}',0,0,"C");

}

// Client address
function addClientAddress( $address )
{
    $r1     = $this->w - 175;
    $r2     = $r1 + 68;
    $y1     = 55;
    $this->SetXY( $r1, $y1);
    $this->SetFont("ARIAL", "B", 10);
    $this->MultiCell( 60, 4, $address);
}

// Payment Terms
function addReglement( $mode )
{
    $r1  = 175;
    $r2  = $r1 + 25;
    $y1  = 60;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1+1 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(10,4, "Terms", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1 + 5 );
    $this->SetFont( "Helvetica", "", 8);
    $this->Cell(10,5,$mode, 0,0, "C");
}

// Invoice Due date
function InvoiceDue( $date )
{
    $r1  = 175; //distance from right
    $r2  = $r1 + 25;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(10,4, "DUE DATE", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(10,5,$date, 0,0, "C");
}

function addSKU($inv1)
{
    $inv1 = "Invoice Details";
	$this->SetFont( "Helvetica", "", 10);
    $length = $this->GetStringWidth( $inv1 );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = 92;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length,4,$inv1);
}

function addCols( $tab )
{
    global $colonnes;

    $r1  = 10;
    $r2  = $this->w - ($r1 * 2) ;
    $y1  = 100;
    $y2  = $this->h - 50 - $y1;
    $this->SetXY( $r1, $y1 );
    $this->Rect( $r1, $y1, $r2, $y2, "D");
    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
    $colX = $r1;
    $colonnes = $tab;
    while ( list( $lib, $pos ) = each ($tab) )
    {
        $this->SetXY( $colX, $y1+2 );
        $this->Cell( $pos, 1, $lib, 0, 0, "C");
        $colX += $pos;
        $this->Line( $colX, $y1, $colX, $y1+$y2);
    }
}

function addLineFormat( $tab )
{
    global $format, $colonnes;

    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        if ( isset( $tab["$lib"] ) )
            $format[ $lib ] = $tab["$lib"];
    }
}

function lineVert( $tab )
{
    global $colonnes;

    reset( $colonnes );
    $maxSize=0;
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        $text = $tab[ $lib ];
        $longCell  = $pos -2;
        $size = $this->sizeOfText( $text, $longCell );
        if ($size > $maxSize)
            $maxSize = $size;
    }
    return $maxSize;
}

// Now lets write some HTML links for PayPal on the PDF invoice and insert button
var $B;
var $I;
var $U;
var $HREF;

function PDF($orientation='P',$unit='mm',$format='A4')
{
    //Call parent constructor
    $this->FPDF($orientation,$unit,$format);
    //Initialization
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';
}

function WriteHTML($html)
{
    //HTML parser
    $html=str_replace("\n",' ',$html);
    $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
    foreach($a as $i=>$e)
    {
        if($i%2==0)
        {
            //Text
            if($this->HREF)
                $this->PutLink($this->HREF,$e);
            else
                $this->Write(5,$e);
        }
        else
        {
            //Tag
            if($e{0}=='/')
                $this->CloseTag(strtoupper(substr($e,1)));
            else
            {
                //Extract attributes
                $a2=explode(' ',$e);
                $tag=strtoupper(array_shift($a2));
                $attr=array();
                foreach($a2 as $v)
                    if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
                        $attr[strtoupper($a3[1])]=$a3[2];
                $this->OpenTag($tag,$attr);
            }
        }
    }
}

function OpenTag($tag,$attr)
{
    //Opening tag
    if($tag=='B' or $tag=='I' or $tag=='U')
        $this->SetStyle($tag,true);
    if($tag=='A')
        $this->HREF=$attr['HREF'];
    if($tag=='BR')
        $this->Ln(5);
}

function CloseTag($tag)
{
    //Closing tag
    if($tag=='B' or $tag=='I' or $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF='';
}

function SetStyle($tag,$enable)
{
    //Modify style and select corresponding font
    $this->$tag+=($enable ? 1 : -1);
    $style='';
    foreach(array('B','I','U') as $s)
        if($this->$s>0)
            $style.=$s;
    $this->SetFont('',$style);
}

function PutLink($URL,$txt)
{
    //Put a hyperlink
    $this->SetTextColor(0,0,255);
    $this->SetStyle('U',true);
    $this->Write(5,$txt,$URL);
    $this->SetStyle('U',false);
    $this->SetTextColor(0);
}
}
$html='Click to pay this invoice via PayPal using a valid Credit Card.
<I>NOTE:- A small surcharge applies to this type of payment.</I><BR />';
$html2='Click to pay this invoice via Paymate using a valid Credit Card.
<I>NOTE:- A small surcharge applies to this type of payment.</I><BR />';

//This MySQL connection is not required
//$link = mysqli_connect( "$DB_HOST", "$DB_USER", "$DB_PASS" );

// Start of template itself

//Setting distance down from top
$disty = 250;
//Setting distance in from left
$distx = 5;
//set initial y axis position per page
$y_axis_initial = 100;

//Instanciation of inherited class
define('FPDF_FONTPATH','font/');
//require('invoice.php');

// Company Details - Top Left
$pdf = new PDF( 'P', 'mm', 'A4' );
$pdf->AliasNbPages();
$pdf->Open();
$pdf->AddPage();
if($cabn <> ""){
    $pdf->addCompany( "$cname",
                  "$caddress\n" .
                  "$ccity , $cstate\n" .
                  "P: $cphone\n" .
                  "E: $cemail\n" .
                  "ABN: $cabn\n");
}
if($cabn == ""){
    $pdf->addCompany( "$cname",
                  "$caddress\n" .
                  "$ccity , $cstate\n" .
                  "P: $cphone\n" .
                  "E: $cemail\n" .
                  "");
}


// Logo
$pdf->Image('images/logo.jpg',60,5,0,15,JPG);
$pdf->temporary($company1['COMPANY_NAME'] );
//$pdf->addDate(date('d M Y',($invoice[INVOICE_DATE])));
//$pdf->addClient($invoice[CUSTOMER_ID]);
//
// Invoice Title - Top Right
// $pdf->fact_dev( "INVOICE" ,'');
$pdf->fact_dev($langvals['invoice_prn_invoice_title'],'');

//Add Invoice Totals Box - With credit terms top right
$invdate=(date($date_format ,($invoice[INVOICE_DATE])));
$invdue=(date($date_format ,($invoice[INVOICE_DUE])));

	$pdf->SetY(20);
	$pdf->SetX(140);
        $pdf->SetFont('Arial', 'B', 8);
	$pdf->MultiCell(30, 4, $langvals['invoice_prn_invoice_id']."\n" .
							$langvals['invoice_prn_invoice_date']."\n" .
							$langvals['invoice_prn_invoice_due_date']."\n" .
							$langvals['invoice_prn_work_order']."\n" .
							$langvals['invoice_prn_technician']."\n" .
							$langvals['invoice_prn_credit_terms']."\n"

							, 0, 0, 'R', 0);
	$pdf->SetY(20);
	$pdf->SetX(170);
	$pdf->MultiCell(30, 4, "$invoice[INVOICE_ID]\n" .
							$invdate."\n" .
							$invdue."\n" .
                                                        $invoice['WORKORDER_ID']."\n" .
                                                        $invoice['EMPLOYEE_DISPLAY_NAME']."\n" .
                                                        $custerms."\n"
							, 0, 0, 'L', 0);

// Add Page Numbers
$pdf->addPageNumber("$page");

// Customer Address
$pdf->SetFont('Arial', 'B', 10);
$pdf->addClientAddress( "Bill To:\n" .
                        "$cusdisplay\n" .
                        "$cusaddress\n" .
                        "$cuscity, $cusstate, $cuszip\n");

/* These require HTML Parsing so do not currently work
// Work Order
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetY(45);
$pdf->SetX($distx);
$pdf->Cell(100, 50, $stats['WORK_ORDER_DESCRIPTION'], 2, 1, 'L', 0);

// Work Order Resolution
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetY(45);
$pdf->SetX($distx + 105);
$pdf->Cell(100, 50, $stats['WORK_ORDER_RESOLUTION'], 2, 1, 'L', 0);
  */

// Invoice Details - Above Main Table
//$pdf->addReglement("NETT 7 Days");
//$pdf->InvoiceDue(date('d M Y',($invoice[INVOICE_DUE])));
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetY($y_axis_initial-10);
$pdf->SetX($distx);
$pdf->Cell(195, 10, $langvals['invoice_prn_invoice_details'].$cusdisplay, 2, 1, 'C', 0);
//print column titles for the actual page
//$pdf->SetFillColor(232, 232, 232);

// Create Labour Table Headings
$pdf->SetY($y_axis_initial);
$pdf->SetX($distx);
$pdf->SetFont( "ARIAL", "B", 8);
$pdf->Cell(15, 6, $langvals['invoice_prn_qty'], 1, 0, 'L', 1);
$pdf->Cell(140, 6, $langvals['invoice_prn_labour_items'], 1, 0, 'L', 1);
$pdf->Cell(20, 6, $langvals['invoice_prn_unit_price'], 1, 0, 'R', 1);
$pdf->Cell(20, 6, $langvals['invoice_prn_subtotal'], 1, 0, 'R', 1);

$y_axis = $y_axis + $row_height;

//initialize counter
$i = 0;

//Set maximum rows per page
$max = 15;

//Set Row Height
$row_height = 6;

// Display Labour on invoice
while($labour_row = mysql_fetch_array($labour_row_pdf))
{
    //If the current row is the last one, create new page and print column title

    $labour_code = $labour_row['INVOICE_LABOR_UNIT'];
    $labour_price = sprintf( "%.2f", $labour_row['INVOICE_LABOR_RATE']);
    $labour_name = $labour_row['INVOICE_LABOR_DESCRIPTION'];
    //$labour_subtotal = sprintf( "%.2f", ($labour_row['INVOICE_LABOR_UNIT'] *  $labour_row['INVOICE_LABOR_RATE']));
    $labour_subtotal = $labour_row['INVOICE_LABOR_SUBTOTAL'];

    $pdf->SetY($y_axis + $y_axis_initial + $row_height);
    $pdf->SetX($distx);
    $pdf->Cell(15, 6, $labour_code, 1, 0, 'L', 0);
    $pdf->Cell(140, 6, $labour_name, 1, 0, 'L', 0);
    $pdf->Cell(20, 6, $currency_sym.$labour_price, 1, 0, 'R', 0);
    $pdf->Cell(20, 6, $currency_sym.$labour_subtotal, 1, 0, 'R', 1);

    //Go to next row
    $y_axis = $y_axis + $row_height;
    $i = $i + 1;
}

// Adds Labour Sub Total
    $pdf->SetY($y_axis + $y_axis_initial + $row_height);
    $pdf->SetX($distx);
    $pdf->Cell(155, 6, '', 0, 0, 'R', 0);
    $pdf->Cell(20, 6, $langvals['invoice_prn_labour_total'], 1, 0, 'R', 0);
    $pdf->Cell(20, 6, $currency_sym.$labour_sub_total_sum, 1, 0, 'R', 1);
    $y_axis = $y_axis + $row_height;
    $i = $i + 1;

// This puts a space inbetween the tables
    $pdf->SetY($y_axis + $y_axis_initial + $row_height);
    $pdf->SetX($distx);
    
    $y_axis = $y_axis + $row_height/2;
    $i = $i + 1;

// This creates Parts Headings
    $pdf->SetY($y_axis + $y_axis_initial + $row_height);
    $pdf->SetX($distx);
    $pdf->Cell(15, 6, $langvals['invoice_prn_qty'], 1, 0, 'L', 1);
    $pdf->Cell(140, 6, $langvals['invoice_prn_parts_items'], 1, 0, 'L', 1);
    $pdf->Cell(20, 6, $langvals['invoice_prn_unit_price'], 1, 0, 'R', 1);
    $pdf->Cell(20, 6, $langvals['invoice_prn_subtotal'], 1, 0, 'R', 1);

    $y_axis = $y_axis + $row_height;
    $i = $i + 1;

 // Display parts on invoice
while($parts_row = mysql_fetch_array($parts_row_pdf))
{
    $parts_code = $parts_row['INVOICE_PARTS_COUNT'];
    $parts_price = sprintf( "%.2f", $parts_row['INVOICE_PARTS_AMOUNT']);
    $parts_name = $parts_row['INVOICE_PARTS_DESCRIPTION'];
    //$parts_subtotal = sprintf( "%.2f", ($parts_row['INVOICE_PARTS_COUNT'] *  $parts_row['INVOICE_PARTS_AMOUNT']));
    $parts_subtotal = $parts_row['INVOICE_PARTS_SUBTOTAL'];

    $pdf->SetY($y_axis + $y_axis_initial + $row_height);
    $pdf->SetX($distx);
    $pdf->Cell(15, 6, $parts_code, 1, 0, 'L', 0);
    $pdf->Cell(140, 6, $parts_name, 1, 0, 'L', 0);
    $pdf->Cell(20, 6, $currency_sym.$parts_price, 1, 0, 'R', 0);
    $pdf->Cell(20, 6, $currency_sym.$parts_subtotal, 1, 0, 'R', 1);

    //Go to next row
    $y_axis = $y_axis + $row_height;
    $i = $i + 1;
}

// Adds Parts Sub Total    
    $pdf->SetY($y_axis + $y_axis_initial + $row_height);
    $pdf->SetX($distx);
    $pdf->Cell(155, 6, '', 0, 0, 'R', 0);
    $pdf->Cell(20, 6, $langvals['invoice_prn_parts_total'], 1, 0, 'R', 0);
    $pdf->Cell(20, 6, $currency_sym.$parts_sub_total_sum, 1, 0, 'R', 1);
    $i = $i + 1;

//Add Totals Box
        $pdf->SetY($y_axis_initial +($row_height * $max + 1));
	//$pdf->SetY($y_axis_initial +($row_height * count($i))+ ($row_height * 2));
	$pdf->SetX(160);
	$pdf->MultiCell(20, 6, $langvals['invoice_prn_subtotal']."\n" .
  							$langvals['invoice_prn_discount']."\n" .
                                                        $langvals['invoice_prn_shipping']."\n" .
							$langvals['invoice_prn_tax']."\n" .
							$langvals['invoice_prn_invoice_total']."\n" .
							$langvals['invoice_prn_paid']."\n" .
							$langvals['invoice_prn_balance']."\n"
							, 1, 0, 'R', 0);
	$pdf->SetY($y_axis_initial +($row_height * $max + 1));
	//$pdf->SetY($y_axis_initial +($row_height * count($i)) + ($row_height * 2));
        $pdf->SetX(180);
	$pdf->MultiCell(20, 6, "$currency_sym $totalinv\n" .
 							"$currency_sym $discinv\n" .
							"$currency_sym $shipinv\n" .
       							"$currency_sym $taxinv\n" .
							"$currency_sym $amntinv\n" .
							"$currency_sym $paidamntinv\n" .
							"$currency_sym $balinv\n"
							, 1, 0, 'R', 2);

// This section not fully translated

//Payment Instructions
 $pdf->SetY($y_axis_initial +($row_height * $max + 1));
 $pdf->SetX(5);
 $pdf->SetFont('Arial', 'B', 8);
 $pdf->Cell(100,3,$langvals['invoice_prn_accepted_payments'],0,'C', FALSE);
  //If Cheques are your payment option
 if($CHECK_PAYABLE <> "" ){
 $pdf->SetY($y_axis_initial +($row_height * $max + 2));
 $pdf->SetX(20);
 $pdf->SetFont('Arial', 'B', 6);
 $pdf->MultiCell(100, 3, "\n" .
                        $pdf->Image('images/icons/cheque.jpeg',10,194,0,5,JPG) .
                        "Cheque\Money Orders:-\n" .
                        "  -Please make payable to $CHECK_PAYABLE\n" .
                        "\n" , 0 ,'L', FALSE);
 }

 //If Direct Deposit is your payment option
 if($DD_NAME <> ""){
                        $pdf->SetY($y_axis_initial +($row_height * $max + 12));
                        $pdf->SetX(20);
                         $pdf->SetFont('Arial', 'B', 6);
                        $pdf->MultiCell(100, 3, "\n" .
                        $pdf->Image('images/icons/deposit.jpeg',3,205,0,5,JPG) .
                        "Direct Deposit:-\n" .
                        "- Bank: $DD_BANK\n" .
                        "- Name: $DD_NAME\n" .
                        "- Branch/BSB: $DD_BSB\n" .
                        "- Account: $DD_ACC\n" .
                        "$DD_INS\n" .
                        "\n", 0 ,'L', FALSE);
 }

 //If PayPal is your payment option
if($PP_ID <> "" ){
                        $pdf->SetY($y_axis_initial +($row_height * $max + 35));
                        $pdf->SetX(20);
                         $pdf->SetFont('Arial', 'B', 6);
                        $pdf->MultiCell(100, 3, "\n" .
                            "\n" .
                        "PayPal Credit Card Processing:-", 0 ,'L', FALSE);

$pdf->SetLink($link);
$pdf->Image('images/paypal/pay_now.gif',5,230,15,0,'','https://www.paypal.com/cmd=_xclick&business='.$PP_ID.'&item_name=Payment%20for%20invoice%20'.$invoice_id.'&item_number='.$invoice_id.'&description=Invoice%20for%20'.$invoice_id.'&amount='.$pamount.'&no_note=Thankyou%20for%20your%20buisness.&currency_code='.$currency_code.'&lc='.$country.'&bn=PP-BuyNowBF');
$pdf->SetLeftMargin(20);
//$pdf->SetFontSize(14);
$pdf->WriteHTML($html);
}
if($PAYMATE_LOGIN <> "" ){
                        $pdf->SetY($y_axis_initial +($row_height * $max + 45));
                        $pdf->SetX(20);
                         $pdf->SetFont('Arial', 'B', 6);
                        $pdf->MultiCell(100, 3, "\n" .
                            "\n" .
                        "Paymate Processing:-", 0 ,'L', FALSE);

$pdf->SetLink($link);
$pdf->Image('images/paymate/paymate_cc.gif',5,242,15,0,'','https://www.paymate.com/PayMate/ExpressPayment?mid='.$PAYMATE_LOGIN.'&amt='.$paymate_amt.'&ref=Payment%20for%20invoice%20'.$invoice_id.'&currency='.$currency_code.'&amt_editable=N&pmt_sender_email='.$cusemail.'&pmt_contact_firstname='.$cusnamef.'&pmt_contact_surname='.$cusnamel.'&pmt_contact_phone='.$cusphone.'&regindi_state='.$cusstate.'&regindi_address1='.$cusaddress.'&regindi_sub='.$cuscity.'&regindi_pcode='.$cuszip.'');
$pdf->SetLeftMargin(20);
//$pdf->SetFontSize(14);
$pdf->WriteHTML($html2);
}
 if($PP_ID == "" & $CHECK_PAYABLE == "" & $DD_NAME == "" & $PAYMATE_LOGIN == ""){
 $pdf->SetY($y_axis_initial +($row_height * $max + 6));
 $pdf->SetX(20);
 $pdf->SetFont('Arial', 'B', 6);
 $pdf->MultiCell(100, 3, $langvals['invoice_prn_discuss_payments']."\n" , 0 ,'L', FALSE);
 }

//$pdf->addremark($cthankyou);
$pdf->SetY($y_axis_initial +($row_height * $max + 60));
 $pdf->SetX(10);
 $pdf->SetFont('Arial', 'BI', 6);
 $pdf->MultiCell(0, 4, $cthankyou , 0 ,'J', FALSE);
//$pdf->Output("cache/INV#".$invoice[INVOICE_ID].".pdf", 'F' );
$pdf->Output("INV#".$invoice['INVOICE_ID'].".pdf",'I');
//TODO - Get pdf file uploaded into database for storage
//$fname = "cache/INV#".$invoice[INVOICE_ID].".pdf" ;
//$fname2 = "INV#".$invoice[INVOICE_ID].".pdf";
//$data = file_get_contents($_FILE[$fname]);
 //$data = mysql_real_escape_string($data);
 // Preparing data to be used in MySQL query
//$q = "INSERT INTO ".PRFX."table_invoice SET PDF_TYPE=INV WHERE INVOICE_ID=2" ;
//$rs = $db->execute($q);
 //mysql_query("INSERT INTO {$table} SET type=pdf, name='$title', size=22, content='$data'");
//
//                $msg = 'Success: image uploaded';
//
//        }

mysql_close($link);

?>