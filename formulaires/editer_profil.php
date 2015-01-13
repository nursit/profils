<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;


function formulaires_editer_profil_charger_dist($id_auteur){
	if (!$id_auteur
	  OR !$auteur = sql_fetsel('*','spip_auteurs','id_auteur='.intval($id_auteur)))
		return false;


	$valeurs = array('newsletter' => '');
	foreach(array('nom',
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

	$oblis = array('name',
	              'prenom',
	              'email',
	              #'adresse',
	              #'adresse_cp',
	              #'adresse_ville',
	              #'adresse_pays'
	);

	foreach($oblis as $obli)
		if (!strlen(_request($obli)))
			$erreurs[$obli] = _T('editer_profil:erreur_'.$obli.'_obligatoire');

	// Verifier l'email
	if (!isset($erreurs['email'])){
		$email = trim(_request('email'));
		if (!email_valide($email))
			$erreurs['email'] = _T('editer_profil:erreur_email_invalide');
		else {
			//...
		}
	}

    /* 15/09/2014  Pour le pseudo, on veut bien qu'au cas où le compte auteur existe, et
        des articles y sont associés, que la modification du pseudo dans
        l'espace lecteur soit interdite. */

    $nb_articles = sql_countsel('spip_auteurs_liens', 'id_auteur=' . intval($id_auteur) . " and objet='article'");
    if ($nb_articles > 0) {
        $nom_auteur = sql_fetsel('nom', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
        if (_request('nom')
            AND _request('nom') !== $nom_auteur
        ) {
            $erreurs['nom'] = _T('editer_profil:erreur_impossible_modifier_pseudo_auteur');
        }

    }
    
	return $erreurs;
}

function formulaires_editer_profil_traiter_dist($id_auteur){

	refuser_traiter_formulaire_ajax();
	$auteur = sql_fetsel('*','spip_auteurs','id_auteur='.intval($id_auteur));

	// si l'email change
	if (_request('email')
	  AND _request('email')!==$auteur['email']){

		// si c'�tait le login, changer aussi le login
		if ($auteur['email']==$auteur['login'])
			set_request('login',_request('email'));

	}

	include_spip('inc/editer');
	// renseigner le nom de la table auteurs (s'il n'a pas été renseigné)
    if (!_request('nom'))
        set_request('nom',_request('prenom').' '._request('name'));
	$res = formulaires_editer_objet_traiter('auteur',$id_auteur);

	// si l'email change
	if (_request('email')
	  AND _request('email')!==$auteur['email']){

		// securite si jamais la modif en base n'a pas eu lieu
		$new_email = sql_getfetsel('email','spip_auteurs','id_auteur='.intval($id_auteur));

		// updater les abonnements dans mailsubscribers
		sql_updateq("spip_mailsubscribers",array('email'=>$new_email),"email=".sql_quote($auteur['email']));
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
