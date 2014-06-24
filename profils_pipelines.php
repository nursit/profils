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

/**
 * Pre-charger les infos profils (nom, adresse, tel) dans le formulaire souscription
 * si le visiteur est loge
 *
 * @param array $flux
 * @return array
 */
function profils_formulaire_charger($flux){

	if ($flux['args']['form']=='souscription'
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
	if ($flux['args']['form']=='souscription'
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
	if ($flux['args']['type']=='souscription'
		AND $id_souscription = $flux['args']['id_objet']
	  AND isset($flux['data']['courriel'])
	  AND $email = $flux['data']['courriel']){
		$id_auteur = 0;
		if (isset($flux['data']['id_auteur']) AND $flux['data']['id_auteur'])
			$id_auteur = $flux['data']['id_auteur'];
		if (!$id_auteur)
			$id_auteur = sql_getfetsel("id_auteur","spip_souscriptions","id_souscription=".intval($id_souscription));

		// si pas d'id_auteur deja connu pour la souscription
		if (!$id_auteur){
			// cet auteur existe deja ?
			if ($row = sql_fetsel("*","spip_auteurs","email=".sql_quote($email)." AND statut<>".sql_quote("5poub"))){
				$id_auteur = $row['id_auteur'];
				// pas de message confusant dans le processus de don
				//$GLOBALS['message_ok_souscription_'.$id_souscription] = _T('profils:message_souscription_info_deja_profil',array('email' => $email));
			}
			else {
				include_spip("inc/profils");
				if ($id_auteur = profils_creer_depuis_souscription($flux['data'])){
					// pas de message confusant dans le processus de don, un mail est envoye
					// $GLOBALS['message_ok_souscription_'.$id_souscription] = _T('profils:message_souscription_info_creation_profil',array('email' => $email));
				}
			}
			if ($id_auteur){
				$flux['data']['id_auteur'] = $id_auteur;
				// doit on le loger ? oui si pas d'historique de souscription (a confirmer)
				if (!sql_countsel("spip_souscriptions","id_auteur=".intval($id_auteur)." AND id_souscription<>".intval($id_souscription))){
					// TODO : loger l'auteur ?
				}
			}
		}
	}
	return $flux;
}
