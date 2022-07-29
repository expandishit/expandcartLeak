<?php
/**
 * @version		$Id: klarna_invoice.php 3535 2014-01-09 10:26:02Z mic $
 * @package		Translation - Frontend
 * @author		mic - http://osworx.net
 * @copyright	2013 OSWorX - http://osworx.net
 * @license		GPL - www.gnu.org/copyleft/gpl.html
 */

// Text
$_['text_title']		= 'Klarna Rechnungskauf';
$_['text_fee']			= 'Klarna Rechnngskauf - Zahlung binnen 14 Tage - Gebühren <span id="klarna_invoice_toc_link"></span> (+%s)<script text="javascript\">$.getScript(\'http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js\', function(){ var terms = new Klarna.Terms.Invoice({ el: \'klarna_invoice_toc_link\', eid: \'%s\', country: \'%s\', charge: %s});})</script>';
$_['text_no_fee']		= 'Klarna Rechnngskauf - Zahlung binnen 14 Tage - keine Gebühren <span id="klarna_invoice_toc_link"></span><script text="javascript">$.getScript(\'http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js\', function(){ var terms = new Klarna.Terms.Invoice({ el: \'klarna_invoice_toc_link\', eid: \'%s\', country: \'%s\'});})</script>';
$_['text_additional']	= 'Klarna benötigt weitere Infos um mit der Zahlung fortfahren zu können.';
$_['text_wait']			= 'Bitte warten ...';
$_['text_male']			= 'Mann';
$_['text_female']		= 'Frau';
$_['text_year']			= 'Jahr';
$_['text_month']		= 'Monat';
$_['text_day']			= 'Tag';
$_['text_comment']		= "Klarna Rechnungsnr.: %s\n%s/%s: %.4f";

// Entry
$_['entry_gender']		= 'Geschlecht';
$_['entry_pno']			= 'Sozialversicherungsnr.<span class="help">Bitte hier die Sozialversicherungsnummer angeben </span>';
$_['entry_dob']			= 'Geburtsdatum<span class="help">Format: TTMMJJJJ</span>';
$_['entry_phone_no']	= 'Telefonnr.<span class="help">Hier eine erreichbare Telefonnr. angeben</span>';
$_['entry_street']		= 'Straße<span class="help">Lieferung kann nur an eine gültige Adresse erfolgen</span>';
$_['entry_house_no']	= 'Hausnummer';
$_['entry_house_ext']	= 'Türnr.';
$_['entry_company']		= 'Firmennummer<span class="help">Hier Firmenbuchnnummer angeben</span>';
$_['entry_cellno']		= 'Mobiltel.Nr.';

// Error
$_['error_deu_terms']		= 'Den Datenschutzbestimmungen von Klarna muss zugestimmt werden';
$_['error_address_match']	= 'Rechnungs- &amp; Zustelladresse müssen übereinstimmen';
$_['error_network']			= 'Fehler: Server momentan nicht erreichbar - bitte nochmals probieren';

$_['error_gender']     = 'Geschlecht erforderlich!';
$_['error_dob']        = 'Geburtsdatum erforderlich!';
$_['error_house_no']   = 'Hausnummer erforderlich!';
$_['error_house_ext']  = 'Türnummer erforderlich!';
?>