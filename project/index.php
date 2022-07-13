<?php
setlocale (LC_TIME, 'fr_FR.utf8','fra');

$dateToday = new DateTime();
$dateToday->modify("-1 month");
$campagne = (isset($_GET["campagne"]) && preg_match('/^[0-9]{4}$/', $_GET["campagne"]))? $_GET["campagne"] : $dateToday->format('Y');
$campagnes = array();
for($i=date('Y')*1; $i >= 2018; $i--) {
       $campagnes[] = $i."";
}    
$folderName = dirname(__FILE__).'/data/'.$campagne;
$folder = dir($folderName);

//si pas de .csv, on regarde le csv.example
$fichier_extension = '.csv';
if (!file_exists($folderName.'/temps'.$fichier_extension)) {
	$fichier_extension = '.csv.example';
}

$tabs = array();
while(false !== (file_exists($folderName) && $file = $folder->read())){
	if($file!="." && $file!=".." && preg_match('/'.$fichier_extension.'$/', $file)){
		$tabs[$file] = ucfirst(preg_replace("/^[0-9]+-/", "", strtolower(str_replace($fichier_extension, '', trim($file)))));
	}
}

// calcul des bars de temps passé
$barsTemps = array(); $first = true;
$pathTemps = $folderName.'/temps'.$fichier_extension;
if (($handle = fopen($pathTemps, "r")) !== false) {
		while (($datas = fgetcsv($handle, 1000, ";")) !== false) {
			if($first){
				$first = false;
				 continue;
			}
			$datas = array_values($datas);
			$typeTemps = $datas[3];
			if(!array_key_exists($typeTemps,$barsTemps)){ $barsTemps[$typeTemps] = 0.0; }
			$barsTemps[$typeTemps] +=  (floatval(str_replace(',', '.', $datas[2])) / 7.0);

		}
	fclose($handle);
}
ksort($barsTemps);
$maxBar = max($barsTemps);
$barSize = $maxBar * 1.33;

// calcul du restant à payer
$pathFactures = $folderName.'/factures'.$fichier_extension;
$restantFactures = 0.0; $first = true;
if (($handle = fopen($pathFactures, "r")) !== false) {
		while (($datas = fgetcsv($handle, 1000, ";")) !== false) {
			if($first){
				$first = false;
				 continue;
			}
			$datas = array_values($datas);
			if(!array_key_exists($typeTemps,$barsTemps)){ $barsTemps[$typeTemps] = 0.0; }
			if(!$datas[4]){
				$s = str_replace(array(" ",',','€'), array('','.',''), preg_replace("/\s+/", '',$datas[3]));
				$restantFactures +=  floatval($s);
			}
		}
	fclose($handle);
}

// calcul des types d'activités
$pathActivites = $folderName.'/activites'.$fichier_extension;
$first = true;
$activites = array("commits" => 0,"mails" => 0,"reunions" => 0);
if (($handle = fopen($pathActivites, "r")) !== false) {
		while (($datas = fgetcsv($handle, 1000, ";")) !== false) {
			if($first){
				$first = false;
				 continue;
			}
			$type = $datas[2];
			if($type == 'Commit'){
				$activites["commits"] +=1;
			}
			if($type == 'Mail'){
				$activites["mails"] +=1;
			}
		}
	fclose($handle);
}

function transform($tableCase){
	if(preg_match('/^Type:/',$tableCase)){
		$attributs = explode(',',$tableCase);
		if($attributs[0] && $attributs[2]){
			$type = str_replace("Type:",'',$attributs[0]);
			$author = str_replace("Author:",'',$attributs[2]);
			if($type && $author){
				return $type."&nbsp;".$author;
			}
		}
	}
	if(preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $tableCase)){
		return "<div class='text-center'>".$tableCase.'</div>';
	}
	$matches = array();
	if(preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})(\ [0-9]{2}:[0-9]{2}:[0-9]{2})?/', $tableCase, $matches)){
		return "<div class='text-center'>".$matches[3]."/". $matches[2]."/". $matches[1].'</div>';
	}
	if(preg_match('/^[0-9\s ]+([,.]{1}[0-9]+)?([\s €]+)?$/', $tableCase)){
		return "<div class='text-right'>".$tableCase.'</div>';
	}

	return str_replace('\n', "<br/>",nl2br($tableCase));
}
$client = strtolower(preg_replace("/_.+$/", "", basename(__DIR__)));

$clientConf = parse_ini_file(dirname(__FILE__)."/../../config/$client.inc");

uasort($tabs, function($a, $b) {  if($a[0] == "F") { $a[0] = "Z"; } if($b[0] = "F") { $b[0] = "Z"; } return $a < $b; });

?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="robots" content="noindex, nofollow"/>
    	<link rel="stylesheet" type="text/css" href="/libs/bootstrap/4.1.0/css/bootstrap.min.css" />
    	<link rel="stylesheet" type="text/css" href="/libs/open-iconic/1.1.1/css/open-iconic-bootstrap.min.css" />
		<title>Report24 - <?php echo strtoupper($client) ?></title>
  </head>
  <body>
		<div class="container">
      		<div class="py-4">
				<img src="https://www.24eme.fr/img/24eme.svg" alt="" width="110">
        		<strong class="text-dark">Interface de gestion de relation client</strong>
				<strong class="float-right text-dark">
					<span class="oi oi-person"></span> <?php echo strtoupper($client) ?>
					<?php if ($clientConf && isset($clientConf['FACTURE_EMAILS'])): ?>
					<br />
					<span class="font-weight-normal"><a target="_blank" href="mailto:<?php echo $clientConf['FACTURE_EMAILS'] ?>"><span class="oi oi-envelope-closed"></span> Contacter</span></a>
					<?php endif; ?>
				</strong>
      		</div>

			<form id="form_campagne" method="GET" action="">Tableau de bord pour l'année
				<select name="campagne" onchange="document.querySelector('form#form_campagne').submit();">
					<?php foreach($campagnes as $c): ?>
						<option value="<?php echo $c; ?>" <?php if($campagne == $c): ?>selected="selected"<?php endif; ?>><?php echo $c; ?></option>
					<?php endforeach; ?>
				</select>
			</form>

			<div class="row my-4">
				<div class="col-7">
					<div class="card card-default h-100">
						<div class="card-body">
							<h5 class="card-title">Résumé des temps passés</h5>
							<div class="row">
							<?php foreach ($barsTemps as $barName => $barTemps) : ?>
								<div class="col-9" >
									<div class="progress" style="height: 30px; margin-bottom:10px; background-color: white;">
									  <div class="progress-bar bg-warning text-dark" role="progressbar" aria-valuenow="<?php echo $barTemps ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo ($barTemps /$barSize) * 100 ?>%"><?php echo $barName ?></div>
									</div>
								</div>
								<div class="col-3 text-right"><?php echo round($barTemps * 2 ) / 2; ?> jours</div>
							<?php endforeach ?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-5" >
					<div class="card card-default h-100">
						<div class="card-body text-center">
							<h5 class="card-title">Résumé des factures</h5>
							<h4><?php echo number_format($restantFactures, 2, ',', ' ')." €"?><br/>restant à payer</h4><hr/>
							<h5 class="card-title">Résumé de l'activité</h5>
								<h4><?php echo $activites["commits"]; ?>&nbsp;commits<br /><?php echo $activites["mails"]; ?>&nbsp;mails</h4>
						</div>
					</div>
				</div>
			</div>

			<?php if(!count($tabs)): ?>
				<p>Aucune donnée pour cette année</p>
			<?php else: ?>
	      		<ul class="nav nav-tabs nav-justified" id="sections" role="tablist">
				  	<?php
				  	if(count($tabs)):
				  		$first = true;
						foreach ($tabs as $tabName):
				  	?>
					<li class="nav-item">
						<a id="section_<?php echo $tabName ?>" data-toggle="tab" role="tab" aria-controls="<?php echo $tabName; ?>" aria-selected="true" class="nav-link<?php if ($first): ?> active<?php endif; ?>" href="#<?php echo $tabName; ?>"><?php echo ucfirst($tabName); ?></a>
					</li>
				  	<?php
				  		$first = false;
						endforeach;
					endif;
					?>
				</ul>
			<?php endif; ?>


			<div class="tab-content" id="sectionsContent">
			  	<?php
			  	if(count($tabs)):
			  		$first = true;
					foreach ($tabs as $target => $tabName):
			  	?>
				<div class="tab-pane fade show<?php if ($first): ?> active<?php endif; ?>" id="<?php echo $tabName ?>" role="tabpanel" aria-labelledby="section_<?php echo $tabName ?>">
					<div class="row my-4">
						<div class="col-12">
							<table class="table table-striped table-bordered table-hover table-sm">
							<?php
							if (($handle = fopen(dirname(__FILE__).'/data/'.$campagne.'/'.$target, "r")) !== false):
								$first = true;
								while (($datas = fgetcsv($handle, 1000, ";")) !== false):
									$datas = array_values($datas);
									$nb = count($datas);
									echo '<tr>';
									for ($i=0; $i < $nb; $i++):
										echo ($first)?  '<th class="text-center">'.$datas[$i].'</th>' : '<td>'.transform($datas[$i]).'</td>';
									endfor;
									echo '</tr>';
									$first = false;
								endwhile;
								fclose($handle);
							endif;$activites["commits"]
							?>
							</table>
							<div class="col-12 text-center">
								<a href="<?php echo 'data/'.$campagne.'/'.$target ?>" target="_blank" class="btn btn-warning"><span class="oi oi-cloud-download"></span> Télécharger le CSV</a>
							</div>
						</div>
					</div>
				</div>
			  	<?php
			  		$first = false;
					endforeach;
				endif;
				?>
			</div>
  		</div>
	 	<script src="/libs/jquery/3.3.1/js/jquery.min.js"></script>
		<script src="/libs/popper.js/1.14.0/js/popper.min.js"></script>
		<script src="/libs/bootstrap/4.1.0/js/bootstrap.min.js"></script>
  </body>
</html>
