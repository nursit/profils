<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip("base/abstract_sql");

function formulaires_editer_adresse_profil_charger_dist($id_auteur){

	$charger = charger_fonction("charger","formulaires/editer_profil");
	return $charger($id_auteur);
}


function formulaires_editer_adresse_profil_verifier_dist($id_auteur){
	$erreurs = array();

	$verifier = charger_fonction("verifier","formulaires/editer_profil");
	$erreurs = $verifier ($id_auteur);


	$oblis = array(
		'adresse',
		'adresse_cp',
		'adresse_ville',
		'adresse_pays'
	);

	foreach ($oblis as $obli)
		if (!strlen(_request($obli)))
			$erreurs[$obli] = _T('editer_profil:erreur_' . $obli . '_obligatoire');

	return $erreurs;
}

function formulaires_editer_adresse_profil_traiter_dist($id_auteur){

	$traiter = charger_fonction("traiter","formulaires/editer_profil");
	$res = $traiter($id_auteur);

	if (isset($res['message_ok'])){
		$res['message_ok'] = _T('editer_profil:message_ok_adresse_profil_modifie');
		$res['editable'] = true;
	}
	return $res;
}
