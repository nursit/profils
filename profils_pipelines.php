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

		if (isset($GLOBALS['visiteur_session']['adresse_1'])){
			$adresse = $GLOBALS['visiteur_session']['adresse_1']
				. "\n" . $GLOBALS['visiteur_session']['adresse_2']
				. "\n" . $GLOBALS['visiteur_session']['adresse_bp'];
			$adresse = explode("\n",$adresse);
			$adresse = array_filter($adresse);
			$adresse = trim(implode("\n",$adresse));
			if ($adresse)
				$flux['data']['adresse'] = $adresse;
		}
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
				//$GLOBALS['message_ok_souscription_'.$id_souscription] = _L('Votre souscription ')
				$GLOBALS['message_ok_souscription_'.$id_souscription] = _T('profils:message_souscription_info_deja_profil',array('email' => $email));
			}
			else {
				if ($id_auteur = profils_creer_depuis_souscription($flux['data'])){
					$GLOBALS['message_ok_souscription_'.$id_souscription] = _T('profils:message_souscription_info_creation_profil',array('email' => $email));
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

/**
 * Creer un auteur lors d'une souscription si besoin
 * et lui envoyer un mail avec un compte auteur
 *
 * @param array $champs
 * @return int
 */
function profils_creer_depuis_souscription($champs){
	$id_auteur = 0;

	// si don unique et pas recu fiscal demande,
	// on ne cree pas de profil
	// (eviter le fichage inutile)
	if ($champs['recu_fiscal']=='off' AND !isset($champs['abo_statut']))
		return $id_auteur;

	include_spip("action/editer_auteur");
	include_spip('inc/acces');
	$id_auteur = auteur_inserer();

	$pass = creer_pass_aleatoire();
	$set = array(
		'email' => $champs['courriel'],
		'login' => $champs['courriel'],
		'name' => $champs['nom'],
		'prenom' => $champs['prenom'],
		'adresse_cp' => $champs['code_postal'],
		'adresse_ville' => $champs['ville'],
		'adresse_pays' => $champs['pays'],
		'tel_fixe' => $champs['telephone'],
		'statut' => '6forum',
		'pass' => $pass,
	);

	$adresse = explode("\n",$champs['adresse']);
	$adresse = array_filter($adresse);
	if (count($adresse))
		$set['adresse_1'] = array_shift($adresse);
	if (count($adresse))
		$set['adresse_2'] = array_shift($adresse);
	if (count($adresse))
		$set['adresse_bp'] = implode(' ',$adresse);

	if (!$set['prenom'] AND !$set['name']){
		$nom = explode('@',$set['email']);
		$set['prenom'] = reset($nom);
	}
	$set['nom'] = trim($set['prenom'].' '.$set['name']);

	spip_log($id_auteur,'profils');
	spip_log($set,'profils');

	autoriser_exception('modifier','auteur',$id_auteur);
	autoriser_exception('instituer','auteur',$id_auteur);
	auteur_modifier($id_auteur,$set);
	autoriser_exception('modifier','auteur',$id_auteur,false);
	autoriser_exception('instituer','auteur',$id_auteur,false);

	// verifier
	$row = sql_fetsel('*','spip_auteurs','id_auteur='.intval($id_auteur));
	if (!$row['login'] OR !$row['email']){
		spip_log("Erreur creation profil $id_auteur ".var_export($set,true),"profils"._LOG_ERREUR);
		return 0;
	}

	// envoyer l'email avec login/pass
	$contexte = array(
		'nom' => $set['prenom']?$set['prenom']:$set['nom'],
		'email' => $set['email'],
		'pass' => $set['pass'],
	);
	$message = recuperer_fond('modeles/mail_creation_profil',$contexte);
	include_spip("inc/notifications");
	notifications_envoyer_mails($set['email'],$message);


	// rattraper les anciennes souscriptions avec cet email et id_auteur=0
	// (historique, ou dons uniques sans recu fiscal demande)
	sql_updateq("spip_souscriptions",array('id_auteur'=>$id_auteur),"id_auteur=0 AND courriel=".sql_quote($champs['courriel']));

	return $id_auteur;
}