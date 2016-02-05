<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip("base/abstract_sql");

function formulaires_editer_profil_charger_dist($id_auteur){
	if (!$id_auteur
		OR !$auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur))
	)
		return false;


	$valeurs = array('newsletter' => '');
	foreach (array('nom',
		         'name',
		         'prenom',
		         'email',
		         'societe',
		         'adresse',
		         'adresse_cp',
		         'adresse_ville',
		         'adresse_pays',
		         'tel_fixe',
		         'tel_mobile') as $champ)
		$valeurs[$champ] = $auteur[$champ];

	// abonne a la newsletter ?
	/*
	if ($auteur['email']){
		$subscriber = charger_fonction("subscriber","newsletter");
		$infos = $subscriber($auteur['email']);
		if ($infos
			AND $infos['status']=='on'
			AND in_array('profils',$infos['listes']))
			$valeurs['newsletter'] = 1;
	}*/

	return $valeurs;
}


function formulaires_editer_profil_verifier_dist($id_auteur){
	$erreurs = array();
	$auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));

	$oblis = array('name',
		'prenom',
		'email',
		#'adresse',
		#'adresse_cp',
		#'adresse_ville',
		#'adresse_pays'
	);

	foreach ($oblis as $obli)
		if (!strlen(_request($obli)))
			$erreurs[$obli] = _T('editer_profil:erreur_' . $obli . '_obligatoire');

	// Verifier l'email
	if (!isset($erreurs['email'])){
		$email = trim(_request('email'));
		if (!email_valide($email)) {
			$erreurs['email'] = _T('editer_profil:erreur_email_invalide');
		}
		// si email=login verifier l'unicite
		elseif($auteur['email']==$auteur['login']) {
			if (sql_countsel("spip_auteurs","(email=".sql_quote($email)." OR login=".sql_quote($email).") AND id_auteur!=".intval($id_auteur))){
				$erreurs['email'] = _T('editer_profil:erreur_email_doublon');
			}
		}
	}

	/* On ne permet pas aux redacteurs/admin de modifier leur pseudo ici
	 si ils ont des articles publies car cela impacte leur signature */

	// 0minirezo ou 1comite
	if (intval($GLOBALS['visiteur_session']['statut'])<=1){

		$nb_articles = sql_countsel('spip_auteurs_liens AS L JOIN spip_articles as A ON (A.id_article=L.id_objet AND L.objet='.sql_quote('article').')', 'L.id_auteur=' . intval($id_auteur) . " AND L.objet='article' AND A.statut=".sql_quote('publie'));
		if ($nb_articles>0){
			if (_request('nom')
				AND _request('nom')!==$auteur['nom']
			){
				$erreurs['nom'] = _T('editer_profil:erreur_impossible_modifier_pseudo_auteur');
			}

		}
	}

	return $erreurs;
}

function formulaires_editer_profil_traiter_dist($id_auteur){

	refuser_traiter_formulaire_ajax();
	$auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));

	$new_email = "";
	// si l'email change
	if (_request('email')
		AND _request('email')!==$auteur['email']
	){

		$new_email = _request('email');

		// si c'etait le login, changer aussi le login
		if ($auteur['email']==$auteur['login'])
			set_request('login', _request('email'));

	}

	include_spip('inc/editer');
	// renseigner le nom de la table auteurs (s'il n'a pas été renseigné)
	if (!_request('nom'))
		set_request('nom', _request('prenom') . ' ' . _request('name'));
	$res = formulaires_editer_objet_traiter('auteur', $id_auteur);

	// si l'email change
	if ($new_email){

		// securite si jamais la modif en base n'a pas eu lieu
		$new_email = sql_getfetsel('email', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));

		// updater les abonnements dans mailsubscribers
		// si jamais il y a deja in subscriber sur le nouveau mail, ca ne fera rien, et l'ancien email continuera a recevoir
		// a charge pour l'utilisateur de se desabonner manuellement sur l'ancien
		if (test_plugin_actif("mailsubscribers")) {
			sql_updateq("spip_mailsubscribers", array('email' => $new_email), "email=" . sql_quote($auteur['email']));
		}
	}

	/*
	if ($email = _request('email')){

	  if (_request('newsletter')){
			$subscribe = charger_fonction("subscribe","newsletter");
			$subscribe($email,array('nom'=>_request('nom'),'listes'=>array('profils'),'force'=>true));
	  }
		else {
			$unsubscribe = charger_fonction("unsubscribe","newsletter");
			$unsubscribe($email,array('listes'=>array('profils')));
		}
		set_request('newsletter');
	}
	*/


	if (isset($res['message_ok'])){
		$res['message_ok'] = _T('editer_profil:message_ok_profil_modifie');
		$res['editable'] = true;
	}
	return $res;
}
