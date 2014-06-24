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
 * Creer un auteur lors d'une souscription si besoin
 * et lui envoyer un mail avec un compte auteur
 *
 * @param array $champs
 * @param bool $notifier
 * @return int
 */
function profils_creer_depuis_souscription($champs, $notifier=true){
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
		'adresse' => $champs['adresse'],
		'adresse_cp' => $champs['code_postal'],
		'adresse_ville' => $champs['ville'],
		'adresse_pays' => $champs['pays'],
		'tel_fixe' => $champs['telephone'],
		'statut' => '6forum',
		'pass' => $pass,
	);

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

	if ($notifier){
		// envoyer l'email avec login/pass
		$contexte = array(
			'nom' => $set['prenom']?$set['prenom']:$set['nom'],
			'email' => $set['email'],
			'pass' => $set['pass'],
		);
		$message = recuperer_fond('modeles/mail_creation_profil',$contexte);
		include_spip("inc/notifications");
		notifications_envoyer_mails($set['email'],$message);
	}


	// rattraper les anciennes souscriptions avec cet email et id_auteur=0
	// (historique, ou dons uniques sans recu fiscal demande)
	sql_updateq("spip_souscriptions",array('id_auteur'=>$id_auteur),"id_auteur=0 AND courriel=".sql_quote($champs['courriel']));

	return $id_auteur;
}