<?php

// Heading
$_['heading_title']  = 'Nbe Bank'; 
$_['settings']  = 'Réglages'; 
$_['switch_text_enabled']  = 'Activé'; 
$_['switch_text_disabled']  = 'Désactivé';
$_['order_statuses']  = 'Ordre d\'ordre'; 

// Text
$_['text_payment']  = 'Paiement'; 
$_['text_success']  = 'Succès: vous avez modifié les détails de compte Smeonline'; 
$_['text_yes']  = 'Oui'; 
$_['text_no']  = 'Non'; 
$_['text_purchase']  = 'Acheter'; 
$_['text_preauth_capture']  = 'Pré-auth / capture'; 
$_['text_capture']  = 'Capturer'; 
$_['text_refund']  = 'rembourser'; 
$_['text_approved']  = 'approuvé.';
$_['text_declined']  = 'diminué.'; 
$_['text_receipt_number']  = 'Numéro de réception'; 

// Entry
$_['entry_api_url']  = "URL API BASE"; 
$_['entry_username']  = 'Nom d\'utilisateur de l\'API'; 
$_['entry_password']  = "Mot de passe API"; 
$_['entry_merchant_number']  = 'Numéro de marchand'; 
$_['entry_merchant_number_note']  = "Votre numéro marchand comme fourni par la banque. '"; 
$_['entry_payment_action']  = "Action de paiement"; 
$_['entry_payment_action_note']  = 'Choisissez entre achat ou pré-auth / capture. Les transactions d\'achat Chargez la carte immédiatement. Les pré-auteurs vérifient si la carte est disponible des fonds et place une prise sur la carte pour le montant nominé. Captures complètes une pré-authentification, chargant la carte '; 
$_['entry_test_mode']  = 'Mode d\'essai'; 
$_['entry_test_mode_note']  = 'Si le mode test est activé, toutes les transactions seront traitées en mode test. Vous ne recevrez pas de fonds '; 
$_['entry_pending_status']  = 'Statut de commande pour en attente'; 
$_['entry_payment_status']  = 'Statut de commande pour le paiement approuvé'; 
$_['entry_preauth_status']  = 'Statut de commande pour la capture en attente'; 
$_['entry_failed_status']  = 'Statut de commande pour le paiement refusé'; 
$_['entry_captured_status']  = 'Statut de commande pour la capture approuvée'; 
$_['entry_refunded_status']  = 'Statut de commande pour le remboursement approuvé'; 
$_['entry_geo_zone']  = 'Geo Zone'; 
$_['entry_total']  = 'Total';
$_['entry_total_note']  = "La commande totale de la commande doit atteindre avant que ce mode de paiement devienne actif."; 
$_['entry_sort_order']  = 'Ordre de tri'; 

//Meza
$_['entry_meeza_active']  = 'METEZA Activation'; 
$_['entry_meeza_settings']  = 'Meeza paramètres'; 
$_['entry_meeza_terminal_id']  = 'ID de terminal'; 
$_['entry_meeza_merchant_id']  = 'Merchant ID'; 
$_['entry_meeza_secret_key']  = 'Clef secrète'; 


// Error
$_['error_permission']  = 'AVERTISSEMENT: Vous n\'avez pas la permission de modifier le paiement Smeonline'; 
$_['error_api_url']  = 'URL API de base requis'; 
$_['error_username']  = "Nom d\'utilisateur de l\'API requis"; 
$_['error_password']  = "Mot de passe API requis"; 
$_['error_merchant_number']  = 'Nombre de marchands requis'; 
$_['error_decline_reason']  = "Raison refusive"; 
$_['error_request']  = 'Avertissement: Erreur lors du traitement de votre demande'; 
$_['error_valid_amount']  = 'Avertissement: montant non valide pour le remboursement'; 
$_['error_payment_captured']  = 'Avertissement: le paiement est déjà terminé '; 
$_['error_payment_refund']  = 'Avertissement: le paiement n\'est pas déjà terminé '; 
$_['error_payment_preauth']  = 'Avertissement: la pré-autorisation devrait être complétée pour effectuer cette action '; 
$_['error_transaction_not_found']  = 'Avertissement: une transaction originale n\'a pas été trouvée '; 
$_['error_maximum_amount_refund']  = 'Avertissement: montant maximum disponible pour remboursement est'; 
$_['error_full_refund']  = 'AVERTISSEMENT: paiement déjà entièrement remboursé'; 
$_['error_curl']  = 'l\'extension CURL PHP  n\'est pas installée. Le module Smeonline est nécessaire pour activer PHP5-CURL sur votre serveur. '; 
