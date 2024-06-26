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
 * Verifier si un compte existe deja ou le creer si besoin
 * a partir des infos venant d'une source externe (autre table en general)
 * @param string $source
 *   nom de la source, qui sert a trouver le modele de mail de notification
 * @param array $champs
 *   doit contenir au moins une entree email, et nom et prenom si possible
 * @param bool $notifier
 * @return string
 */
function profils_verifier_ou_creer_auteur_depuis_source($source, $champs,$notifier = true){
	$id_auteur = 0;
	$message = "";

	if (!isset($champs['email']) or !$champs['email']) {
		return array(0, _T('profils:message_info_erreur_profil'));
	}

	$email = $champs['email'];
	if (isset($champs['id_auteur'])) {
		$id_auteur = $champs['id_auteur'];
	}

	// est-ce que l'auteur existe bien ?
	// $id_auteur == -1 pour ne pas creer d'auteur
	if ($id_auteur>0 AND !sql_countsel('spip_auteurs','id_auteur='.intval($id_auteur))){
		$id_auteur = 0;
	}

	// si pas d'id_auteur deja connu
	if (!$id_auteur){

		// cet auteur existe deja ?
		if ($row = sql_fetsel("*","spip_auteurs","email=".sql_quote($email)." AND statut<>".sql_quote("5poubelle"))){
			$id_auteur = $row['id_auteur'];
			$message = _T('profils:message_info_deja_profil',array('email' => $email));
		}
		else {
			if ($id_auteur = profils_creer_depuis_source($source, $champs, $notifier)){
				$message = _T('profils:message_info_creation_profil',array('email' => $email));
			}
		}

	} else {
		$message = _T('profils:message_info_deja_profil',array('email' => $email));
	}


	return array($id_auteur, $message);
}


/**
 * Creer un profil depuis une autre table source
 * @param string $source
 *   nom de la source, qui sert a trouver le modele de mail de notification
 * @param array $champs
 *   doit contenir au moins une entree email, et nom et prenom si possible
 * @param bool $notifier
 * @return bool|int
 */
function profils_creer_depuis_source($source, $champs, $notifier=true){

	if (!isset($champs['email']) or !$champs['email']) {
		return false;
	}

	$set = array(
		'email' => $champs['email'],
		'login' => isset($champs['login'])?$champs['login']:$champs['email'],
		'name' => isset($champs['nom'])?$champs['nom']:'',
		'prenom' => isset($champs['prenom'])?$champs['prenom']:'',
	);

	if (!$set['prenom'] AND !$set['name']){
		$nom = explode('@',$set['email']);
		$set['prenom'] = reset($nom);
	}
	$set['nom'] = trim($set['prenom'].' '.$set['name']);


	if (isset($champs['date'])){
		$set['date_inscription'] = $champs['date'];
	}
	else {
		$set['date_inscription'] = date('Y-m-d H:i:s');
	}

	// on note l'IP sauf si l'operation est realisee par qqun deja connecte
	if (!isset($GLOBALS['visiteur_session']['id_auteur'])
		or !$GLOBALS['visiteur_session']['id_auteur']){
		$set['ip_inscription'] = $GLOBALS['ip'];
	}

	if (!$row = profils_creer_auteur($set)) {
		return false;
	}

	$id_auteur = $row['id_auteur'];

	if ($notifier
	  and trouver_fond($fond = 'modeles/mail_creation_profil_'.$source)){
		// envoyer l'email avec login/pass
		$contexte = array(
			'id_auteur' => $id_auteur,
			'nom' => $row['prenom']?$row['prenom']:$row['nom'],
			'email' => $row['email'],
			'pass' => $row['pass'],
		);
		// on merge avec les champs fournit en appel, qui sont passes au modele de notification donc
		$contexte = array_merge($champs, $contexte);
		$message = recuperer_fond($fond, $contexte);
		include_spip("inc/notifications");
		notifications_envoyer_mails($row['email'],$message);
	}

	return $id_auteur;
}

/**
 * Pour compatibilite : depuis mailsubscriber
 * @param $champs
 * @param bool $notifier
 * @return bool|int
 */
function profils_creer_depuis_mailsubscriber($champs, $notifier=true){
	return profils_creer_depuis_source('mailsubscriber', $champs, $notifier);
}


/**
 * Creer un auteur lors d'une souscription si besoin
 * et lui envoyer un mail avec un compte auteur
 *
 * @param array $champs
 * @param bool $notifier
 * @return int
 */
function profils_creer_depuis_souscription($champs, $notifier=true){
	$id_auteur = 0;

	if (isset($champs['cadeau'])
		AND $champs['cadeau']
	  AND $cadeau = unserialize($champs['cadeau'])){
		$set = array(
			'email' => $cadeau['courriel'],
			'login' => $cadeau['courriel'],
			'name' => $cadeau['nom'],
			'prenom' => $cadeau['prenom'],
		);
	}
	else {
		$set = array(
			'email' => $champs['courriel'],
			'login' => $champs['courriel'],
			'name' => $champs['nom'],
			'prenom' => $champs['prenom'],
			'adresse' => $champs['adresse'],
			'adresse_cp' => $champs['code_postal'],
			'adresse_ville' => $champs['ville'],
			'adresse_pays' => $champs['pays'],
			'tel_fixe' => $champs['telephone'],
		);
	}

	if (isset($champs['date_souscription'])){
		$set['date_inscription'] = $champs['date_souscription'];
	}
	else {
		$set['date_inscription'] = date('Y-m-d H:i:s');
	}
	if (!isset($GLOBALS['visiteur_session']['id_auteur']) OR !$GLOBALS['visiteur_session']['id_auteur']){
		$set['ip_inscription'] = $GLOBALS['ip'];
	}


	if (!$set['prenom'] AND !$set['name']){
		$nom = explode('@',$set['email']);
		$set['prenom'] = reset($nom);
	}
	$set['nom'] = trim($set['prenom'].' '.$set['name']);

	if (!$row = profils_creer_auteur($set))
		return 0;

	$id_auteur = $row['id_auteur'];

	if ($notifier){
		$type = (isset($champs['type_souscription'])?$champs['type_souscription']:'don');
		// envoyer l'email avec login/pass
		$contexte = array(
			'nom' => $row['prenom']?$row['prenom']:$row['nom'],
			'email' => $row['email'],
			'pass' => $row['pass'],
			'cadeau' => (isset($cadeau) AND $cadeau)?' ':'',
		);
		if ($contexte['cadeau']){
			$contexte['cadeau_from'] = array(
				"nom" => $champs['nom'],
				"prenom" => $champs['prenom'],
				"email" => $champs['courriel'],
			);
		}
		$contexte['id_auteur'] = $id_auteur;
		$message = recuperer_fond('modeles/mail_creation_profil_'.$type,$contexte);
		include_spip("inc/notifications");
		notifications_envoyer_mails($row['email'],$message);
	}

	// rattraper les anciennes souscriptions avec cet email et id_auteur=0
	// (historique, ou dons uniques sans recu fiscal demande)
	sql_updateq("spip_souscriptions",array('id_auteur'=>$id_auteur),"id_auteur=0 AND courriel=".sql_quote($row['email']));
	// rattraper les anciennes transactions avec cet email et id_auteur=0
	// (historique, ou dons uniques sans recu fiscal demande)
	// sql_updateq("spip_transactions",array('id_auteur'=>$id_auteur),"id_auteur=0 AND auteur=".sql_quote($row['email']));

	return $id_auteur;
}

/**
 * Creer un auteur et lui generer un mot de passe
 * @param $set
 * @return array|bool
 */
function profils_creer_auteur($set){
	include_spip("action/editer_auteur");
	include_spip('inc/acces');
	$id_auteur = auteur_inserer();

	spip_log($id_auteur,'profils');
	spip_log($set,'profils');

	if ($id_auteur){
		$set['pass'] = creer_pass_aleatoire();
		$set['statut'] = '6forum';

		autoriser_exception('modifier','auteur',$id_auteur);
		autoriser_exception('instituer','auteur',$id_auteur);
		auteur_modifier($id_auteur,$set);
		autoriser_exception('modifier','auteur',$id_auteur,false);
		autoriser_exception('instituer','auteur',$id_auteur,false);

		// verifier
		$row = sql_fetsel('*','spip_auteurs','id_auteur='.intval($id_auteur));
		if (!$row['login'] OR !$row['email']){
			spip_log("Erreur creation profil $id_auteur ".var_export($set,true),"profils"._LOG_ERREUR);
			return false;
		}

		$row['pass'] = $set['pass'];
		return $row;
	}

	return false;
}
