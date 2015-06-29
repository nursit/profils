<?php

// This is a SPIP language file  --  Ceci est un fichier langue de SPIP

$GLOBALS[$GLOBALS['idx_lang']] = array(

	'cfg_titre_parametrages' => 'Configuration',
	'legend_creation_automatique_profil' => 'Création automatique des profils',
	'label_creer_depuis_mailsubscriber_oui' => 'Création automatique lors d\'une inscription à l\'Infolettre',
	'label_creer_depuis_souscription_adhesion_oui' => 'Création automatique lors d\'une Ahésion',
	'label_creer_depuis_souscription_don_oui' => 'Création automatique lors d\'un Don',
	'label_creer_sur_paiements_attente_oui' => 'Créer aussi les profils sur les paiements en attente',

	'bouton_creer_compte' => 'Créer mon compte',
	// L
	'label_auteur_coordonnees' => "Coordonnées",
	'label_auteur_name' => 'Nom',
	'label_auteur_prenom' => 'Prénom',
	'label_auteur_societe' => 'Société',
	'label_auteur_adresse' => 'Adresse',
	'label_auteur_adresse_cp' => 'Code Postal',
	'label_auteur_adresse_ville' => 'Ville',
	'label_auteur_adresse_pays' => 'Pays',
	'label_auteur_tel_fixe' => 'Téléphone fixe',
	'label_auteur_tel_mobile' => 'Téléphone mobile',
	'label_inscription_adresse_email' => 'Votre adresse email&nbsp;:',
	'label_partenaire_1' => 'Compte partenaire',
	'label_avatar' => 'Nouvelle photo',
	'label_votre_avatar' => 'Votre photo',
	'info_partenaire' => 'Compte partenaire',
	'info_adresse' => 'Adresse :',
	'info_societe' => 'Société',
	'info_prenom' => 'Prénom',
	'info_adresse_bp' => 'BP',
	'info_adresse_ville' => 'Ville',
	'info_adresse_pays' => 'Pays',

	'form_inscription_voici1' => 'Voici vos identifiants pour vous connecter plus tard sur @nom_site_spip@.
(Votre compte est accessible à l\'adresse @adresse_login@ ).',

	'signature_equipe' => 'L\'équipe de @nom_site_spip@',

	'message_souscription_info_creation_profil' => 'Un compte vous a été créé automatiquement. Vous allez recevoir à l\'adresse @email@ les informations pour y accèder.',
	'message_souscription_info_deja_profil' => 'Cette souscription a été associée à votre compte déjà existant.',

	'message_confirmation_paiement_don' => '<br /><br />Vous pouvez retrouver tous vos dons et re&ccedil;us fiscaux dans <a href="@url@">votre espace lecteur</a>.',
	'message_confirmation_paiement_adhesion' => '<br /><br />Vous pouvez retrouver les informations concernant votre adhésion dans <a href="@url@">votre espace adhérent</a>.',


	'bouton_acces_page_profil' => 'Accès à mon compte',
	'bouton_deconnexion' => 'Déconnexion',
	'bouton_modifier_profil' => 'Modifier vos informations',
	'bouton_fermer' => 'Fermer',
	'bouton_newsletter_unsubscribe' => '<i class="icon-remove icon-white"></i> Se désinscrire',
	'bouton_newsletter_subscribe' => 'S\'inscrire à la newsletter',

	'titre_commentaire' => 'Commentaires',
	'titre_avatar' => 'Photo',
	'titre_inviter_ami_newsletter' => 'Inviter un ami à lire la Newsletter',
	'titre_mot_de_passe' => 'Changement mot de passe',
	'titre_newsletter' => 'Newsletter',
	'titre_page_profil' => 'Mon compte',
	'titre_page_profil_connexion' => 'Connexion à mon compte',
	'titre_profil' => 'Vos informations',
	'explications_commentaire' => 'Vos derniers commentaires',
	'explications_inviter_ami_newsletter'=> 'Faites découvrir <b>@nom_site@</b> à vos amis : envoyez-leur une invitation à s\'inscrire à la newsletter.',
	'explications_mot_de_passe' => 'Il est possible de changer votre mot de passe ici.',
	'explications_newsletter' => "Les dernières lettres d'information.",
	'explications_profil' => 'Vous pouvez modifier votre courriel, votre mot de passe et vos coordonnées.',
	'explication_privacy_info_perso' => 'Ces coordonnées sont uniquement utilisées pour gérer les fonctionnalités de cet espace, et ne seront jamais communiquées à des tiers. Nous ne pratiquons ni l\'achat, ni la vente, ni l\'échange de fichiers.',
	'info_1_nouveau_commentaire' => '1 nouveau commentaire depuis',
	'info_nb_nouveaux_commentaires' => '@nb@ nouveaux commentaires',
	'votre_commentaire_minus' => 'Votre commentaire',
	'vos_nb_commentaires_minus' => 'Vos @nb@ commentaires',

	'bouton_pas_de_compte' => 'Je n\'ai pas encore de compte',
	'bouton_deja_un_compte' => 'J\'ai déjà un compte',
	'explication_compte' => 'Cet espace vous permet de gérer votre compte, vos inscriptions newsletter, et votre abonnement.',

);

/*
if (!defined('_ECRIRE_INC_VERSION')) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(

	'bouton_favori_remove' => '<i class="icon-remove icon-white"></i> Retirer',
	'bouton_confirmer_email' => 'Confirmer mon adresse mail',

	'confirmsubscribe_invite_texte_email_1' => '@invite_email_from@ vous invite à vous inscrire à la Newsletter de @nom_site_spip@ avec l\'adresse email @email@. Voici son message :',
	'confirmsubscribe_texte_email_2' => 'Pour confirmer votre inscription et recevoir les derniers articles de Basta !, toutes les deux semaines, merci de cliquer sur le lien suivant :
@url_confirmsubscribe@',

	'defaut_message_invite_email_subscribe' => 'Bonjour, je vous invite à découvrir @nom_site_spip@, journal en ligne sur les questions sociales et environnementales.',
	'recommander' => 'Poster',
	'forum_texte' => 'Texte de votre message <span>(4000 signes maximum)</span>',

	'titre_don' => 'Dons',
	'titre_favoris' => 'Favoris',

	'explications_don' => 'Historique de vos dons.',
	'explications_favoris'=> 'Un article vous intéresse, mais vous n\'avez pas le temps de le lire maintenant ? Cliquez sur le petit cœur, en haut de l\'article, et retrouvez-le dans votre interface à tout moment.',

	'info_faire_un_don' => 'Faire un don',
	'info_telecharger_les_recus_fiscaux' => 'Télécharger les reçus fiscaux :',
	'info_telecharger_le_recu_fiscal' => 'Télécharger le reçu fiscal',

	'label_resilier_abonnement' => 'X Résilier mon soutien mensuel',
	'label_avancement_campagne_dons' => 'Avancement de la campagne : <b>@montant@</b> € sur <b>@objectif@ €</b> !',

	'login_login2' => 'Adresse email :',

	'message_souscription_info_creation_profil' => 'Un compte lecteur vous a été créé. Vous allez recevoir votre mot de passe à l\'adresse <b>@email@</b>.',

	'pass_vousinscrire' => 'Créer votre espace lecteur',
	'pass_forum_bla' => 'Votre espace lecteur vous permet de participer aux forums, de selectionner vos articles favoris pour les lire au calme et de gérer votre abonnement à la newsletter.',

	'texte_confirmer_resilier_abonnement' => 'Êtes vous sûr de vouloir résilier votre soutien mensuel ?',


	// souscription
	'explication_bloc_fiscal_don' => 'Ces informations sont nécessaires pour établir votre reçu fiscal, qui sera disponible en début d\'année prochaine.',
	'label_envoyer_info' => 'Je souhaite recevoir la newsletter bimensuelle',
);
*/
?>
