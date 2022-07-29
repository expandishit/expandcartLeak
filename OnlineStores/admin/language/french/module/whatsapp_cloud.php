<?php
// L'en-tête va ici :
$_["heading_title"]           = "WhatsApp";
$_["title_whatsapp_bot"]      = "Bot WhatsApp";
$_["title_whatsapp_chatting"] = "Chat WhatsApp";
$_["title_whatsapp_settings"] = "Paramètres WhatsApp";
$_["title_settings"]          = "Paramètres";

//bac à sable
$_["text_register_success"]                     = "Enregistrement réussi !";
$_["text_thank_you_for_choosing_whatsapp"]      = "Merci d'avoir choisi WhatsApp !";
$_["text_whatsapp_team_review_your_request"]    = "En soumettant la demande, l'équipe WhatsApp examinera la demande et vous enverra sa confirmation";
$_["text_account_review_status"]                = "statut de révision du compte : %s";
$_["text_send_test_message"]                    = "Envoyer un message de test";
$_["text_enter_phone_number"] 					= "Entrez le numéro what'sapp avec le code de téléphone ex : +2010000000000" ;
$_["text_congratulations"]                      = "Félicitations";
$_["text_now_you_can_send_notifications"]       = "Vous pouvez désormais envoyer des notifications à vos clients via WhatsApp";
$_["text_verify_your_WhatsApp_business_number"] = "Vérifiez votre numéro d'entreprise WhatsApp pour commencer à envoyer des notifications";
$_["text_we_are_preparing_you_data"]            = "nous préparons vos données veuillez patienter quelques secondes .. ";
$_["text_start_sending_notifications"]          = "Commencer à envoyer des notifications";
$_["text_message_sent"]                         = "Message envoyé !";
$_["text_loading"]                              = "Chargement en cours..";

$_["text_status_approved"] 						= "Approuvé" ;
$_["text_status_pending"] 						= "En attente" ;
$_["text_status_rejected"] 						= "Rejeté" ;
$_["text_ok"] 									= "D'accord" ;

//relier
$_["text_connect_whatsApp_business_account"]    = "Connecter le compte WhatsApp Business";
$_["title_setup_whatsApp_business_account"]     = "Configurer un compte WhatsApp Business";
$_["text_setup_whatsApp_business_desc"]         = "Pour configurer, vous devrez connecter le compte professionnel Facebook de votre entreprise à votre boutique sur ExpandCart";
$_["text_to_connect_you_should"]                = "Pour vous connecter, vous devez entrer,";
$_["text_your_company_legal_documents"]         = "Le nom légal et l'adresse de votre entreprise";
$_["text_display_name_and_short_desc"]          = "Un nom d'affichage et une courte description de l'entreprise";
$_["text_phone_number_you_have_access_to"]      = "Un numéro de téléphone auquel vous avez accès, appartenant à votre entreprise";
$_["text_connect_with_facebook"]                = "Se connecter avec Facebook";
$_["text_something_went_wrong_contact_support"] = "Une erreur s'est produite, veuillez contacter l'assistance !";
$_["text_error"]                                = "Erreur !";

// sélection du téléphone
$_["text_select_phone_number_desc"] = "Il semble que ce compte WA ait plusieurs numéros de téléphone, veuillez choisir celui qui vous intéresse";
$_["text_choose_phone_number"]      = "Choisir le numéro de téléphone";
$_["btn_confirm"]                   = "Confirmer";

// robot
$_['btn_add_new_template']                  = 'Ajouter un nouveau modèle';
$_['btn_disable']                           = 'Désactiver';
$_['text_active']                           = 'Actif';
$_['text_pending']                          = 'En attente';
$_['text_custom']                           = 'Douane';
$_['text_need_attention']                   = 'Besoin d\'attention';
$_['text_customer_notification']            = 'Notifications clients';
$_['text_store_owner_notifications']        = 'Notifications du propriétaire de la boutique';
$_['text_seller_notifications']             = 'Notifications du vendeur';
$_['text_registration_notifications']       = 'Notifications d\'inscription';
$_['text_new_order_notification']           = 'Notification de nouvelle commande';
$_['text_phone_confirmation_notification']  = 'Notification de confirmation de téléphone';
$_['text_header']                           = 'En-tête';
$_['text_message_template']                 = 'Modèle de message';
$_['text_footer']                           = 'Pied de page';
$_['text_preview_template_message_desc']    = '[Aperçu du modèle de message]';
$_['text_order_notification_with_status_x'] = 'Notification de commande pour le statut : [%s]';
$_['text_verification_status']              = 'Statut de vérification';
$_['text_pending_approval']                 = 'Approbation en attente :';
$_['text_sending_failed']                   = 'L\'envoi a échoué :';
$_['text_approved']                         = 'Approuvé :';
$_['text_need_attention']                   = 'Attention requise :';
$_['text_visit']                            = 'Visite';
$_['text_community_guidelines']             = 'Règles de la communauté';
$_['text_send_at_first_order_only'] 		= 'Envoyer à la première commande uniquement';

$_['template_help_customer_account_registration'] = 'Currently, only {firstname}, {lastname}, {fullname}, {telephone} and {password} are available for substitution';
$_['template_help_customer_checkout']             = 'Currently, only {order_id}, {order_date} are available for substitution';
$_['template_help_customer_phone_confirm']        = 'The template used during phone confirmation using WhatsApp. You can only use {firstname}, {lastname}, {fullname}, {telephone}. And you have to add {confirm_code} it is a must, so that the message contains the confirmation code';
$_['template_help_customer_order_observers']      = 'Currently, only {order_id} , {order_date}, {comment}  which is the comment you write when you add history. Empty text box will use default template.';
$_['template_help_admin_account_registration']    = 'Currently, only {firstname}, {lastname}, {fullname}, {telephone} and {password} are available for substitution';
$_['template_help_admin_checkout']                = 'Currently, only {order_id} and {order_date} are available for substitution';
$_['template_help_admin_order_observers']         = 'Currently, only {order_id} , {order_date}, {comment}  which is the comment you write when you add history. Empty text box will use default template.';

$_['text_seller_notification_with_status_x'] = 'Seller Notification for status : [%s]';


$_['ms_seller_status_' . MsSeller::STATUS_ACTIVE]   = 'Active';
$_['ms_seller_status_' . MsSeller::STATUS_INACTIVE] = 'Inactive';
$_['ms_seller_status_' . MsSeller::STATUS_DISABLED] = 'Disabled';
$_['ms_seller_status_' . MsSeller::STATUS_DELETED]  = 'Deleted';
$_['ms_seller_status_' . MsSeller::STATUS_UNPAID]   = 'Unpaid signup fee';

//chatting 
$_['title_chatting'] 			= 'Discuter' ;
$_['title_profile'] 			= 'Profil';
$_['label_change']			 	= 'Modifier' ;
$_['entry_name'] 				= 'Nom' ;
$_['entry_business_breif'] 		= 'Bref d\'affaires' ;
$_['entry_email'] 				= 'E-mail' ;
$_['entry_address'] 			= 'Adresse';
$_['entry_business_category'] 	= 'Catégorie d\'entreprise' ;
$_['button_save_changes'] 		= 'Enregistrer les modifications' ;
$_['entry_name_help'] 			= 'Ceci n\'est pas un nom d\'utilisateur, il sera visible par vos utilisateurs WhatsApp';

$_['text_no_contact_selected']	= 'Aucun contact sélectionné ';
$_['text_chat_start_desc']		= 'Démarrez le chat en sélectionnant dans le menu Contacts';

//réglage
$_['button_logout'] 							  = 'Déconnexion' ;
$_['entry_linked_whatsapp_number'] 				  = 'Numéro Whatsapp lié' ;
$_['entry_whatsapp_business_account_id'] 		  = 'ID de compte Whatsapp Business' ;
$_['entry_maximum_confirmation_messages'] 		  = 'Nombre maximal de messages de confirmation';
$_['text_whatsapp_confirm_trails_help'] 		  = 'Le nombre maximum d\'essais que le client peut demander de lui renvoyer le code de confirmation';
$_['entry_allowed_countries_keys'] 				  = 'Clés des pays autorisés' ;
$_['entry_allowed_countries_keys_help'] 		  = 'Seuls les utilisateurs des pays sélectionnés pourront recevoir vos messages.';
$_['entry_allowed_countries_keys_hint'] 		  = 'Choisissez les touches téléphoniques du pays pour les utilisateurs que vous souhaitez prendre en charge, le statut par défaut est « Tous »';
$_['entry_remove_app']							  = 'Êtes-vous sûr de vouloir déconnecter Whatsapp ? ';
$_['text_chat_displaying'] 						  = 'Affichage du chat sur la boutique ';
$_['text_remove_app_desc'] 						  = 'En vous déconnectant, vous perdrez la connexion à toutes les fonctionnalités WhatsApp ';
$_['text_display_controls'] 					  = 'Afficher les commandes' ;
$_['text_all_customers'] 						  = 'Tous les clients' ;
$_['text_specific_customer'] 					  = 'Groupes de clients spécifiques' ;
$_['select_customer_group'] 					  = 'Sélectionner un groupe de clients' ;
$_['text_customer_groups_help'] 				  = 'Devrait sélectionner au moins un groupe ou choisir toutes les options client';
$_['msg_allowed_numbers_updated'] 				  = 'numéros autorisés mis à jour !';
$_['msg_logout_success'] 						  = 'Déconnexion réussie !';
$_['text_no_contacts'] 							  = 'AUCUN contact trouvé !';

$_['template_help_customer_account_registration'] = 'Actuellement, seuls {firstname}, {lastname}, {fullname}, {telephone} et {password} sont disponibles pour la substitution';
$_['template_help_customer_checkout']             = 'Actuellement, seuls {order_id}, {order_date} sont disponibles pour la substitution';
$_['template_help_customer_phone_confirm']        = 'Le modèle utilisé lors de la confirmation par téléphone via WhatsApp. Vous ne pouvez utiliser que {firstname}, {lastname}, {fullname}, {telephone}. Et vous devez ajouter {confirm_code} c\'est un must, pour que le message contienne le code de confirmation';
$_['template_help_customer_order_observers']      = 'Actuellement, uniquement {order_id} , {order_date}, {comment} qui est le commentaire que vous écrivez lorsque vous ajoutez un historique. La zone de texte vide utilisera le modèle par défaut.';
$_['template_help_admin_account_registration']    = 'Actuellement, seuls {firstname}, {lastname}, {fullname}, {telephone} et {password} sont disponibles pour la substitution';
$_['template_help_admin_checkout']                = 'Actuellement, seuls {order_id} et {order_date} sont disponibles pour la substitution';
$_['template_help_admin_order_observers']         = 'Actuellement, uniquement {order_id} , {order_date}, {comment} qui est le commentaire que vous écrivez lorsque vous ajoutez un historique. La zone de texte vide utilisera le modèle par défaut.';

$_['text_seller_notification_with_status_x'] 	  = 'Notification du vendeur pour le statut : [%s]';


$_['ms_seller_status_' . MsSeller::STATUS_ACTIVE]   = 'Actif';
$_['ms_seller_status_' . MsSeller::STATUS_INACTIVE] = 'Inactif';
$_['ms_seller_status_' . MsSeller::STATUS_DISABLED] = 'Désactivé';
$_['ms_seller_status_' . MsSeller::STATUS_DELETED]  = 'Supprimé';
$_['ms_seller_status_' . MsSeller::STATUS_UNPAID]   = 'Frais d\'inscription non payés';

//bavardage
$_['title_chatting']          = 'Discuter';
$_['title_profile']           = 'Profil';
$_['label_change']            = 'Modifier';
$_['entry_name']              = 'Nom';
$_['entry_business_breif']    = 'Bref d\'affaires';
$_['entry_email']             = 'E-mail';
$_['entry_address']           = 'Adresse';
$_['entry_business_category'] = 'Catégorie d\'entreprise';
$_['button_save_changes']     = 'Enregistrer les modifications';
$_['entry_name_help']         = 'Ceci n\'est pas un nom d\'utilisateur, il sera visible par vos utilisateurs WhatsApp';

$_['text_no_contact_selected'] = 'Aucun contact sélectionné ';
$_['text_chat_start_desc']     = 'Démarrez le chat en sélectionnant dans le menu Contacts';

//réglage
$_['button_logout']                       = 'Déconnexion';
$_['entry_linked_whatsapp_number']        = 'Numéro Whatsapp lié';
$_['entry_whatsapp_business_account_id']  = 'ID de compte Whatsapp Business';
$_['entry_maximum_confirmation_messages'] = 'Nombre maximal de messages de confirmation';
$_['text_whatsapp_confirm_trails_help']   = 'Le nombre maximum d\'essais que le client peut demander de lui renvoyer le code de confirmation';
$_['entry_allowed_countries_keys']        = 'Clés des pays autorisés';
$_['entry_allowed_countries_keys_help']   = 'Seuls les utilisateurs des pays sélectionnés pourront recevoir vos messages.';
$_['entry_allowed_countries_keys_hint']   = 'Choisissez les touches téléphoniques du pays pour les utilisateurs que vous souhaitez prendre en charge, le statut par défaut est « Tous »';
$_['entry_remove_app']                    = 'Êtes-vous sûr de vouloir déconnecter Whatsapp ? ';
$_['text_chat_displaying']                = 'Affichage du chat sur la boutique ';
$_['text_remove_app_desc']                = 'En vous déconnectant, vous perdrez la connexion à toutes les fonctionnalités WhatsApp ';
$_['text_display_controls']               = 'Afficher les commandes';
$_['text_all_customers']                  = 'Tous les clients';
$_['text_specific_customer']              = 'Groupes de clients spécifiques';
$_['select_customer_group']               = 'Sélectionner un groupe de clients';
$_['msg_allowed_numbers_updated']         = 'numéros autorisés mis à jour !';
$_['msg_logout_success']                  = 'Déconnexion réussie !';
$_['text_no_contacts']                    = 'AUCUN contact trouvé !';
$_['text_success'] 						  = 'succès';
$_['text_error'] 						  = 'Erreur!';
$_['text_setting_changed_successfully']   = 'Paramètre modifié avec succès';
$_['text_something_went_wrong'] 		  = 'Quelque chose s\'est mal passé';



//validations 
$_['error_email_invalid'] = "Format d'email invalide";
