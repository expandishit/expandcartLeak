<?php

$_['heading_title']				     = 'Liste des règles de prix / quantité d\'Amazon'; 
$_['heading_title_add']			   = 'Ajouter prix / quantité '; 
$_['heading_title_edit']		   = 'Modifier les règles de prix / quantité'; 
$_['heading_title_csv']		     = 'Ajouter des règles avec le fichier CSV'; 

//text
$_['text_add']					       = 'Ajouter le prix / règle de quantité'; 
$_['text_price_type_fixed']	   = 'Prix fixe'; 
$_['text_price_type_percent']  = "Prix pour cent"; 
$_['text_price_type_inc']	     = '+ (Incrément)'; 
$_['text_price_type_dec']      = '- (décrément)'; 
$_['entry_rule_type']          = "Type de règle"; 

$_['text_store']					     = 'Magasin'; 
$_['btn_add_rule']	           = 'Ajouter une règle de prix'; 
$_['btn_add_csv']	             = 'Télécharger le CSV'; 
$_['btn_add_csv_tool']	       = 'Ajouter une liste de règles de prix en vrac du fichier de téléchargement de CSV'; 
$_['text_confirm']				     = 'Êtes-vous sûr de vouloir supprimer ?'; 
$_['text_success']				     = "Succès: Régule de prix ajoutée avec succès!"; 
$_['text_success_add']				 = "Succès: Régule de prix ajoutée avec succès!"; 
$_['text_success_edit']				 = 'Succès: règle de prix mis à jour avec succès! '; 
$_['text_success_del']				 = 'Succès: règle de prix supprimé avec succès!'; 
$_['text_list']				         = "Liste des règles de prix / quantité"; 

//entry
$_['entry_price_from']         = 'Règle de prix de'; 
$_['entry_price_to']           = 'Règle de prix à'; 
$_['entry_price_type']         = 'Type d\'opération'; 
$_['entry_price_value']        = "Valeur de montant"; 
$_['entry_price_status']       = "Statut de la règle de prix"; 
$_['entry_status']       = "Statut de la règle de prix"; 
$_['entry_price_store']        = 'Magasin'; 
$_['entry_price_opration']     = 'Opération'; 
$_['entry_csv']                = 'Télécharger le fichier CSV'; 
$_['help_csv']                 = 'Entrez le fichier CSV qui contient le format approprié identique à la démo.'; 
//column
$_['column_price_from']         = 'Règle de prix de'; 
$_['column_price_to']           = 'Règle de prix à'; 
$_['column_price_type']         = 'Type d\'opération'; 
$_['column_price_value']        = "Valeur de montant"; 
$_['column_price_status']       = "Statut de la règle de prix"; 
$_['column_status']             = "Statut de la règle de prix"; 
$_['column_price_store']        = 'Magasin'; 
$_['column_price_opration']     = 'Opération'; 
$_['column_action']             = 'Action'; 

//placeholder
$_['placeholder_price_from']   = 'Entrez le prix minimum de la règle de prix'; 
$_['placeholder_price_to']     = 'Entrez le prix maximum de la règle de prix'; 
$_['placeholder_price_value']  = "Entrez la valeur de la quantité"; 


//help
$_['help_price_from']          = 'Sélectionnez le prix minimum de la règle de prix à partir de'; 
$_['help_price_to']            = 'Définir le prix maximum de la règle de prix pour obtenir inactif'; 
$_['help_price_type']          = 'Sélectionnez le type d\'opération comme fixe ou pourcentage (seulement s\'appliquer uniquement au prix)'; 
$_['help_price_value']         = "Entrez la valeur de la quantité"; 
$_['help_price_status']        = "Définir le statut de la règle de prix"; 
$_['help_price_store']         = 'Sélectionnez le magasin'; 
$_['help_price_opration']      = 'Sélectionnez l\'opération AS + (incrément) ou - (décrément)'; 
$_['help_amazon_store_name']   = 'Entrez le nom du magasin de compte Amazon.'; 
$_['help_rule_type']           = 'Sélectionner la quantité ou le prix.'; 
//error
$_['error_field_required']     = 'S\'il vous plaît remplir le formulaire soigneusement.'; 
$_['error_price_to']           = 'La règle de prix sur le champ est requise et doit contenir une valeur supérieure à 0. '; 
$_['error_price_from']         = 'La règle de prix du champ est requise et doit contenir une valeur supérieure à 0. '; 
$_['error_price_value']        = "Le champ de valeur de montant est requis et doit contenir une valeur supérieure à 0."; 
$_['error_range_price_from']   = 'La gamme de prix est déjà utilisée, veuillez choisir un prix différent à laquelle la valeur n\'appartient pas à la gamme de règles de prix ajoutées.'; 
$_['error_range_wide_range']   = 'Plage de prix entré pour le prix et le prix pour contient d\'autres gammes de liste de règles, alors veuillez entrer une autre plage pour les deux déclarations. '; 
$_['error_numeric']            = 'S\'il vous plaît entrer uniquement des valeurs numériques.'; 
$_['error_empty_file']         = 'Veuillez télécharger un fichier CSV pour ajouter des règles de prix. '; 
$_['error_file_type']          = 'Fichier CSV incorrect. Veuillez télécharger un fichier correct avec une extension CSV. '; 
$_['error_csv_keys']           = "Votre fichier CSV contient des noms de colonne incorrects, veuillez les corriger de la même manière que le fichier CSV de démo. '"; 
$_['error_non_zero']           = "Votre fichier CSV contient la valeur 0 pour les valeurs de colonne non nulle, veuillez les corriger de la même manière que le fichier CSV Demo. '"; 
$_['error_zero']               = 'Ce champ ne valide pas la valeur DOE 0, veuillez corriger une valeur non nulle.'; 
$_['error_same_value']         = "Votre fichier csv contient Price_to et Price_from même valeur ou prix_to moins que Price_from Value, veuillez les corriger de la même manière que le fichier CSV Demo. '"; 
$_['error_range_price_to']      = 'Plage de prix entré pour le prix et le prix pour contient d\'autres gammes de liste de règles, alors veuillez entrer une autre plage pour les deux déclarations. '; 
$_['error_equal']              = 'Le prix de la valeur doit être inférieur à la valeur de la valeur, veuillez corriger la valeur des champs. '; 
$_['entry_info']  		         = 'Le téléchargement de fichier CSV vous permet d\'importer plusieurs données de règles de prix à partir d\'un seul fichier dans <A HREF ';
