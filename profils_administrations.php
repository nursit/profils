<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) Nursit.net
 * Licence GPL
 *
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Declaration des champs complementaires sur la table auteurs, pour les profils
 *
 * @param  $tables
 * @return
 */
function profils_declarer_tables_objets_sql($tables){

	$tables['spip_auteurs']['field']['name'] = "text DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['prenom'] = "text DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['societe'] = "text DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['adresse_1'] = "text DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['adresse_2'] = "text DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['adresse_bp'] = "tinytext DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['adresse_cp'] = "tinytext DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['adresse_ville'] = "tinytext DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['adresse_pays'] = "tinytext DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['tel_fixe'] = "tinytext DEFAULT '' NOT NULL";
	$tables['spip_auteurs']['field']['tel_mobile'] = "tinytext DEFAULT '' NOT NULL";

	// date et ip inscription
	$tables['spip_auteurs']['field']["date_inscription"] = "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL";
	$tables['spip_auteurs']['field']['ip_inscription'] = "varchar(40) DEFAULT '' NOT NULL";


	$tables['spip_auteurs']['champs_editables'][] = 'name';
	$tables['spip_auteurs']['champs_editables'][] = 'prenom';
	$tables['spip_auteurs']['champs_editables'][] = 'societe';
	$tables['spip_auteurs']['champs_editables'][] = 'adresse_1';
	$tables['spip_auteurs']['champs_editables'][] = 'adresse_2';
	$tables['spip_auteurs']['champs_editables'][] = 'adresse_bp';
	$tables['spip_auteurs']['champs_editables'][] = 'adresse_cp';
	$tables['spip_auteurs']['champs_editables'][] = 'adresse_ville';
	$tables['spip_auteurs']['champs_editables'][] = 'adresse_pays';
	$tables['spip_auteurs']['champs_editables'][] = 'tel_fixe';
	$tables['spip_auteurs']['champs_editables'][] = 'tel_mobile';
	return $tables;
}


/**
 * Installation/maj des tables profils
 *
 * @param string $nom_meta_base_version
 * @param string $version_cible
 */
function profils_upgrade($nom_meta_base_version,$version_cible){
	$maj = array();
	// creation initiale
	$maj['create'] = array(
		array('maj_tables',array('spip_auteurs')),
	);


	// lancer la maj
	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}


function profils_inscrire_newsletter(){
	$subscriber = charger_fonction("subscriber","newsletter");
	$subscribe = charger_fonction("subscribe","newsletter");
	$GLOBALS['notification_instituermailsubscriber_status'] = false; // pas de notif pour cet import

	$res = sql_select("email,nom","spip_auteurs","statut=".sql_quote("6forum"));
	while ($row = sql_fetch($res)){
		$infos = $subscriber($row['email']);
		if ($infos
			AND $infos['status']=='on'
			AND in_array('newsletter',$infos['listes'])){
		}
		else {
			$subscribe($row['email'],array('nom'=>$row['nom'],'listes'=>array('profils'),'force'=>true));
		}
	}

	$res = sql_select("A.email,A.nom","spip_auteurs AS A JOIN spip_auteurs_liens AS L ON A.id_auteur = L.id_auteur","A.statut=".sql_quote("6forum")." AND L.objet=".sql_quote('site'),"A.id_auteur");
	while ($row = sql_fetch($res)){
		$infos = $subscriber($row['email']);
		if ($infos
			AND $infos['status']=='on'
			AND in_array('profils_avec_sites',$infos['listes'])){
		}
		else {
			$subscribe($row['email'],array('nom'=>$row['nom'],'listes'=>array('profils_avec_sites'),'force'=>true));
		}
	}
}

/**
 * Desinstallation/suppression du plugin
 *
 * @param string $nom_meta_base_version
 */
function profils_vider_tables($nom_meta_base_version) {
	effacer_meta($nom_meta_base_version);
}
