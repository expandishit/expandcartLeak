<?php

$_['heading_title']              = 'Rajhi Bank'; 
$_['settings']                   = 'Réglages'; 
$_['switch_text_enabled']        = 'Activé'; 
$_['switch_text_disabled']       = 'Désactivé';
$_['rajhi_bank_contact']         = 'Contactez la banque Rajhi'; 


// --------------- TEXT ---------------
$_['text_payment']         = 'Paiement'; 
$_['text_success']         = 'Succès: vous avez modifié le module de paiement RAJHI_BANK! '; 
$_['text_changes']         = "Il y a des changements non gravés."; 
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


// --------------- ENTRY ---------------
$_['entry_merchant_code']            = 'Code marchand'; 
$_['entry_merchant_hash_key']       = "Clé de hachage marchand"; 
$_['entry_iframe_id']           = 'IDame ID'; 
$_['entry_api_server']         = 'Serveur API'; 
$_['help_api_server']          = 'Utilisez le'. $_['"Text_live']. ' ou '. $_[' text_test '].' serveur pour traiter les transactions '; 
$_['entry_risk_speed']         = "Risque / vitesse"; 
$_['help_risk_speed']          = "<Strong> High </ Strard> Les confirmations de vitesse prennent généralement 5-10 secondes et peuvent être utilisées pour des produits numériques ou des articles à faible risque <br> <strong> Les confirmations de vitesse faible </ forte> prennent environ 1 heure et devraient être utilisé pour des articles de grande valeur"; 
$_['entry_geo_zone']           = 'Geo Zone'; 
$_['entry_status']             = 'Statut'; 
$_['entry_sort_order']         = 'Ordre de tri'; 
$_['entry_new_status']         = 'Nouveau';
$_['help_new_status']          = 'Une facture nouvelle ou partiellement payée en attendant le paiement intégral'; 
$_['entry_paid_status']        = 'Payé'; 
$_['help_paid_status']         = 'Une facture entièrement payée en attente de confirmation'; 
$_['entry_confirmed_status']   = 'Confirmé'; 
$_['help_confirmed_status']    = "Une facture confirmée par réglage de risque / vitesse"; 
$_['entry_complete_status']    = "Statut de commande complète"; 
$_['entry_failed_status']       = "Statut de commande échoué"; 
$_['entry_refund_status']       = 'Statut de commande de remboursement'; 
$_['help_complete_status']     = 'Une facture confirmée qui a été créditée sur le compte marchand & # 8217; S'; 
$_['entry_expired_status']     = 'Expiré'; 
$_['help_expired_status']      = 'Une facture où le paiement intégral n\'a pas été reçu et la fenêtre de paiement de 15 minutes S\'est écoulée'; 
$_['entry_invalid_status']     = 'Invalide'; 
$_['help_invalid_status']      = 'Une facture qui a été entièrement payée mais non confirmée et # 8217' ;
$_['entry_notify_url']         = 'URL de notification'; 
$_['help_notify_url']          = 'Rajhi_bank & # 8217; S IPN postera des mises à jour de l\'état des factures à cette URL'; 
$_['entry_return_url']         = 'URL de retour'; 
$_['help_return_url']          = 'Rajhi_Bank fournira un lien de redirection à l\'utilisateur pour cette URL lors du paiement réussi de la facture'; 
$_['entry_debug_mode']         = 'Mode débogage'; 
$_['help_debug_mode']          = "Enregistre des informations supplémentaires sur le journal rajhi_bank"; 
$_['entry_default']            = 'Défaut'; 
$_['entry_contact_rajhi_bank']     = "Contact avec rajhi_bank"; 
$_['rajhi_bank_transportal_id']             = "ID de transport"; 
$_['rajhi_bank_transportal_password']     = "Mot de passe de transport"; 
$_['rajhi_bank_resource_key']             = "Clé de ressource"; 
$_['rajhi_bank_gatway_endpoint']          = "Point de terminaison de passerelle"; 
$_['rajhi_bank_support_endpoint']          = "Point d\'extrémité soutenant"; 

// --------------- ERRORS ---------------
$_['error_permission']         = 'Avertissement: vous n\'avez pas la permission de modifier le module de paiement RAJHI_BANK.'; 

$_['error_merchant_code']      = 'Le code marchand est requis (pour les avis de paiement authentifiés)'; 
$_['error_merchant_hash_key']      = 'La clé de hachage de marchand est requise (pour les avis de paiement authentifiés)'; 
$_['error_notify_url']         = "URL de notification est requise"; 
$_['error_return_url']         = 'URL de retour est requis'; 
$_['error_api_key_valid']      = 'La clé de l\'API doit être une clé d\'accès API valide RAJHI_BANK API'; 
$_['error_notify_url_valid']   = 'l\'URL de notification doit être une ressource URL valide'; 
$_['error_return_url_valid']   = 'l\'URL de retour doit être une ressource URL valide'; 
$_['error_transportal_id']             = "ID de transport"; 
$_['error_transportal_password']     = "Mot de passe de transport"; 
$_['error_resource_key']             = "Clé de ressource"; 
$_['error_gatway_endpoint']          = "Point d\'extrémité de la passerelle non prise en charge"; 
$_['error_support_endpoint']          = "Point d\'extrémité de soutien non supporté"; 
