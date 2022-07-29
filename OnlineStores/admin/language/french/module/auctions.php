<?php


//Heading titles
$_["heading_title"]               = "Les enchères"; 
$_["heading_title_auctions"]      = "Les enchères"; 
$_["heading_title_create"]        = "Créer une nouvelle vente aux enchères"; 
$_["heading_title_edit"]          = "Mettre à jour une vente aux enchères"; 
$_["heading_title_orders"]         = "Commandes enchères"; 
$_["heading_title_deposit"]       = "Dépôts des enchères"; 

$_["text_module"]                 = "Applications"; 
$_["text_success"]                = "Sauvegardé avec succès"; 

$_["entry_move_to_next_winner"]   = "Passer au prochain gagnant"; 
$_["entry_move_to_next_winner_help"]  = "Si gagner le soumissionnaire n\'a pas acheté le produit de vente aux enchères, effectuez la prochaine offre la plus élevée du soumissionnaire gagnant"; 

//Form inputs & entries
$_["entry_product"]               = "Nom du produit"; 
$_["entry_starting_bid_price"]    = "Prix d\'offre de départ"; 
$_["text_starting_bid_price_help"] = "Doit être supérieur à zéro"; 
$_["entry_start_datetime"]        = "Date de début / heure"; 
$_["entry_close_datetime"]        = "Date / heure rapprochée"; 
$_["entry_min_deposit"]           = "Montant de dépôt minimum"; 
$_["entry_increment"]             = "Incrément d\'appel d\'offres"; 
$_["text_increment_help"]         = "Le montant à ajouter au montant de l\'offre actuelle pour calculer la prochaine offre minimale autorisée. La valeur par défaut est de 0,25% s"; 
$_["entry_min_quantity"]          = "Quantité minimum"; 
$_["entry_quantity"]              = "Quantité"; 
$_["entry_min_quantity_help"]     = "Quantité minimale de produit d\'enchères Le gagnant peut acheter avec le prix de candidature gagnant"; 
$_["entry_max_quantity"]          = "Quantité maximale"; 
$_["entry_max_quantity_help"]     = "Quantité maximale d\'un produit de vente aux enchères Le gagnant peut acheter avec le prix d\'offre gagnant"; 
$_["entry_purchase_valid_days"]   = "Achat de jours valides"; 

$_["entry_auctions_timezone"]     = "Timezone des enchères"; 
$_["text_purchase_valid_days_help"]   = "Nombre de jours valables pour gagner le soumissionnaire pour acheter le produit de vente aux enchères avec le prix gagnant. La valeur par défaut est de 3 jours et les jours maximum autorisés sont de 99 jours"; 
$_["text_days"]                   = "Jours"; 
$_["text_items"]                  = "Articles"; 

//Auctions list table columns..
$_["column_product_name"]         = "Nom du produit"; 
$_["column_starting_price"]       = "Prix de départ"; 
$_["column_increment"]            = "Incrément"; 
$_["column_start_datetime"]       = "Démarrer DateTime"; 
$_["column_close_datetime"]       = "Fermer DateTime"; 
$_["column_auction_status"]       = "Statut d\'enchères"; 
$_["column_biding_status"]        = "Statut d\'appel d\'offres"; 
$_["column_min_deposit"]          = "Dépôt minimum"; 
$_["column_paid_at"]              = "Payé à"; 
$_["column_auction_id"]           = "ID de vente aux enchères"; 

//Bids list tab table
$_["column_bidder_name"]          = "Nom du soumissionnaire"; 
$_["column_amount"]               = "Montant";
$_["column_is_winner"]            = "Gagne"; 
$_["lbl_auction_bids"]            = "Liste des enchères"; 
//tabs
$_["tab_settings"]     = "Réglages"; 
$_["tab_orders"]       = "Ordres"; 
$_["tab_auctions"]     = "Les enchères"; 
$_["tab_auction_info"] = "Informations sur les enchères"; 
$_["tab_auction_bids"] = "Liste des offres"; 
$_["tab_deposits"]     = "LOG DE DÉPÔTS"; 

//Buttons
$_["btn_new_auction"]     = "Ajouter une nouvelle vente aux enchères"; 




//Anytime picker labels
$_["text_select_datetime"]  = "Sélectionnez une date et une heure"; 
$_["text_hour"]             = "Heure"; 
$_["text_minute"]           = "Minute"; 
$_["text_second"]           = "Second";
$_["text_day_of_month"]     = "Jour du mois"; 
$_["text_month"]            = "Mois"; 
$_["text_year"]             = "An"; 
$_["text_order_order"]      = "Commander"; 
$_['text_auction']          = "Enchères"; 

//error messages
$_["error_purchase_valid_days"]  = "l\'achat de jours valides est requis et doit être supérieur à zéro"; 
$_["error_max_quantity"]         = "La quantité maximale doit être non nulle, positive, entier et pas moins que la valeur minimale de la quantité"; 
$_["error_min_quantity"]         = "La quantité minimale doit être un nombre non nul, positif et entier"; 
$_["error_close_datetime"]       = "La date / l\'heure rapprochée est requise et doit être supérieure à la date / heure de début"; 
$_["error_start_datetime"]       = "La date / heure de début est requise et doit être égale ou supérieure à la date et heure actuelles"; 
$_["error_increment"]            = "l\'incrément doit être un nombre non nul et positif"; 
$_["error_min_deposit"]          = "Le dépôt minimum est requis et doit être un nombre positif"; 
$_["error_starting_bid_price"]   = "Le prix d\'achat de départ est requis et doit être un nombre non nul et positif"; 
$_["error_product"]              = "Un nom de produit doit être sélectionné"; 
$_["error_warning"]              = "Les erreurs:"; 
$_["error_delete"]               = "Impossible de supprimer le fonctionnement / s"; 
$_["error_delete_bid"]           = "Impossible de supprimer cette offre"; 
$_["error"]  = "Erreur"; 
