<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;


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
		$message = recuperer_fond('modeles/mail_creation_profil_'.$type,$contexte);
		include_spip("inc/notifications");
		notifications_envoyer_mails($row['email'],$message);
	}

	// rattraper les anciennes souscriptions avec cet email et id_auteur=0
	// (historique, ou dons uniques sans recu fiscal demande)
	sql_updateq("spip_souscriptions",array('id_auteur'=>$id_auteur),"id_auteur=0 AND courriel=".sql_quote($row['email']));

	return $id_auteur;
}


function profils_creer_depuis_mailsubscriber($champs, $notifier=true){

	$set = array(
		'email' => $champs['email'],
		'login' => $champs['email'],
		'name' => $champs['nom'],
		'prenom' => '',
	);

	if (!$set['prenom'] AND !$set['name']){
		$nom = explode('@',$set['email']);
		$set['prenom'] = reset($nom);
	}
	$set['nom'] = trim($set['prenom'].' '.$set['name']);

	if (!$row = profils_creer_auteur($set))
		return 0;
	$id_auteur = $row['id_auteur'];

	if ($notifier){
		// envoyer l'email avec login/pass
		$contexte = array(
			'nom' => $row['prenom']?$row['prenom']:$row['nom'],
			'email' => $row['email'],
			'pass' => $row['pass'],
		);
		$message = recuperer_fond('modeles/mail_creation_profil_mailsubscriber',$contexte);
		include_spip("inc/notifications");
		notifications_envoyer_mails($row['email'],$message);
	}

	return $id_auteur;
}