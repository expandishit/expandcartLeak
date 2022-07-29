<?php
// Heading
$_['heading_title']        = 'Option de produit Images'; 
$_['poip_module_name']     = 'Option de produit Images'; 

// Text
$_['text_module']          = 'Applications'; 
$_['text_success']         = "Succès:" . $_["Heading_title"]. " Les paramètres ont changé! "; 
$_['text_content_top']     = 'Top du contenu'; 
$_['text_content_bottom']  = 'Fond de contenu'; 
$_['text_column_left']     = 'Colonne à gauche'; 
$_['text_column_right']    = 'Colonne droite'; 

// Entry
$_['entry_settings']                   = "Paramètres de module"; 
$_['entry_import']                     = 'Importer'; 
$_['entry_import_description']         = '<B> AVIS: Toutes les options de produit Les images seront automatiquement supprimées avant la démarrage d\'importation. </ B> 
<br><br>Import file format: XLS. Import uses only first sheet for getting data.
<br>First table row must contain fields names (head): product_id, option_value_id, image (not product_option_id)
<br>Next table rows must contain related options data in accordance with fields names in first table row.';
$_['PHPExcelNotFound']                 = '<un href ';
$_['button_upload']		                 = 'Importer le fichier'; 
$_['button_upload_help']               = 'Importation commence immédiatement après avoir sélectionné le fichier'; 
$_['entry_server_response']            = 'Réponse du serveur'; 
$_['entry_import_result']              = 'Lignes transformées / images / ignorées'; 

$_['entry_layout']         = 'Disposition'; 
$_['entry_position']       = 'Positionner'; 
$_['entry_status']         = 'Statut'; 
$_['entry_sort_order']     = 'Ordre de tri'; 
$_['entry_sort_order_short']     = 'Trier'; 
$_['entry_settings_default']           = 'paramètres globaux'; 
$_['entry_settings_yes']           = 'Sur'; 
$_['entry_settings_no']           = 'Désactivé'; 


$_['entry_img_use_v0']           = 'Désactivé'; 
$_['entry_img_use_v1']           = 'On pour tous'; 
$_['entry_img_use_v2']           = "Sur pour la sélection"; 

$_['entry_img_first_v0']           = 'Comme d\'habitude'; 
$_['entry_img_first_v1']           = 'Remplacer par la première image d\'option'; 
$_['entry_img_first_v2']           = 'Ajouter à la liste des images options'; 

// Entry Module Settings
$_['entry_img_change']           = 'Modifier l\'image principale du produit sur l\'option Sélectionnez'; 
$_['entry_img_change_help']      = 'Modifier l\'image principale du produit sur la page du produit lorsque la valeur d\'option est sélectionnée (utilisez la première image d\'option)'; 
$_['entry_img_hover']            = 'Modifier l\'image principale du produit sur la souris sur'; 
$_['entry_img_hover_help']       = 'Modifier l\'image principale du produit sur la page du produit lorsque la souris planent une image supplémentaire'; 
$_['entry_img_main_to_additional']            = "Image principale à"; 
$_['entry_img_main_to_additional_help']       = 'Ajouter une image principale du produit à la liste des images supplémentaires'; 

$_['entry_img_use']              = 'Options Images comme des images supplémentaires'; 
$_['entry_img_use_help']         = 'Afficher les options Images dans une liste des images de produit supplémentaires sur la page du produit'; 

$_['entry_img_limit']            = "Filtrer des images supplémentaires"; 
$_['entry_img_limit_help']       = 'Afficher dans une liste d\'images de produit supplémentaires sur la page de produit uniquement Images appropriées pour les options sélectionnées Valeurs <br> 
works only with feature "'.$_['entry_img_use'].'"';
$_['entry_img_gal']              = 'Filtrer le produit Galerie d\'images'; 
$_['entry_img_gal_help']         = 'Afficher dans le produit Galerie d\'images uniquement Images visibles de la liste des images de produit supplémentaires sur la page du produit <br> 
Recommended to be use with features "'.$_['entry_img_use'].'" and "'.$_['entry_img_limit'].'"';

$_['entry_img_option']           = "Images ci-dessous l\'option"; 
$_['entry_img_option_help']      = 'Afficher la valeur d\'option Images ci-dessous Valeur d\'option Sélectionner / Radio / Cochez la case lorsque la valeur d\'option est sélectionnée'; 

$_['entry_img_category']         = 'Options dans les listes de produits'; 
$_['entry_img_category_help']    = 'Afficher les images d\'option Valeurs avec un petit aperçu des listes de produits tels que des catégories pages, des pages de marques et des boîtes de promotion de produits.'; 

$_['entry_img_first']            = 'Image d\'option standard'; 
$_['entry_img_first_help']       = 'Utilisation des images d\'option standard ajoutées à la page Options (catalogue de menus - Options - etc.)'; 
$_['entry_img_cart']             = 'Image de valeur d\'option dans le panier'; 
$_['entry_img_cart_help']        = 'Afficher la valeur d\'option sélectionnée Image dans le panier'; 

$_['entry_show_settings']        = "Afficher les paramètres d\'option de produit actuel"; 
$_['entry_hide_settings']        = "Masquer les paramètres d\'option de produit actuel"; 



// Error
$_['error_permission']     = 'Avertissement: vous n\'avez pas la permission de modifier le module '. $_["Heading_title"]. ''; 


$_['module_description']     = ''; 


$_['module_info']  = '"'. $_[" heading_title "]. '"'; 


?>