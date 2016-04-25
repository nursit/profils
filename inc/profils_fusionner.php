<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;



function inc_profils_fusionner_dist($id_auteur,$id_import){

	spip_log($s="Fusion auteur #$id_import => #$id_auteur","profils"._LOG_INFO_IMPORTANTE);

	// dupliquer les liens
	include_spip('action/editer_liens');
	objet_dupliquer_liens('auteur',$id_import,$id_auteur);
	// supprimer les liens
	objet_dissocier(array('auteur'=>$id_import),'*');
	objet_dissocier('*',array('auteur'=>$id_import));

	// changer le id_auteur dans toutes les tables qui en ont un
	$tables = lister_tables_objets_sql();
	foreach($tables as $table_sql=>$desc){
		if (isset($desc['field']['id_auteur'])){
			spip_log("$table_sql : id_auteur #$id_import => #$id_auteur","profils"._LOG_INFO_IMPORTANTE);
			sql_updateq($table_sql,array('id_auteur'=>$id_auteur),'id_auteur='.intval($id_import));
		}
	}

	// log/poubelle sur l'import
	$log = date('Y-m-d H:i:s').' par #'.$GLOBALS['visiteur_session']['id_auteur'].' : '.$s."\n";
	$auteur = sql_fetsel('*','spip_auteurs','id_auteur='.intval($id_import));
	$set = array(
		"statut"=>"5poubelle",
		"email" => $auteur['email']."-xxdoublon",
		"login" => $auteur['login']."-xxdoublon",
		'log' => $auteur['log'].$log,
	);
	sql_updateq("spip_auteurs",$set,"id_auteur=".intval($auteur['id_auteur']));

	// log sur le nouveau
	$auteur = sql_fetsel('*','spip_auteurs','id_auteur='.intval($id_auteur));
	$set = array(
		'log' => $auteur['log'].$log,
	);
	sql_updateq("spip_auteurs",$set,"id_auteur=".intval($auteur['id_auteur']));

}
