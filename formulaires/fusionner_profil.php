<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;


function formulaires_fusionner_profil_charger_dist($id_auteur){
	include_spip('inc/autoriser');
	if (!autoriser('webmestre')) return false;

	$valeurs = array(
		'id_profil_import' => '',
	);
	return $valeurs;
}

function formulaires_fusionner_profil_verifier_dist($id_auteur) {
	$erreurs = array();

	$id = _request('id_profil_import');
	if (!intval($id)){
		$erreurs['id_profil_import'] = _T('info_obligatoire');
	}
	elseif(!$auteur = sql_fetsel('*','spip_auteurs','id_auteur='.intval($id))){
		$erreurs['id_profil_import'] = _T('profils:erreur_compte_inexistant');
	}
	elseif(!_request('confirmer_import') OR _request('confirmer_import')!=$id) {
		$erreurs['_confirmer_import'] = recuperer_fond('prive/objets/contenu/auteur',array('id'=>$id));
		$erreurs['_confirmer_import'] .= pipeline('afficher_complement_objet',array('args'=>array('type'=>'auteur','id'=>$id),'data'=>''));
		$erreurs['_confirmer_import'] .= pipeline('affiche_auteurs_interventions',array('args'=>array('id_auteur'=>$id),'data'=>''));
		$erreurs['_confirmer_import'] = preg_replace(',<form.*</form>,Uims','',$erreurs['_confirmer_import']);

		$erreurs['_confirmer_import'] .= "<br />"
			."<input type='checkbox' value='$id' name='confirmer_import' id='confirmer_import'/> "
			."<label for='confirmer_import'>"._T('profils:label_confirmer_import')."</label>";
		$erreurs['message_erreur'] = '';
	}

	return $erreurs;
}

function formulaires_fusionner_profil_traiter_dist($id_auteur) {
	refuser_traiter_formulaire_ajax();
	$res = array('message_ok'=>_T('profils:message_fusion_profil_ok'));
	if ($id = _request('id_profil_import')){
		$profils_fusionner = charger_fonction('profils_fusionner','inc');
		$profils_fusionner($id_auteur,$id);
	}
	return $res;
}