<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;

function inc_renseigner_adresse_auteur_dist($id_auteur){

	$adresse = sql_fetsel("nom,societe,adresse,adresse_cp,adresse_ville,adresse_pays,tel_fixe,tel_mobile","spip_auteurs","id_auteur=".intval($id_auteur));

	if ($adresse){
		$adresse['telephone'] = '';
		if ($adresse['tel_mobile'] AND !$adresse['telephone']) $adresse['telephone'] = $adresse['tel_mobile'];
		if ($adresse['tel_fixe'] AND !$adresse['telephone']) $adresse['telephone'] = $adresse['tel_fixe'];
		unset($adresse['tel_fixe']);
		unset($adresse['tel_mobile']);
	}

	return $adresse;
}