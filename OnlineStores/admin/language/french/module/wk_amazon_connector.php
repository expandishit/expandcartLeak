<?php
// Heading
$_['heading_title']   				     = 'Amazon Connector'; 

// Text
$_['text_apps']  				     = 'Applications'; 
$_['text_success']    				     = 'Succès: vous avez modifié un module de connecteur Amazon! '; 
$_['text_edit']       				     = 'Modifier le module de connecteur Amazon'; 
$_['text_default']    				     = 'Store par défaut'; 
$_['text_option1']    				     = 'Option 1: Importez tous les produits Amazon (avec ou sans variation)'; 
$_['text_option2']    				     = 'Option 2: Importer uniquement les produits Amazon qui n\'ont aucune variation.'; 

// Entry Amazon
$_['entry_status']     				     = 'Statut'; 
$_['entry_default_category']	     = 'Choisir la catégorie par défaut'; 
$_['entry_default_quantity']	     = 'Quantité de produit par défaut'; 
$_['entry_default_weight']		     = 'Amazon Produit Poids (en gram)'; 
$_['entry_cron_create_product']    = 'Stockez le produit non mappé sur Amazon (à travers le travail de Cron)'; 
$_['entry_cron_update_product']    = 'Mettre à jour les produits cartographiés (à travers le travail cron)'; 
$_['entry_default_store']			     = 'Stocker par défaut pour la commande Sync'; 
$_['entry_order_status']			     = 'Statut de commande importé Amazon'; 
$_['entry_default_product_store']	 = "Magasin par défaut pour le produit"; 
$_['entry_variation_options']	     = 'Variation de produit (option) Options de choix'; 
$_['entry_update_imported']	       = "Mettre à jour les produits importés"; 
$_['entry_update_exported']	       = 'Mettre à jour les produits exportés'; 
$_['entry_price_rules']	           = 'Appliquer des règles de prix pour'; 
$_['entry_import_quantity_rule']   = 'Appliquer des règles de quantité pour le produit d\'importation'; 
$_['entry_export_quantity_rule']   = 'Appliquer des règles de quantité pour le produit d\'exportation'; 

//panel
$_['panel_general_options']        = 'Options générales'; 
$_['panel_order_options']	         = 'Options de commande'; 
$_['panel_product_options']	       = 'Options de produit'; 
$_['panel_real_time_setting']	     = 'Paramètres de mise à jour en temps réel'; 

// price rules
$_['panel_price_rules']	           = "Paramètres de règles de prix / quantité"; 

//help amazon
$_['help_default_category'] 	 = 'Choisissez la catégorie de magasin par défaut pour attribuer un produit Amazon.'; 
$_['help_default_quantity']		 = 'Quantité donnée sera la quantité de produit par défaut Amazon / Store, si la quantité de produit est nulle.'; 
$_['help_default_weight']			 = 'Cette valeur sera utilisée lorsque le produit Amazon ne contient pas le poids.'; 
$_['help_default_store']			 = 'Sélectionnez Grount Store pour la synchronisation de la commande.'; 
$_['help_order_status']				 = 'Définir le statut de la commande par défaut pour la commande qui importée d\'Amazon'; 
$_['help_default_product_store']	 = "Le magasin sélectionné sera attribué à tous les produits Amazon par défaut"; 
$_['help_variation_options']	 = 'Vous pouvez sélectionner l\'option pour le produit avec / sans variation / option.'; 
$_['info_option']              = 'Option 1: Dans ce cas, un nouveau produit sera toujours créé dans Develcart pour chaque produit Amazon si ce produit a une variation / option ou non. <br> <br> 
Option 2 : In this case, Products will import only those have no variation/option. Products with variation/option will not import. In order import case, if order\'s product has variations(options) then product and order related to
that product both will not import..';
$_['entry_update_imported']	   = 'Mettre à jour le produit importé sur Amazon Store!'; 
$_['entry_update_exported']	   = 'Mettre à jour le produit exporté sur Amazon Store!'; 
$_['help_update_imported']	   = 'Mettre à jour le produit importé sur Amazon Store, si nous effectuons une mise à jour sur un magasin Expandcart.'; 
$_['help_update_exported']	   = 'Mettre à jour le produit exporté sur Amazon Store, si nous effectuons une mise à jour sur un magasin Expandcart.'; 
$_['help_cron_create_product'] = 'Avec l\'aide de cette option, vous pouvez exporter les produits OpenCart nouvellement ajoutés sur le premier compte vendeur Amazon.'; 
$_['help_cron_update_product'] = 'Si activé, vous pouvez alors mettre à jour les champs de prix et de quantité de produit en fonction de leur source de synchronisation.'; 
$_['help_price_rules']        = 'Si l\'exportation est sélectionnée, la règle de prix sera appliquée sur les produits exportés, sinon la règle de prix sera appliquée aux produits importés.'; 

//placeholder
$_['placeholder_quantity']	   = 'Entrez la quantité de produit par défaut ..'; 
$_['placeholder_weight']			 = 'Entrez le poids de produit par défaut (en gram) ..'; 
$_['text_import']			 = 'Importer'; 
$_['text_export']			 = 'Exportation'; 

$_['info_update_imported']     = 'Remarque: Si le produit importé sera mis à jour sur Stocker, seule la quantité et le prix de ce produit seront automatiquement mis à jour sur Amazon Store'; 
$_['info_update_exported']     = 'Remarque: Si le produit exporté doit mettre à jour sur Stocker, la quantité et le prix de ce produit ne se mettront à jour automatiquement sur Amazon Store'; 
$_['info_price_rules']         = 'Remarque: si l\'exportation est sélectionnée, la règle de prix s\'appliquera aux produits exportés et si l\'importation est sélectionnée, la règle de prix sera applicable aux produits importés '; 
$_['info_import_quantity_rule']         = 'Remarque: si Activer, la règle de quantité s\'appliquera aux produits importés.'; 
$_['info_export_quantity_rule']         = 'Remarque: Si Activer, la règle de quantité est applicable aux produits exportés.'; 
// Error
$_['error_permission']  = 'Avertissement: vous n\'avez pas la permission de modifier le module de connecteur Amazon!'; 
$_['error_quantity']  = 'Avertissement: la quantité de produit par défaut doit être un nombre positif.'; 
$_['error_weight']  = 'AVERTISSEMENT: Le poids du produit Amazon doit être un nombre positif.'; 
