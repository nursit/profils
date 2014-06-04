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
		array('profils_importer_vieilles_souscriptions'),
	);

	$maj['0.3.0'] = array(
		array('profils_importer_vieilles_souscriptions'),
	);


	// lancer la maj
	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}


function profils_importer_vieilles_souscriptions(){


	$sous = sql_allfetsel("*","spip_souscriptions","id_auteur=0 AND (abo_statut<>".sql_quote('non')." OR recu_fiscal=".sql_quote("on").")");
	#var_dump(count($sous));

	foreach($sous as $sou){
		#var_dump($sou);
		$email = $sou['courriel'];
		if ($row = sql_fetsel("*","spip_auteurs","email=".sql_quote($email)." AND statut<>".sql_quote("5poub"))){
			$id_auteur = $row['id_auteur'];
			sql_updateq("spip_souscriptions",array('id_auteur'=>$id_auteur),"id_auteur=0 AND id_souscription=".intval($sou['id_souscription']));
		}
		else {
			include_spip("inc/profils");
			$id_auteur = profils_creer_depuis_souscription($sou,false);
		}
		if ($id_auteur){
			$trans = sql_allfetsel("id_objet","spip_souscriptions_liens","id_souscription=".intval($sou['id_souscription'])." AND objet=".sql_quote('transaction'));
			$trans = array_map('reset',$trans);
			sql_updateq("spip_transactions",array('id_auteur'=>$id_auteur),"id_auteur=0 AND ".sql_in('id_transaction',$trans));
		}

		#var_dump($id_auteur);
		if (time()>_TIME_OUT)
			return;

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
