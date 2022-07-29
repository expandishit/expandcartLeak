<?php

// Heading Goes here:
$_['heading_title']     = 'WhatsApp notifications'; 


// Text
$_['text_module']       = 'Applications'; 
$_['text_success']      = "Succès: vous avez modifié les notifications WhatsApp Module MODIFIÉE!"; 
$_['text_left']         = 'Gauche'; 
$_['text_right']        = 'Droit';
$_['text_home']         = 'Maison'; 

$_['text_yes']          = 'Oui'; 
$_['text_no']           = 'Non'; 

// Entry
$_['text_phone_num_filter']  = 'Numéro de téléphone commence par'; 
$_['text_phone_num_filter_help']  = 'Cette règle pour les numéros de téléphone du récepteur, WhatsApp n\'enverra que si le numéro de téléphone commence par les chiffres que vous entrez ici. 
You able to enter multiple patterns, it must be comma separated (ie: 00971,+971,0971)';

$_['whatsapp_entry_notify_customer_by_WhatsApp_on_registration']  = '<B> Notifications d\'enregistrement: </ B>'; 
$_['whatsapp_entry_notify_customer_by_WhatsApp_on_registration_help']  = 'WhatsApp enverra une notification au client une fois qu\'il a terminé son enregistrement.'; 

$_['text_cus_reg_temp']  = '<B> Modèle de message d\'enregistrement client </ B>'; 

$_['activation_message_template']  = '<B> Modèle de message d\'activation </ B>'; 

$_['text_message_header']  = 'Entête'; 
$_['text_message_body']  = 'Corps'; 
$_['text_message_footer']  = 'Bas de page'; 

$_['text_WhatsApp_confirm_per_order']  = '<b> Confirmer le code pour chaque nouvelle commande: </ b>'; 
$_['text_WhatsApp_confirm_per_order_help']  = 'En activant cela, le client doit recevoir un code de confirmation avec chaque nouvelle commande, même si le téléphone vérifié auparavant'; 

$_['text_WhatsApp_confirm']  = '<B> Confirmer le téléphone sur la première commande uniquement: </ b>'; 
$_['text_WhatsApp_confirm_help']  = 'En activant cela, le client doit recevoir un code de confirmation avec une fois'; 


$_['text_WhatsApp_confirm_trials']  = 'Messages de confirmation maximale'; 

$_['text_WhatsApp_confirm_template']  = 'Le modèle de message'; 

$_['text_WhatsApp_confirm_trials_help']  = 'Les essais maximaux comptent que le client peut demander renvoyer le code de confirmation'; 

$_['whatsapp_entry_notify_customer_by_WhatsApp_on_checkout']  = '<B> Nouvelle notification de commande: </ b>'; 
$_['whatsapp_entry_notify_customer_by_WhatsApp_on_checkout_help']  = 'En activant cela, les clients recevront un message une fois qu\'il confirme une nouvelle commande'; 

$_['text_cus_order_temp']  = 'Le modèle de message'; 

$_['text_admin_order_status']  = '<B> Notification d\'état de la commande de mise à jour </ B>'; 
$_['text_WhatsApp_order_status']  = '<B> Notification d\'état de la commande de mise à jour </ B>'; 

$_['text_WhatsApp_order_status_help']  = 'En activant cela, le client recevra un message lorsque vous mettez à jour le statut de ses commandes. 
                  <br />
                  In addition to the variables listed above, Four other variables can be used here: <b></b> , <b>{order_id}</b> , <b>{order_date}</b>, <b>{comment}</b> 
                  which is the comment you write when you add history.
                  <br />
                  Empty text box will use default template.';

$_['text_admin_order_status_help']  = 'Par activer cela, le propriétaire du stockage (vous) recevra un message lorsque l\'état de la commande est mis à jour. 
                <br />
                In addition to the variables listed above, Four other variables can be used here: <b></b> , <b>{order_id}</b> , <b>{order_date}</b>, <b>{comment}</b> 
                which is the comment you write when you add history.
                <br />
                Empty text box will use default template.';

$_['text_add_new_field']  = "Ajouter un nouveau message"; 

$_['whatsapp_entry_ntfy_admin_by_WhatsApp_on_reg']  = '<B> Notification d\'enregistrement: </ B>'; 
$_['whatsapp_entry_ntfy_admin_by_WhatsApp_on_reg_help']  = 'En activez cela, le propriétaire du stockage (vous) recevra un message lorsqu\'un client complète l\'enregistrement'; 

$_['text_admin_cust_reg']  = 'Le modèle de message'; 


$_['whatsapp_entry_notify_admin_by_WhatsApp_on_checkout']  = '<B> Nouvelle notification de commande: </ b>'; 
$_['whatsapp_entry_notify_admin_by_WhatsApp_on_checkout_help']  = 'En activez cela, le propriétaire du magasin (vous) recevra un message lors de la création d\'une nouvelle commande'; 

$_['whatsapp_entry_username']  = 'Nom d\'utilisateur'; 
$_['whatsapp_entry_passwd']    = 'Mot de passe'; 
$_['whatsapp_entry_status']    = 'Statut'; 

$_['whatsapp_entry_notify_seller_on_status_change']  = 'Notifier le vendeur sur <b> admin change un statut </ B>'; 
$_['whatsapp_entry_notify_seller_on_status_change_help']  = 'Envoyer WhatsApp au vendeur une fois l\'administrateur modifier son statut.'; 

$_['whatsapp_entry_cstmr_reg_available_vars']                = 'Actuellement, seulement <b> {prénom} </ b>, <b> {lastname} </ b>, <b> {téléphone} </ b> et <b> {mot de passe} </ b> sont disponibles pour substitution'; 







$_['whatsapp_entry_notify_extra_by_WhatsApp_on_checkout']         = 'Numéros de téléphone d\'alerte supplémentaires: <br />'. 
    '<span class = "Aide"> Tous les numéros de téléphone supplémentaires que vous souhaitez recevoir de l\'alerte par WhatsApp (Séparéve des virgules). '. 
    '<br />If filled then WhatsApp will be sent even if you disable "Notify store owner for new order"</span>';

$_['whatsapp_entry_order_available_vars']  = 'Actuellement, seulement <b> {Commande_ID} </ B>, <b> {Commande_Date} </ B> est disponible pour la substitution'; 

// Error
$_['error_permission']  = 'Avertissement: vous n\'avez pas la permission de modifier le module WhatsApp!'; 
$_['text_footer']       = 'S\'il vous plaît répondez à ce WhatsApp si vous avez des questions.'; 



$_['text_gateway_setup']  = "Configuration de la passerelle"; 
$_['text_WhatsApp_tem']  = 'WhatsApp Mody'; 

$_['text_data_that_will_be_in_DB_only']  = "Les données qui seront uniquement dans dB"; 
$_['text_integration_status']  = 'Statut de l\'intégration WhatsApp: '; 
$_['text_Client_api_status']  = 'État de l\'API du client: '; 
$_['text_whatsapp_integration']  = 'WhatsApp Integration'; 
$_['text_whatsapp_phone_number']  = 'Numéro de téléphone WhatsApp'; 
$_['text_whatsapp_business_account_id']  = 'ID de compte de l\'entreprise WhatsApp'; 
$_['text_template_messages_namespaces']  = "Espaces de noms de messages de modèle"; 
$_['text_client_api_url']  = "URL de l\'API client"; 
$_['text_client_api_username']  = 'Nom d\'utilisateur de l\'API client'; 
$_['text_client_api_password']  = "mot de passe de l\'API client"; 

$_['text_customer_notif']  = 'Notifications client'; 
$_['text_admin_notif']  = "Notifications du propriétaire de magasin"; 
$_['text_seller_notif']  = 'Alertes du vendeur'; 

$_['text_whatsApp_template_messages']  = 'Messages de modèle'; 
$_['text_whatsapp_chat']  = 'WhatsApp Chat'; 
$_['text_language']  = 'Langue'; 
$_['text_header']  = 'entête'; 
$_['text_body']  = 'corps'; 
$_['text_footer']  = 'bas de page'; 


$_['text_WhatsApp_filter']  = 'Téléphones Country Keys'; 
$_['text_number_rewrite']  = "Réécriture de numéro"; 
$_['text_logs']  = "Journaux"; 
$_['text_api_url']  = 'API URL'; 
$_['text_api_http_method']  = 'Méthode http'; 
$_['text_get']  = 'OBTENIR'; 
$_['text_post_1']  = 'Post (multipart / form-données)'; 
$_['text_post_2']  = 'Poste (Application / X-www-Form-Urlencoded)'; 
$_['text_api_method_help']  = '<p> 
                          POST (multipart/form-data) or POST (application/x-www-form-urlencoded)?
                          As usual, check the gateway documentation. But here are some hints:
                          </p>
                              <ul>
                                  <li>API from old WhatsApp gateways are used to use POST (multipart/form-data)</li>
                                  <li>Most recent WhatsApp gateway APIs use POST (application/x-www-form-urlencoded)</li>
                              </ul>
                          </p>';
$_['text_dest_field']  = 'Le champ de destination'; 
$_['text_dest_field_help']  = 'C\'est le nom de la variable qui représente les numéros de destination.'; 
$_['text_dest_field_placeholder']  = 'ex: mobiles ou destinations'; 
$_['text_msg_field']  = "Le champ de message"; 
$_['text_msg_field_help']  = 'C\'est le nom de la variable qui représente le message.'; 
$_['text_msg_field_placeholder']  = 'ex: message'; 
$_['text_unicode']  = 'Unicode?'; 
$_['text_unicode_help']  = "Une passerelle API (ex. Pour la langue arabe) nécessite que le corps du message soit converti en unicode."; 
$_['text_unicode_help_2']  = 'Nous enlevons le \ u avant d\'envoyer. Ex: <b> test </ b> sera envoyé comme suit: <b> 0074006500730074 </ b> '; 
$_['text_additional_fields']  = 'Champs supplémentaires'; 
$_['text_name']  = 'Nom'; 
$_['text_field_name']  = 'nom de domaine'; 
$_['text_value']  = 'Évaluer'; 
$_['text_field_value']  = "valeur de champ"; 
$_['text_url_encode']  = 'URL Encode'; 
$_['text_remove_field']  = 'Supprimer ce champ'; 
$_['text_url_encode_help']  = '<p style 
                            <p>URL encoding converts characters into a format that can be sent through internet.</p>
                            <p>We should use urlencode for all GET parameters because POST parameters are automatically encoded.</p>
                            <p>Some API doesn\'t understand some URL encoded fields when sending with GET. If this is the case, disable URL encoding for the concerned fields.</p>';
$_['text_WhatsApp_template_system']  = "Système de modèles WhatsApp"; 
$_['text_WhatsApp_temp_sys_1']  = "Vous utiliserez des variables prédéfinies qui sont des détenteurs de places qui seront remplacées par les informations réelles au moment de l\'exécution. "; 
$_['text_available_var']  = "Variables disponibles"; 
$_['text_arrow']  = '→'; 
$_['text_firstname']  = 'Prénom'; 
$_['text_lastname']  = 'Nom de famille'; 
$_['text_phonenumber']  = 'Numéro de téléphone'; 
$_['text_orderid']  = 'Numéro de commande'; 
$_['text_total']  = 'Total';
$_['text_storeurl']  = "URL de magasin"; 
$_['text_shippingadd1']  = 'Adresse de livraison 1'; 
$_['text_shippingadd2']  = 'Adresse de livraison 2'; 
$_['text_payadd1']  = 'Adresse de paiement 1'; 
$_['text_payadd2']  = 'Adresse de paiement 2'; 
$_['text_paymethod']  = 'mode de paiement'; 
$_['text_shipmethod']  = 'Mode de livraison'; 
$_['text_WhatsApp_system_example']  = 'Exemple: 
					<span class = "Aide"> <i> Cher <B> {FirstName} </ B>, merci de votre commande sur mystore.com ID de commande <B> {Commande_ID} </ B> Montant <B> {TOTAL} </ B > </ i> </ span> 
					<br />
					<span class = "Aide"> La prochaine fois qu\'un client fait une commande (disons Ahmed), il recevra un WhatsApp contenant ce qui suit: </ span> 
					<span class = "Aide"> <I> Cher <b> Ahmed </ B>, merci de votre commande sur mystore.com ID de commande <B> 9999 </ B> montant <B> 9999,99 </ B> </ i> < / span> '; 
$_['text_no_send_kw']  = "Mots-clés à ne pas envoyer "; 
$_['text_no_send_help']  = 'n\'envoyez pas WhatsApp au client sur <B> Nouveau commande </ B> si l\'un des mots-clés suivants est utilisé dans le coupon lors de la caisse. 
					<br />
					One keyword per line (a keyword can contain spaces).';
$_['text_WhatsApp_seller_status']  = 'Modèle WhatsApp sur le changement de statut du vendeur'; 

$_['text_WhatsApp_seller_status_help']  = 'Modèle WhatsApp à utiliser lorsque l\'administrateur met à jour le statut du vendeur dans la page du vendeur. 
                  <br />
                  In addition to the variables listed above, Five other variables can be used here: <b></b> , <b>{seller_email}</b> , <b>{seller_firstname}</b>, <b>{seller_lastname}</b>, <b>{seller_nickname}</b> 
                  <br />
                  Empty text box will use default template.';
$_['text_add_new_fields']  = "Ajouter un nouveau message"; 
$_['text_status']  = 'Statut'; 
$_['text_seperator']  = '───────'; 
$_['text_admin_WhatsApp_temp']  = 'Modèle de propriétaire de magasin WhatsApp'; 
$_['text_admin_WhatsApp_temp_help']  = 'Vous pouvez utiliser les variables énumérées ci-dessus'; 


$_['text_add_new_fields_2']  = 'Ajouter de nouveaux champs'; 
$_['text_status_2']  = 'Statut'; 
$_['text_seperator_2']  = '───────'; 

$_['text_filter_size']  = 'Numéro de téléphone Filtrage: <I> <B> Taille du numéro </ B> </ I>'; 
$_['text_filter_size_help']  = 'Envoyer WhatsApp uniquement si le numéro de téléphone a x chiffres que vous entrez ici. Par exemple: Si vous définissez la valeur sur 8 puis, WhatsApp automatique sera envoyé à 12345678 mais pas à 2345678. '; 
$_['text_phone_rewrite']  = 'Numéro de téléphone Réécriture'; 
$_['text_phone_rewrite_help']  = 'Effectuez le remplacement au numéro de téléphone avant d\'envoyer WhatsApp. 
                            <br />
                            Rewriting is applied only after filtering rules are applied.';
$_['text_replace_1_occ']  = 'Remplacer la première occurrence de'; 
$_['text_pattern']  = 'modèle'; 
$_['text_by']  = 'par'; 
$_['text_substitution']  = 'substitution'; 
$_['text_enable_logs']  = 'Activer les journaux'; 
$_['text_enable_logs_help']  = 'Les journaux verbeux seront imprimés dans le fichier journal. Utile lorsque vous devez déterminer ce qui se passe. '; 



$_['text_WhatsApp_confirm_template_help']  = 'Le modèle utilisé lors de la confirmation du téléphone à l\'aide de WhatsApp. Vous ne pouvez utiliser que <b> {prénom} </ b>, <b> {lastname} </ b>, <b> {téléphone} </ b>. <br> et vous devez ajouter <b> {confirmer_code} </ b> il est indispensable, de sorte que le message contient le code de confirmation '; 

$_['text_tab_supported']  = "Passerelles"; 
$_['text_supported_providers']  = 'Nous soutenons de nombreux fournisseurs de services WhatsApp dans tous les pays, tels que'; 
$_['text_supported_providers_help']  = "Si vous avez besoin d\'aide pour activer tout fournisseur de services de ce qui précède, ou tout autre fournisseur de services, veuillez parler de l\'un de nos représentants de services à la clientèle."; 

$_['text_status']  = 'statut'; 


$_['activation_message_template_note']  = 'Vous pouvez utiliser <br /> <b> {activationToken} </ b>'; 

$_['code_settings']  = 'Paramètres de code d\'activation'; 
$_['code_length']  = "Longueur de code"; 
$_['code_type']  = 'Type de code'; 
$_['code_alphanumeric']  = 'Alphanumérique'; 
$_['code_numeric']  = 'Nombres'; 

$_['text_seller_status_notification_header']  = 'Cher,';
$_['text_seller_status_notification_body_prefix']  = "Votre statut de compte est "; 
$_['text_seller_status_notification_footer']  = ''; 

$_['text_insert_your_business_Data']  = 'Insérez vos données professionnelles'; 
$_['text_in_verification']               = "en vérification"; 
$_['text_get_confirmation_code']          = 'recevoir le code de confirmation'; 
$_['text_enter_confirmation_code']          = 'Entrer le code de confirmation'; 
$_['text_verified']                        = "vérifié"; 
$_['entry_business_name']                    = 'Nom d\'entreprise'; 
$_['entry_whatsapp_business_id']           = 'WhatsApp Business ID'; 
$_['entry_whatsapp_business_id_help']      = 'Vous pouvez obtenir cet identifiant de votre responsable de l\'entreprise WhatsApp'; 
$_['entry_whatsapp_phone_number']           = 'Numéro de téléphone pour WhatsApp'; 
$_['entry_whatsapp_phone_number_help']      = 'Ce numéro devrait être enregistré en tant que numéro d\'entreprise avec WhatsApp'; 
$_['entry_country_code']                   = 'Code postal'; 
$_['entry_phone_number']                   = 'Numéro de téléphone'; 
$_['entry_whatsapp_methods']               = 'Méthode de vérification'; 
$_['text_we_are_reviewing_your_Data_and_will_confirm_soon']           = 'Nous examinons vos données et nous confirmerons bientôt.'; 
$_['entry_whatsapp_verification_code']     = 'code de vérification'; 
$_['text_congratulation']                 = 'Félicitations! <bro> processus d\'intégration terminé '; 
$_['btn_next']                                = 'Suivant';
$_['btn_previous']                            = 'Précédent';
$_['btn_finish']                            = "commençons"; 
$_['heading_steps_title']                   = "processus d\'intégration"; 
