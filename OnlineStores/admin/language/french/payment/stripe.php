<?php
// = 
// Stripe Payment Gateway v230.4
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
// = 

$version  = 'v230.4'; 

//------------------------------------------------------------------------------
// Heading
//------------------------------------------------------------------------------
$_['heading_title']						 = 'Gateway de paiement à rayures';
$_['text_stripe']						= '<a target="blank" href="https://stripe.com"><img src="https://stripe.com/img/logo.png" alt="Stripe" title="Stripe" /></a>';
$_['text_payment']                       = 'Paiement'; 
$_['text_success']                       = "Succès Vous avez des paramètres modifiés!"; 

$_['settings']  = 'Réglages'; 
$_['switch_text_enabled']  = 'Activé'; 
$_['switch_text_disabled']  = 'Désactivé';

//------------------------------------------------------------------------------
// Extension Settings
//------------------------------------------------------------------------------
$_['tab_extension_settings']			 = 'Réglages'; 
$_['heading_extension_settings']		 = 'Réglages'; 

$_['entry_status']						 = 'Statut'; 
$_['entry_sort_order']					 = 'Ordre de tri'; 
$_['entry_title']						 = 'Titre'; 
$_['entry_button_text']					 = 'Texte du bouton'; 
$_['entry_button_class']				 = 'Classe de boutons'; 
$_['entry_button_styling']				 = 'Button Styling'; 

// Payment Page Text
$_['heading_payment_page_text']			 = 'Texte de la page de paiement'; 

$_['entry_text_card_details']			 = 'Carte Détails'; 
$_['entry_text_use_your_stored_card']	 = 'Utilisez votre carte stockée'; 
$_['entry_text_ending_in']				 = 'se terminant en'; 
$_['entry_text_use_a_new_card']			 = 'Utiliser une nouvelle carte'; 
$_['entry_text_card_name']				 = 'Nom sur la carte'; 
$_['entry_text_card_number']			 = 'Numéro de carte'; 
$_['entry_text_card_type']				 = 'Type de carte'; 
$_['entry_text_card_expiry']			 = 'Card expiration (mm / yy)'; 
$_['entry_text_card_security']			 = 'Code de sécurité de la carte (CVC)'; 
$_['entry_text_store_card']				 = "Carte de magasin pour une utilisation future"; 
$_['entry_text_please_wait']			 = 'S\'il vous plaît, attendez'; 
$_['entry_text_to_be_charged']			 = 'Être chargé plus tard:'; 

// Errors
$_['heading_errors']					 = 'Les erreurs'; 

$_['entry_error_customer_required']		 = 'Client requis:'; 
$_['entry_error_shipping_required']		 = 'Expédition requise:'; 
$_['entry_error_shipping_mismatch']		 = 'Mismatch de l\'expédition:'; 

// Stripe Error Codes
$_['heading_stripe_error_codes']		 = 'Codes d\'erreur à rayures'; 
$_['help_stripe_error_codes']			 = 'Laissez tous ces champs en blanc pour afficher le message d\'erreur par défaut de Stripe \'S pour ce code d\'erreur. HTML est pris en charge. Les messages d\'erreur ne sont pas affichés lors de l\'utilisation de la checke à rayures. '; 

$_['entry_error_card_declined']			 = 'carte refusée'; 
$_['entry_error_expired_card']			 = 'Carte expirée'; 
$_['entry_error_incorrect_cvc']			 = 'incorrect_cvc:'; 
$_['entry_error_incorrect_number']		 = "incorrect_number"; 
$_['entry_error_incorrect_zip']			 = 'Zip incorrect:'; 
$_['entry_error_invalid_cvc']			 = 'invalid_cvc'; 
$_['entry_error_invalid_expiry_month']	 = 'mois d\'expiration invalide'; 
$_['entry_error_invalid_expiry_year']	 = 'Année d\'expiration invalide'; 
$_['entry_error_invalid_number']		 = 'Numéro invalide'; 
$_['entry_error_missing']				 = 'disparu: '; 
$_['entry_error_processing_error']		 = 'Traitement_Error'; 

// Cards Page Text
$_['heading_cards_page_text']			 = 'Cartes de la page texte'; 

$_['entry_cards_page_link']				 = 'Cartes de page Lien:'; 
$_['entry_cards_page_heading']			 = 'Cartes Page de la page:'; 
$_['entry_cards_page_none']				 = "Pas de message de cartes:"; 
$_['entry_cards_page_default_card']		 = 'Texte de la carte par défaut:'; 
$_['entry_cards_page_make_default']		 = 'Faire un bouton par défaut'; 
$_['entry_cards_page_delete']			 = 'Supprimer le bouton'; 
$_['entry_cards_page_confirm']			 = 'Supprimer la confirmation'; 
$_['entry_cards_page_add_card']			 = "Ajouter un nouveau bouton de la carte"; 
$_['entry_cards_page_card_address']		 = 'Adresse de la carte:'; 
$_['entry_cards_page_success']			 = "Message de réussite"; 

// Subscriptions Page Text
$_['heading_subscriptions_page_text']	 = 'Subscriptions Texte de la page'; 

$_['entry_subscriptions_page_heading']	 = "Abonnements page en rubrique:"; 
$_['entry_subscriptions_page_message']	 = 'Message de la carte par défaut:'; 
$_['entry_subscriptions_page_none']		 = 'Aucun message d\'abonnement:'; 
$_['entry_subscriptions_page_trial']	 = "Texte de fin d\'essai:"; 
$_['entry_subscriptions_page_last']		 = 'Dernier texte de charge:'; 
$_['entry_subscriptions_page_next']		 = 'Texte de chargement suivant:'; 
$_['entry_subscriptions_page_charge']	 = 'Texte supplémentaire supplémentaire:'; 
$_['entry_subscriptions_page_cancel']	 = 'Bouton Annuler'; 
$_['entry_subscriptions_page_confirm']	 = 'Annuler la confirmation:'; 

//------------------------------------------------------------------------------
// Order Statuses
//------------------------------------------------------------------------------
$_['tab_order_statuses']				 = 'Ordre d\'ordre'; 
$_['heading_order_statuses']			 = 'Ordre d\'ordre'; 
$_['help_order_statuses']				 = 'Choisissez l\'état de la commande défini lorsqu\'un paiement répond à chaque condition. Remarque: Pour réellement <forts> refuser les paiements </ forts> qui échouent CVC ou des contrôles ZIP, vous devez activer le paramètre approprié dans votre panneau d\'administration Stripe. <br /> Vous pouvez rembourser un paiement en utilisant le lien fourni dans l\'historique. onglet pour la commande. '; 

$_['entry_success_status_id']			 = 'Paiement réussi (capturé)'; 
$_['entry_authorize_status_id']			 = 'Paiement réussi (autorisé)'; 
$_['entry_error_status_id']				 = 'Erreur d\'achèvement des commandes:'; 
$_['entry_street_status_id']			 = 'Échec du chèque de rue'; 
$_['entry_zip_status_id']				 = 'Échec de contrôle zip'; 
$_['entry_cvc_status_id']				 = 'CVC Vérifiez l\'échec'; 
$_['entry_refund_status_id']			 = 'Paiement entièrement remboursé'; 
$_['entry_partial_status_id']			 = 'Paiement partiellement remboursé'; 

$_['text_ignore']						 = '--- Ignorer ---'; 

//------------------------------------------------------------------------------
// Restrictions
//------------------------------------------------------------------------------
$_['tab_restrictions']					 = 'Restrictions'; 
$_['heading_restrictions']				 = 'Restrictions'; 
$_['help_restrictions']					 = 'Définissez le total requis sur le panier et sélectionnez les magasins éligibles, les zones géographiques et les groupes de clients pour ce mode de paiement.'; 

$_['entry_min_total']					 = 'Total minimum:'; 
$_['entry_max_total']					 = 'Total maximum:'; 

$_['entry_stores']						 = 'Magasins): '; 

$_['entry_geo_zones']					 = 'Zone géo (s):'; 
$_['text_everywhere_else']				 = '<em> partout ailleurs </ em>'; 

$_['entry_customer_groups']				 = 'Groupe de clients: '; 
$_['text_guests']						 = '<em> invités </ em>'; 

// Currency Settings
$_['heading_currency_settings']			 = 'Paramètres de devise'; 
$_['help_currency_settings']			= 'Select the currencies that Stripe will charge in, based on the order currency. <a target="_blank" href="https://support.stripe.com/questions/which-currencies-does-stripe-support">See which currencies your country supports</a>';
$_['entry_currencies']					 = 'Quand les commandes sont dans [monnaie], chargez-la'; 
$_['text_currency_disabled']			 = '--- Désactivée ---'; 

//------------------------------------------------------------------------------
// Stripe Settings
//------------------------------------------------------------------------------
$_['tab_stripe_settings']				 = "Réglages de rayures"; 
$_['help_stripe_settings']				 = 'Les touches API peuvent être trouvées dans votre panneau d\'administration Stripe sous votre compte> Paramètres du compte> Touches API '; 

// API Keys
$_['heading_api_keys']					 = 'API KEYS'; 

$_['entry_test_secret_key']				 = 'Test Secret Key'; 
$_['entry_test_publishable_key']		 = 'Testez la clé publiable'; 
$_['entry_live_secret_key']				 = "Clé secrète en direct"; 
$_['entry_live_publishable_key']		 = "Clé publiquement en direct"; 

// Stripe Settings
$_['heading_stripe_settings']			 = "Réglages de rayures"; 

$_['entry_webhook_url']					 = "URL webhook:"; 

$_['entry_transaction_mode']			 = 'Mode de transaction:'; 
$_['text_test']							 = 'Test'; 
$_['text_live']							 = 'Habitent'; 

$_['entry_charge_mode']					 = 'Mode de charge:'; 
$_['text_authorize']					 = 'Autoriser'; 
$_['text_capture']						 = 'Capturer'; 
$_['text_fraud_authorize']				 = 'Autoriser s\'il est peut-être frauduleux, capturer autrement'; 

$_['entry_transaction_description']		 = 'Description de la transaction: '; 

$_['entry_send_customer_data']			 = 'Envoyer des données client:'; 
$_['text_never']						 = 'Jamais'; 
$_['text_customers_choice']				 = 'Client & Apos; s choix'; 
$_['text_always']						 = 'Toujours'; 

$_['entry_allow_stored_cards']			 = "Permettre aux clients d\'utiliser des cartes stockées:"; 

// Apple Pay Settings
$_['heading_apple_pay_settings']		 = 'Apple Pay paramètres'; 

$_['entry_applepay']					 = 'Activer Apple Pay:'; 
$_['entry_applepay_label']				 = 'Étiquette de la feuille de paiement: '; 
$_['entry_applepay_billing']			 = "Exiger une adresse de facturation:"; 

//------------------------------------------------------------------------------
// Stripe Checkout
//------------------------------------------------------------------------------
$_['tab_stripe_checkout']				 = "Checkout à rayures"; 
$_['heading_stripe_checkout']			 = "Checkout à rayures"; 
$_['help_stripe_checkout']				= 'Stripe Checkout uses Stripe&apos;s pop-up for displaying the credit card inputs, validation, and error handling. You can read more about it and view a demo at <a target="_blank" href="https://stripe.com/docs/checkout">https://stripe.com/docs/checkout</a><br />Note: Stripe Checkout does <strong>not</strong> allow customers to use the billing address entered in Expandcart.';

$_['entry_use_checkout']				 = 'Utilisez la pop-up de la caisse de rayures:'; 
$_['text_yes_for_desktop_devices']		 = 'Oui, pour les appareils de bureau seulement'; 

$_['entry_checkout_remember_me']		 = 'Activer l\'option "mémoriser moi":'; 

$_['entry_checkout_alipay']				 = 'Activer Alipay:'; 
$_['entry_checkout_bitcoin']			 = 'Activer Bitcoin:'; 

$_['entry_checkout_billing']			 = "Exiger une adresse de facturation:"; 

$_['entry_checkout_shipping']			 = 'Exiger une adresse de livraison:'; 

$_['entry_checkout_image']				 = "Logo pop-up:"; 
$_['text_browse']						 = 'Parcourir'; 
$_['text_clear']						 = 'Dégager'; 
$_['text_image_manager']				 = "Gestionnaire d\'images"; 

$_['entry_checkout_title']		 		 = 'Titre pop-up:'; 

$_['entry_checkout_description']		 = 'Description pop-up:'; 

$_['entry_checkout_button']				 = "Texte du bouton contextuel:"; 

$_['entry_quick_checkout']				 = "Checkout rapide"; 

//------------------------------------------------------------------------------
// Subscription Products
//------------------------------------------------------------------------------
$_['tab_subscription_products']			 = 'Produits d\'abonnement'; 
$_['help_subscription_products']		 = '&taureau; Les produits d\'abonnement vous abonneront au client au plan de rayures associé lorsqu\'ils sont achetés. Vous pouvez associer un produit avec un plan en entrant dans l\'ID de plan de rayures dans le champ "Emplacement" du produit. <br /> & Bull; Si l\'abonnement n\'est pas défini pour être chargé immédiatement (c\'est-à-dire qu\'il a une période d\'essai), le montant de l\'abonnement sera retiré de leur commande d\'origine et une nouvelle commande sera créée lorsque l\'abonnement est effectivement chargé de leur carte. < BR /> & Bull; n\'importe quel temps Stripe charge la souscription à l\'avenir, une commande correspondante sera créée dans Develcart. <br /> & Bull; Si vous avez un coupon configuré dans votre compte Stripe, vous pouvez modifier un coupon expandcart en utilisant le même code de coupon et le même montant de réduction. Lorsqu\'un client achète un produit d\'abonnement et utilise ce code de coupon, il passera le code à la bande pour ajuster correctement les frais d\'abonnement. '; 

$_['heading_subscription_products']		 = "Paramètres du produit d\'abonnement"; 

$_['entry_subscriptions']				 = "Activer les produits d\'abonnement"; 
$_['entry_prevent_guests']				 = 'Empêcher les invités d\'acheter:'; 
$_['entry_include_shipping']			 = 'Inclure l\'expédition:'; 
$_['entry_allow_customers_to_cancel']	 = "Permettre aux clients d\'annuler des abonnements:"; 

// Current Subscription Products
$_['heading_current_subscriptions']		 = 'Produits d\'abonnement actuels'; 
$_['entry_current_subscriptions']		 = 'Produits d\'abonnement actuels:'; 

$_['text_thead_Expandcart']				 = 'Expandcart'; 
$_['text_thead_stripe']					 = 'Bande'; 
$_['text_product_name']					 = "Nom du produit"; 
$_['text_product_price']				 = 'Prix du produit'; 
$_['text_location_plan_id']				 = "Emplacement / ID de plan"; 
$_['text_plan_name']					 = "Nom du plan"; 
$_['text_plan_interval']				 = "Intervalle de plan"; 
$_['text_plan_charge']					 = 'Frais de plan'; 
$_['text_no_subscription_products']		 = 'Aucun produit d\'abonnement'; 
$_['text_create_one_by_entering']		 = 'Créez-en une en entrant dans l\'ID de plan de rayures dans le champ\' Emplacement \'du produit'; 

// Map Options to Subscriptions
$_['heading_map_options']				 = 'Options de carte aux abonnements'; 
$_['help_map_options']					 = 'Si le client dispose d\'un produit avec le nom d\'option approprié et la valeur d\'option dans leur panier, ils seront abonnés à l\'ID de plan correspondant. Cela remplacera l\'identifiant du plan dans le champ de localisation de ce produit. '; 

$_['column_action']						 = 'Action'; 
$_['column_option_name']				 = "Nom d\'option"; 
$_['column_option_value']				 = 'Valeur d\'option'; 
$_['column_plan_id']					 = 'ID de plan'; 

$_['button_add_mapping']				 = "Ajouter la cartographie"; 

// Map Recurring Profiles to Subscriptions
$_['heading_map_recurring_profiles']	 = "Carte des profils récurrents aux abonnements"; 
$_['help_map_recurring_profiles']		 = 'Si le client dispose d\'un produit avec le nom de profil récurrent approprié dans son panier, ils seront abonnés à l\'ID de plan correspondant. Cela remplacera l\'ID de plan dans le champ Emplacement pour ce produit. La fréquence d\'abonnement et la quantité de charge sont déterminées par le plan de rayures, pas les paramètres de profil récurrent, assurez-vous qu\'ils correspondent exactement. '; 

$_['column_profile_name']				 = "Nom de profil récurrent"; 

//------------------------------------------------------------------------------
// Create a Charge
//------------------------------------------------------------------------------
$_['tab_create_a_charge']				 = 'Créer une charge'; 

$_['help_charge_info']					 = 'Entrez les informations de charge ci-dessous, puis choisissez de générer un lien de paiement, chargez une carte de client ou entrez manuellement une carte.'; 
$_['heading_charge_info']				 = 'Info de charge'; 

$_['entry_order_id']					 = 'Numéro de commande: '; 
$_['entry_order_status']				 = "Changement d\'état de la commande:"; 
$_['entry_description']					 = 'Description: '; 
$_['entry_statement_descriptor']		 = 'Descripteur de déclaration:'; 
$_['entry_amount']						 = 'Montant';

// Create Payment Link
$_['heading_create_payment_link']		 = 'Créer un lien de paiement'; 

$_['help_create_payment_link']			 = ''; 
$_['button_create_payment_link']		 = 'Créer un lien de paiement'; 

// Use a Stored Card
$_['heading_use_a_stored_card']			 = 'Utilisez une carte stockée'; 

$_['entry_customer']					 = 'Client';
$_['placeholder_customer']				 = 'Commencez à taper le nom d\'un client ou l\'adresse électronique'; 
$_['text_customers_stored_cards_will']	 = '(La carte par défaut du client apparaîtra ici)'; 
$_['button_create_charge']				 = 'Créer des frais'; 

// Use a New Card
$_['heading_use_a_new_card']			 = 'Utiliser une nouvelle carte'; 

//------------------------------------------------------------------------------
// Standard Text
//------------------------------------------------------------------------------
$_['copyright']							 = ''; 

$_['standard_autosaving_enabled']		 = 'Économie automatique activée'; 
$_['standard_confirm']					 = 'Cette opération ne peut pas être annulée. Continuez?'; 
$_['standard_error']					= '<strong>Error:</strong> You do not have permission to modify ' . $_['heading_title'] . '!';
$_['standard_max_input_vars']			 = '<STRUT> AVERTISSEMENT: </ strong> Veuillez contacter le service clientèle 2.'; 
$_['standard_please_wait']				 = 'S\'il vous plaît, attendez...'; 
$_['standard_saved']					 = 'Enregistré!';
$_['standard_saving']					 = 'Économie...'; 
$_['standard_select']					 = '--- Sélectionnez ---'; 
$_['standard_success']					 = 'Succès!'; 
$_['standard_testing_mode']				 = 'Votre journal est trop grand pour ouvrir! Effacer en premier, puis exécutez votre test. '; 
$_['standard_vqmod']					 = '<STRUT> AVERTISSEMENT: </ FORT> Veuillez contacter le service clientèle.'; 

$_['standard_module']					 = 'Applications'; 
$_['standard_shipping']					 = 'Expédition'; 
$_['standard_payment']					 = 'Paiements'; 
$_['standard_total']					 = 'Commander total'; 
$_['standard_feed']						 = 'Flux'; 

// Errors

$_['error_settings']                     = "AVERTISSEMENT: veuillez remplir la touche Publishable Test, Test Secret Key, Live Publishable Key et Live Secret Clips Fields !!"; 
?>