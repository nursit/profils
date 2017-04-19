<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * @param $flux
 * @return mixed
 */
function profils_affiche_milieu($flux){
	if (isset($flux['args']['exec'])
	  AND $flux['args']['exec']=='auteur'
	  AND $id_auteur = $flux['args']['id_auteur']){

		// si c'est bien un profil 6forum
		if ($statut = sql_getfetsel("statut","spip_auteurs","id_auteur=".intval($id_auteur)." AND statut=".sql_quote("6forum"))){
			$ins = recuperer_fond('prive/squelettes/inclure/profil',$flux['args']);
			$flux['data'] .= $ins;
		}
	}
	return $flux;
}

/**
 * Enrichir le formulaire editer_auteur avec les champs du profil
 * @param $flux
 * @return mixed
 */
function profils_formulaire_fond($flux){
	if ($flux['args']['form'] == 'editer_auteur'){
		if ($p = strpos($flux['data'],'<!--extra-->')){
			$complement = recuperer_fond('formulaires/inc-saisie-profil-profil',$flux['args']['contexte']);
			$flux['data'] = substr_replace($flux['data'],$complement,$p,0);
		}
	}
	return $flux;
}

function profils_boite_infos($flux){
	if ($flux['args']['type']=='auteur'
	  AND $id_auteur = $flux['args']['id']
	  AND include_spip('inc/autoriser')
		AND autoriser('webmestre')){

		// on peut s'autologer a la place d'un visiteur
		if ($statut = sql_getfetsel("statut","spip_auteurs","id_auteur=".intval($id_auteur)." AND statut=".sql_quote("6forum"))){
			include_spip('inc/actions');
			$bouton = bouton_action("Se connecter avec son compte",generer_action_auteur("usurper_profil","$id_auteur",generer_url_public("profil","","",false)));
			$flux['data'] .= $bouton;
		}
	}
	return $flux;
}

/**
 * Pre-charger les infos profils (nom, adresse, tel) dans le formulaire souscription
 * si le visiteur est loge
 *
 * @param array $flux
 * @return array
 */
function profils_formulaire_charger($flux){
	if (!isset($GLOBALS['souscription_forms']))
		$GLOBALS['souscription_forms'] = array('souscription');

	if (in_array($flux['args']['form'],$GLOBALS['souscription_forms'])
		AND !test_espace_prive()
	  AND isset($GLOBALS['visiteur_session']['id_auteur'])
	  AND $GLOBALS['visiteur_session']['id_auteur']
	  AND is_array($flux['data'])){

		$flux['data']['recu_fiscal'] = 'on';
		if (isset($GLOBALS['visiteur_session']['name']) AND $GLOBALS['visiteur_session']['name'])
			$flux['data']['nom'] = $GLOBALS['visiteur_session']['name'];
		if (isset($GLOBALS['visiteur_session']['prenom']) AND $GLOBALS['visiteur_session']['prenom'])
			$flux['data']['prenom'] = $GLOBALS['visiteur_session']['prenom'];
		if (isset($GLOBALS['visiteur_session']['adresse']) AND $GLOBALS['visiteur_session']['adresse'])
			$flux['data']['adresse'] = $GLOBALS['visiteur_session']['adresse'];
		if (isset($GLOBALS['visiteur_session']['adresse_cp']) AND $GLOBALS['visiteur_session']['adresse_cp'])
			$flux['data']['code_postal'] = $GLOBALS['visiteur_session']['adresse_cp'];
		if (isset($GLOBALS['visiteur_session']['adresse_ville']) AND $GLOBALS['visiteur_session']['adresse_ville'])
			$flux['data']['ville'] = $GLOBALS['visiteur_session']['adresse_ville'];
		if (isset($GLOBALS['visiteur_session']['adresse_pays']) AND $GLOBALS['visiteur_session']['adresse_pays'])
			$flux['data']['pays'] = $GLOBALS['visiteur_session']['adresse_pays'];

		if (isset($GLOBALS['visiteur_session']['tel_fixe']) AND $GLOBALS['visiteur_session']['tel_fixe'])
			$flux['data']['telephone'] = $GLOBALS['visiteur_session']['tel_fixe'];
		elseif (isset($GLOBALS['visiteur_session']['tel_mobile']) AND $GLOBALS['visiteur_session']['tel_mobile'])
			$flux['data']['telephone'] = $GLOBALS['visiteur_session']['tel_mobile'];
	}
	return $flux;
}

/**
 * Generer un nouveau mot de passe et envoyer le mail
 * @param $flux
 * @return mixed
 */
function profils_formulaire_verifier($flux){
$flux['data']['message_erreur']=' ';
	if ($flux['args']['form'] == 'editer_auteur'
	  and $id_auteur = $flux['args']['args'][0]
	  and _request('reset_password')){

		$auteur = sql_fetsel('*','spip_auteurs','id_auteur='.intval($id_auteur));
		$config = auteurs_edit_config($auteur);
		if ($config['edit_pass']) {
			include_spip('inc/profils');
			// on vide toutes les erreurs eventuelles car on ne submit rien en vrai
			$flux['data'] = array();
			if ($email = profils_regenerer_identifiants($id_auteur)) {
				$flux['data']['message_ok'] = _T('profils:message_nouveaux_identifiants_ok',array('email'=>$email));
				$flux['data']['message_erreur'] = '';
			}
			elseif($email===false) {
				$flux['data']['message_erreur'] = _T('profils:message_nouveaux_identifiants_echec_envoi');
			}
			else {
				$flux['data']['message_erreur'] = _T('profils:message_nouveaux_identifiants_echec_creation');
			}
		}
		else {
			$flux['data']['message_erreur'] = _T('profils:message_nouveaux_identifiants_echec_creation');
		}
	}

	return $flux;
}


/**
 * Ajouter le message OK concernant la creation du profil a la volee
 * @param $flux
 * @return mixed
 */
function profils_formulaire_traiter($flux){
	if (!isset($GLOBALS['souscription_forms']))
		$GLOBALS['souscription_forms'] = array('souscription');

	if (in_array($flux['args']['form'],$GLOBALS['souscription_forms'])
		AND $id_souscription = $flux['data']['id_souscription']
		AND isset($GLOBALS['message_ok_souscription_'.$id_souscription])){
		$flux['data']['message_ok'] .= "<br />" . $GLOBALS['message_ok_souscription_'.$id_souscription];
	}
	return $flux;
}

/**
 * Creation du profil a la volee lors de la souscription
 * @param $flux
 * @return mixed
 */
function profils_pre_edition($flux){
	// quand un auteur change d'email, noter le changement
	// pour actualiser ses abonnements mailsubscribers si besoin dans post_edition
	if ($flux['args']['type']=='auteur'
		AND $id_auteur= $flux['args']['id_objet']
	  AND isset($flux['data']['email'])){
		$auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
		if ($flux['data']['email']!==$auteur['email']){
			$GLOBALS['email_changed'][$flux['data']['email']] = $auteur['email'];
		}
	}

	/*
	if ($flux['args']['type']=='souscription'
		AND $id_souscription = $flux['args']['id_objet']
	  AND isset($flux['data']['courriel'])
	  AND $email = $flux['data']['courriel']){

		// don ou adhesion ?
		if (isset($flux['data']['type_souscription']))
			$type = $flux['data']['type_souscription'];
		else
			$type = sql_getfetsel("type_souscription","spip_souscriptions","id_souscription=".intval($id_souscription));

		include_spip("inc/config");
		if (
		     ($type=="don" AND lire_config("profils/creer_depuis_souscription_don","non")=='oui')
		  OR ($type=="adhesion" AND lire_config("profils/creer_depuis_souscription_adhesion","oui")=='oui') ){

			if ($message = profils_verifier_auteur_souscription($id_souscription,$flux['data'])){
				// pas de message confusant dans le processus de souscription
				//$GLOBALS['message_ok_souscription_'.$id_souscription] = $message;
			}
		}
	}
	*/
	return $flux;
}

/**
 * Verifier/creer l'auteur d'une souscription
 * @deprecated
 * @param $id_souscription
 * @param $champs
 * @param bool $notifier
 * @return mixed|string
 */
function profils_verifier_auteur_souscription($id_souscription,&$champs,$notifier = true){
	$id_auteur = 0;
	$message = "";
	$cadeau = false;

	if (!$souscription = sql_fetsel("*","spip_souscriptions","id_souscription=".intval($id_souscription))){
		return "";
	}


	$souscription_m = array_merge($souscription,$champs);
	// attention si c'est un cadeau prendre l'email du destinataire du cadeau
	// et l'auteur eventuellement deja cree pour lui
	if (isset($souscription_m['cadeau'])
		AND $cadeau = $souscription_m['cadeau']
	  AND $cadeau = unserialize($cadeau)){
		$email = $cadeau['courriel'];
		$id_auteur = (isset($cadeau['id_auteur'])?$cadeau['id_auteur']:0);
	}
	else {
		$email = $souscription_m['courriel'];
		if (isset($champs['id_auteur']) AND $champs['id_auteur'])
			$id_auteur = $champs['id_auteur'];
		if (!$id_auteur)
			$id_auteur = $souscription['id_auteur'];
	}

	// est-ce que l'auteur existe bien ?
	// $id_auteur == -1 pour ne pas creer d'auteur
	if ($id_auteur>0 AND !sql_countsel('spip_auteurs','id_auteur='.intval($id_auteur))){
		$id_auteur = 0;
	}

	// si pas d'id_auteur deja connu pour la souscription
	if (!$id_auteur AND $email){

		// cet auteur existe deja ?
		if ($row = sql_fetsel("*","spip_auteurs","email=".sql_quote($email)." AND statut<>".sql_quote("5poubelle"))){
			$id_auteur = $row['id_auteur'];
			$message = _T('profils:message_info_creation_profil',array('email' => $email));
		}
		else {
			include_spip("inc/profils");
			if ($cadeau AND !isset($champs['cadeau']))
				$champs['cadeau'] = serialize($cadeau);
			if ($id_auteur = profils_creer_depuis_souscription($souscription,$notifier)){
				$message = _T('profils:message_info_deja_profil',array('email' => $email));
			}
		}
		if ($id_auteur){
			if ($cadeau){
				$cadeau['id_auteur'] = $id_auteur;
				$champs['cadeau'] = serialize($cadeau);
				// si jamais l'auteur de la souscription est connu, on lui attribue la souscription, c'est mieux
				if ( ($email2 = $souscription_m['courriel'] OR $email2=$souscription['courriel'])
				  AND $id2 = sql_getfetsel("id_auteur","spip_auteurs","email=".sql_quote($email2)." AND statut<>".sql_quote("5poubelle"))){
					$champs['id_auteur'] = $id2;
				}
			}
			else {
				$champs['id_auteur'] = $id_auteur;
				// doit on le loger ? oui si pas d'historique de souscription (a confirmer)
				if (!sql_countsel("spip_souscriptions","id_auteur=".intval($id_auteur)." AND id_souscription<>".intval($id_souscription))){
					// TODO : loger l'auteur ?
				}
			}
		}
	}

	return $message;
}


/**
 * Creation du profil a la volee lors de l'inscription a la newsletter
 * @param $flux
 * @return mixed
 */
function profils_post_edition($flux){

	$notifier=true;
	//ne pas envoyer de notif par exemple lors d'une inscription en masse a une newsletter
	if (isset($GLOBALS['notification_instituermailsubscriber_status']) AND !$GLOBALS['notification_instituermailsubscriber_status'])
		$notifier = false;

	// quand un auteur change d'email, noter le changement
	// pour actualiser ses abonnements mailsubscribers si besoin dans post_edition
	if ($flux['args']['table']=='spip_auteurs'
		AND $id_auteur= $flux['args']['id_objet']
	  AND isset($flux['data']['email'])
	  AND isset($GLOBALS['email_changed'][$flux['data']['email']])
	  AND $old_email = $GLOBALS['email_changed'][$flux['data']['email']]){

		if (test_plugin_actif("mailsubscribers")){
			sql_updateq("spip_mailsubscribers", array('email' => $flux['data']['email']), "email=" . sql_quote($old_email));
		}
	}


	if ($flux['args']['table']=='spip_mailsubscribers'
		AND $id_mailsubscriber = $flux['args']['id_objet']
		AND $flux['args']['action']=='instituer'
	  AND isset($flux['data']['statut'])
		AND $flux['data']['statut']=='valide'
		AND $flux['args']['statut_ancien']!=='valide'){

		include_spip("inc/config");
		if (lire_config("profils/creer_depuis_mailsubscriber","non")=='oui'){
			if ($row = sql_fetsel("*","spip_mailsubscribers","id_mailsubscriber=".intval($id_mailsubscriber))){
				if (!sql_fetsel("*","spip_auteurs","email=".sql_quote($row['email'])." AND statut<>".sql_quote("5poubelle"))){
					include_spip("inc/profils");
					$id_auteur = profils_creer_depuis_mailsubscriber($row,$notifier);
				}
			}
		}

	}

	return $flux;
}

/**
 * Reglements en attente ? on cree le profil id_auteur quand meme
 * @param array $flux
 * @return array
 */
function profils_trig_bank_reglement_en_attente($flux){
	include_spip("inc/config");

	if ($id_transaction=$flux['args']['id_transaction']
	  AND lire_config("profils/creer_sur_paiements_attente", "non")=='oui'){
		$res = profils_bank_pre_facturer_reglement($flux);
		$message = $res['data'];
		sql_updateq("spip_transactions",array('message'=>$message),"id_transaction=".intval($id_transaction));
	}

	return $flux;
}

function profils_bank_pre_facturer_reglement($flux){

	$transaction = sql_fetsel("*", "spip_transactions", 'id_transaction=' . intval($flux['args']['id_transaction']));
	$souscription = sql_fetsel("*", "spip_souscriptions", 'id_transaction_echeance=' . intval($flux['args']['id_transaction']));
	if (!$souscription
		AND $transaction['parrain']=='souscription'
		AND $id_souscription = $transaction['tracking_id']){
		$souscription = sql_fetsel("*", "spip_souscriptions", 'id_souscription=' . intval($id_souscription));
	}
	$set = array();
	include_spip("inc/config");
	if ($souscription
		AND $souscription['type_souscription']=='don'
		AND lire_config("profils/creer_depuis_souscription_don", "non")=='oui'
	){

		$message = profils_verifier_auteur_souscription($souscription['id_souscription'], $set);
		$souscription = array_merge($souscription, $set);

		if ($souscription['id_auteur']
			AND !(isset($souscription['cadeau']) AND $souscription['cadeau']) ){
			$flux['data'] .= _T('profils:message_confirmation_paiement_don', array('url' => generer_url_public("profil")));
		}
	}

	if ($souscription
		AND $souscription['type_souscription']=='adhesion'
		AND lire_config("profils/creer_depuis_souscription_adhesion", "oui")=='oui'
	){

		$message = profils_verifier_auteur_souscription($souscription['id_souscription'], $set);
		$souscription = array_merge($souscription, $set);

		if ($souscription['id_auteur']
			AND !(isset($souscription['cadeau']) AND $souscription['cadeau']) ){
			$flux['data'] .= _T('profils:message_confirmation_paiement_adhesion', array('url' => generer_url_public("profil")));
		}
	}

	if (count($set)){
		sql_updateq("spip_souscriptions",$set,"id_souscription=".intval($souscription['id_souscription']));
		if (!$transaction['id_auteur']){
			$set = array();
			// on recharge
			$souscription = sql_fetsel("*", "spip_souscriptions", 'id_souscription=' . intval($souscription['id_souscription']));
			if ($cadeau = $souscription['cadeau']
			  AND $cadeau = unserialize($cadeau)){
				if (isset($cadeau['id_auteur'])){
					$set['id_auteur'] = $cadeau['id_auteur'];
				}
			}
			elseif($souscription['id_auteur']){
				$set['id_auteur'] = $souscription['id_auteur'];
			}
			if ($set){
				sql_updateq("spip_transactions",$set,"id_transaction=".intval($flux['args']['id_transaction']));
			}
		}
	}

	return $flux;
}
