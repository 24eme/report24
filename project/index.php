<?php
setlocale (LC_TIME, 'fr_FR.utf8','fra');

$campagne = (isset($_GET["campagne"]) && preg_match('/^[0-9]{4}$/', $_GET["campagne"]))? $_GET["campagne"] : date('Y');

$folderName = dirname(__FILE__).'/data/'.$campagne;
$folder = dir($folderName);
$tabs = array();
while(false !== ($file = $folder->read())){
	if($file!="." && $file!=".." && !preg_match('/.example$/', $file)){
		$tabs[$file] = strtolower(str_replace('.csv', '', trim($file)));
	}
}

// calcul des bars de temps passé
$barsTemps = array(); $first = true;
$pathTemps = $folderName.'/temps.csv';
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
usort($barsTemps);
$maxBar = max($barsTemps);
$barSize = $maxBar * 1.33;

// calcul du restant à payer
$pathFactures = $folderName.'/factures.csv';
$restantFactures = 0.0; $first = true;
if (($handle = fopen($pathFactures, "r")) !== false) {
		while (($datas = fgetcsv($handle, 1000, ";")) !== false) {
			if($first){
				$first = false;
				 continue;
			}
			$datas = array_values($datas);
			if(!array_key_exists($typeTemps,$barsTemps)){ $barsTemps[$typeTemps] = 0.0; }
			if(strtoupper($datas[4]) == "FAUX"){
				$s = str_replace(array(" ",',','€'), array('','.',''), preg_replace("/\s+/", '',$datas[3]));
				$restantFactures +=  floatval($s);
			}
		}
	fclose($handle);
}

// calcul des types d'activités
$pathActivites = $folderName.'/activites.csv';
$first = true;
$activites = array("commits" => 0,"mails" => 0,"reunions" => 0);
if (($handle = fopen($pathActivites, "r")) !== false) {
		while (($datas = fgetcsv($handle, 1000, ";")) !== false) {
			if($first){
				$first = false;
				 continue;
			}
			$datas = array_values($datas);
			$attributs = explode(',',$datas[1]);
			$type = str_replace("Type:",'',$attributs[0]);
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
	$matches = array();
	if(preg_match('/^([0-9]{4}-[0-9]{2}-[0-9]{2})\ [0-9]{2}:[0-9]{2}:[0-9]{2}/', $tableCase, $matches)){
		return $matches[1];
	}
	return str_replace('\n', "<br/>",nl2br($tableCase));
}

?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous" />
    	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/open-iconic/1.1.1/font/css/open-iconic-bootstrap.min.css" />
    	<title>Report24 - </title>
  </head>
  <body>
		<div class="container">
      		<div class="py-4">
        		<img src="http://www.24eme.fr/img/24eme.svg" alt="" width="110">
        		<strong class="text-dark">Interface de gestion de relation client</strong>
      			<strong class="float-right text-dark"><span class="oi oi-person"></span> <?php echo ucfirst(strtolower(basename(__DIR__))) ?></strong>
      		</div>

		<div class="row my-4">
			<div class="col-7">
				<div class="card card-default h-100">
					<div class="card-body">
						<div class="row">
						<?php foreach ($barsTemps as $barName => $barTemps) : ?>
							<div class="col-9" >
								<div class="progress" style="height: 30px; margin-bottom:10px;">
								  <div class="progress-bar bg-warning text-dark" role="progressbar" aria-valuenow="<?php echo $barTemps ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo ($barTemps /$barSize) * 100 ?>%"><?php echo $barName ?></div>
								</div>
							</div>
							<div class="col-3"><?php echo round($barTemps * 2 ) / 2; ?> jrs</div>
						<?php endforeach ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-5" >
				<div class="card card-default h-100">
					<div class="card-body">
						<div class="text-center"><h4><?php echo sprintf("%.2f",$restantFactures)." €"?><br/>restant à payer</h4><hr/>
							<h4><?php echo $activites["commits"]; ?>&nbsp;commits&nbsp;/&nbsp;<?php echo $activites["mails"]; ?>&nbsp;mails<br/><?php echo $activites["reunions"]; ?>&nbsp;réunions</h4>
						</div>
					</div>
				</div>
			</div>
		</div>


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
	 	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
  </body>
</html>
