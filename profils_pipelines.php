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
	  AND $GLOBALS['visiteur_session']['id_auteur']){

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
 * @param $id_souscription
 * @param $champs
 * @param bool $notifier
 * @return mixed|string
 */
function profils_verifier_auteur_souscription($id_souscription,&$champs,$notifier = true){
	$id_auteur = 0;
	$souscription = sql_fetsel("*","spip_souscriptions","id_souscription=".intval($id_souscription));
	$message = "";
	$cadeau = false;

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

	// si pas d'id_auteur deja connu pour la souscription
	if (!$id_auteur AND $email){

		// cet auteur existe deja ?
		if ($row = sql_fetsel("*","spip_auteurs","email=".sql_quote($email)." AND statut<>".sql_quote("5poub"))){
			$id_auteur = $row['id_auteur'];
			$message = _T('profils:message_souscription_info_deja_profil',array('email' => $email));
		}
		else {
			include_spip("inc/profils");
			if ($cadeau AND !isset($champs['cadeau']))
				$champs['cadeau'] = serialize($cadeau);
			if ($id_auteur = profils_creer_depuis_souscription($souscription,$notifier)){
				$message = _T('profils:message_souscription_info_creation_profil',array('email' => $email));
			}
		}
		if ($id_auteur){
			if ($cadeau){
				$cadeau['id_auteur'] = $id_auteur;
				$champs['cadeau'] = serialize($cadeau);
				// si jamais l'auteur est connu, on lui attribue la souscription, c'est mieux
				if ($id2 = sql_getfetsel("id_auteur","spip_auteurs","email=".sql_quote($champs['courriel'])." AND statut<>".sql_quote("5poub"))){
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


	if ($flux['args']['table']=='spip_mailsubscribers'
		AND $id_mailsubscriber = $flux['args']['id_objet']
		AND $flux['args']['action']=='instituer'
	  AND isset($flux['data']['statut'])
		AND $flux['data']['statut']=='valide'
		AND $flux['args']['statut_ancien']!=='valide'){

		include_spip("inc/config");
		if (lire_config("profils/creer_depuis_mailsubscriber","non")=='oui'){
			if ($row = sql_fetsel("*","spip_mailsubscribers","id_mailsubscriber=".intval($id_mailsubscriber))){
				if (!sql_fetsel("*","spip_auteurs","email=".sql_quote($row['email'])." AND statut<>".sql_quote("5poub"))){
					include_spip("inc/profils");
					$id_auteur = profils_creer_depuis_mailsubscriber($row,$notifier);
				}
			}
		}

	}

	return $flux;
}


function profils_bank_traiter_reglement($flux){

	$souscription = sql_fetsel("*", "spip_souscriptions", 'id_transaction_echeance=' . intval($flux['args']['id_transaction']));
	$transaction = sql_fetsel("*", "spip_transactions", 'id_transaction=' . intval($flux['args']['id_transaction']));
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
