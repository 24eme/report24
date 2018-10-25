<?php
setlocale (LC_TIME, 'fr_FR.utf8','fra');

$annee = "2018";

$folder = dir(dirname(__FILE__).'/data/'.$annee);
$tabs = array();
while(false !== ($file = $folder->read())){
	if($file!="." && $file!=".."){
		$tabs[] = $file;
	}
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
    	<style type="text/css">
	    	#tmaSections .oi-chevron-right {
	    		visibility: hidden;
	    	}
	    	#tmaSections .active .oi-chevron-right {
	    		visibility: visible;
	    	}
    	</style>
  </head>
  <body>
		<div class="container">
      		<div class="py-4">
        		<img src="http://www.24eme.fr/img/24eme.svg" alt="" width="110">
        		<strong class="text-dark">Interface de gestion de relation client</strong>
      			<strong class="float-right text-dark"><span class="oi oi-person"></span> InterRhône</strong>
      		</div>

      		<ul class="nav nav-tabs nav-justified" id="sections" role="tablist">
			  	<?php if(count($tabs)):
						foreach ($tabs as $tabName): ?>
					<li class="nav-item">
						<a id="sectionTma" data-toggle="tab" role="tab" aria-controls="<?php echo $tabName; ?>" aria-selected="true" class="nav-link active" href="#<?php echo $tabName; ?>"><span class="oi oi-timer"></span>&nbsp;<?php echo str_replace(".csv","",$tabName); ?>&nbsp;<?php echo date('Y') ?></a>
					</li>
			  	<?php
					endforeach;
				endif; ?>
			</ul>

			<div class="tab-content" id="sectionsContent">
			  	<?php if($hasTma): ?>
				<div class="tab-pane fade show active" id="tma" role="tabpanel" aria-labelledby="sectionTma">
					<?php require_once dirname(__FILE__).'/tma.php'; ?>
				</div>
			  	<?php endif; ?>
			  	<?php if($hasFactures): ?>
			  	<div class="tab-pane fade" id="factures" role="tabpanel" aria-labelledby="sectionFactures">
			  		<?php require_once dirname(__FILE__).'/factures.php'; ?>
			  	</div>
			  	<?php endif; ?>
			  	<?php if($hasContrats): ?>
			  	<div class="tab-pane fade" id="contrats" role="tabpanel" aria-labelledby="sectionContrats">
			  		<?php require_once dirname(__FILE__).'/contrats.php'; ?>
			  	</div>
			  	<?php endif; ?>
			</div>
  		</div>
	 	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
  </body>
</html>
