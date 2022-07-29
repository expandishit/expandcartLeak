<?php
/**
 * @license https://github.com/bitpay/opencart-bitpay/blob/master/LICENSE MIT
 */

$_['heading_title']  = 'Bitpay'; 
$_['settings']  = 'Réglages'; 
$_['switch_text_enabled']  = 'Activé'; 
$_['switch_text_disabled']  = 'Désactivé';
// Text
$_['text_payment']         = 'Paiement'; 
$_['text_success']         = 'Succès: vous avez modifié le module de paiement BitPay! '; 
$_['text_changes']         = "Il y a des changements non gravés."; 
$_['text_bitpay']          = '<un onclick ';
$_['text_bitpay_support'] = '<span>For <strong>24/7 support</strong>, please visit our website <a href="https://support.bitpay.com" target="_blank">https://support.bitpay.com</a> or send an email to <a href="mailto:support@bitpay.com" target="_blank">support@bitpay.com</a> for immediate attention</span>';
$_['text_bitpay_apply']    = '<un href ';
$_['text_high']            = 'Haut'; 
$_['text_medium']          = 'Moyen'; 
$_['text_low']             = 'Faible'; 
$_['text_live']            = 'Habitent'; 
$_['text_test']            = 'Test'; 
$_['text_all_geo_zones']   = 'Toutes les zones géographiques'; 
$_['text_settings']        = 'Réglages'; 
$_['text_log']             = 'Enregistrer'; 
$_['text_general']         = 'Général';
$_['text_statuses']        = 'Ordre d\'ordre'; 
$_['text_advanced']        = 'Avancée'; 


// Entry
$_['entry_api_token']          = "Jeton API"; 
$_['entry_api_key']            = 'Clé API'; 
$_['help_api_key']             = '<un href'; 
$_['help_api_key_test']        = '<un href ';
$_['entry_api_server']         = 'Serveur API'; 
$_['help_api_server']          = 'Utilisez le'. $_['"Text_live']. ' ou '. $_[' text_test '].' serveur pour traiter les transactions '; 
$_['entry_risk_speed']         = "Risque / vitesse"; 
$_['help_risk_speed']          = "<Strong> High </ Strard> Les confirmations de vitesse prennent généralement 5-10 secondes et peuvent être utilisées pour des produits numériques ou des articles à faible risque <br> <strong> Les confirmations de vitesse faible </ forte> prennent environ 1 heure et devraient être utilisé pour des articles de grande valeur '"; 
$_['entry_geo_zone']           = 'Geo Zone'; 
$_['entry_status']             = 'Statut'; 
$_['entry_sort_order']         = 'Ordre de tri'; 
$_['entry_new_status']         = 'Nouveau';
$_['help_new_status']          = 'Une facture nouvelle ou partiellement payée en attendant le paiement intégral'; 
$_['entry_paid_status']        = 'Payé'; 
$_['help_paid_status']         = 'Une facture entièrement payée en attente de confirmation'; 
$_['entry_confirmed_status']   = 'Confirmé'; 
$_['help_confirmed_status']    = "Une facture confirmée par réglage de risque / vitesse"; 
$_['entry_complete_status']    = 'Compléter'; 
$_['help_complete_status']     = 'Une facture confirmée qui a été créditée sur le compte marchand & # 8217; S'; 
$_['entry_expired_status']     = 'Expiré'; 
$_['help_expired_status']      = 'Une facture où le paiement intégral n\'a pas été reçu et la fenêtre de paiement de 15 minutes S\'est écoulée'; 
$_['entry_invalid_status']     = 'Invalide'; 
$_['help_invalid_status']      = 'Une facture qui a été entièrement payée mais non confirmée et # 8217';
$_['entry_notify_url']         = 'URL de notification'; 
$_['help_notify_url']          = 'Bitpay & # 8217; S IPN postera les mises à jour de l\'état de la facture à cette URL'; 
$_['entry_return_url']         = 'URL de retour'; 
$_['help_return_url']          = 'Bitpay fournira un lien de redirection à l\'utilisateur pour cette URL lors du paiement réussi de la facture'; 
$_['entry_debug_mode']         = 'Mode débogage'; 
$_['help_debug_mode']          = "Enregistre des informations supplémentaires sur le journal Bitpay"; 
$_['entry_default']            = 'Défaut'; 

// Error
$_['error_permission']         = 'Avertissement: vous n\'avez pas la permission de modifier le module de paiement BitPay.'; 

$_['error_api_key']            = 'La clé API est requise (pour les avis de paiement authentifiés)'; 
$_['error_notify_url']         = "URL de notification est requise"; 
$_['error_return_url']         = 'URL de retour est requis'; 
$_['error_api_key_valid']      = 'La clé de l\'API doit être une clé d\'accès API de BitPay valide'; 
$_['error_notify_url_valid']   = 'l\'URL de notification doit être une ressource URL valide'; 
$_['error_return_url_valid']   = 'l\'URL de retour doit être une ressource URL valide'; 
