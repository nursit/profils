<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;

function action_usurper_profil_dist(){

	$securiser_action = charger_fonction("securiser_action","inc");
	$id_auteur = $securiser_action();

	if (isset($GLOBALS['visiteur_session']['id_auteur'])
		AND intval($GLOBALS['visiteur_session']['id_auteur'])
		AND isset($GLOBALS['visiteur_session']['email'])
		AND include_spip("inc/autoriser")
		AND autoriser('webmestre')
	  AND $auteur = sql_fetsel("*","spip_auteurs","id_auteur=".intval($id_auteur))
	  AND $auteur['statut']=='6forum'){


		// securite : on envoi un mail au webmestre qui utilise la fonction, pour prevenir toute utilisation abusive
		$envoyer_mail = charger_fonction("envoyer_mail","inc");
		$envoyer_mail($GLOBALS['visiteur_session']['email'],
			"[".$GLOBALS['meta']['nom_site']."] SECURITE : Connexion avec Profil client",
			"Vous vous etes connecte avec le compte ci-dessous.
Si ce n'est pas vous qui avez fait cette operation avertissez tout de suite le support technique pour des raisons de sécurité
".var_export(array('nom'=>$auteur['name'],'prenom'=>$auteur['prenom'],'email'=>$auteur['email']),true));

		include_spip('inc/auth');
		/*
		auth_trace($GLOBALS['visiteur_session'], '0000-00-00 00:00:00');
		// le logout explicite vaut destruction de toutes les sessions
		if (isset($_COOKIE['spip_session'])) {
			$session = charger_fonction('session', 'inc');
			$session($GLOBALS['visiteur_session']['id_auteur']);
			spip_setcookie('spip_session', $_COOKIE['spip_session'], time()-3600);
		}*/

		auth_loger($auteur);
	}

}
