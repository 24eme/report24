<?php

$consommeTma = 0;
foreach ($contentTma as $dir) {
	$csvFile = array_diff(scandir($dirTma.'/'.$dir), array('..', '.'));
	if (count($csvFile) != 1) continue;
	$csvFile = current($csvFile);
	if (($handle = fopen($dirTma.'/'.$dir.'/'.$csvFile, "r")) !== false) {
		while (($datas = fgetcsv($handle, 1000, ";")) !== false) {
			unset($datas[1], $datas[count($datas)]);
			$datas = array_values($datas);
			$nb = count($datas);
			$consommeTma +=  floatval(str_replace(',', '.', $datas[$nb-1]));
		}
		fclose($handle);
	}
}
$consommeTma = ceil($consommeTma / 7 * 2) / 2;
$pourcentageTma = ceil($consommeTma * 100 / $joursTma);

rsort($contentTma);
?>

<div class="row my-4">
	
	<div class="col-1"></div>
	<div class="col-8">
		<div class="progress" style="height: 30px;">
		  <div class="progress-bar bg-warning text-dark" role="progressbar" aria-valuenow="<?php echo $pourcentageTma ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $pourcentageTma ?>%"><?php echo $consommeTma ?> jrs (<?php echo $pourcentageTma ?>%)</div>
		</div>
		<div class="progress" style="height: 2px;">
		  <div class="progress-bar bg-warningbg-info" role="progressbar" aria-valuenow="<?php echo count($contentTma) * 100 / 12 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo count($contentTma) * 100 / 12 ?>%"></div>
		</div>
	</div>
	<div class="col-1"><?php echo $joursTma ?> jrs</div>
	<div class="col-2">&nbsp;</div>
	
	<div class="col-1 text-right"><small>Janvier</small></div>
	<div class="col-8"></div>
	<div class="col-1"><small>Décembre</small></div>
	<div class="col-2">&nbsp;</div>
</div>

<div class="row my-4">
	
	<div class="col-10">
		<div class="tab-content" id="tmaSectionsContent">
			<?php 
				foreach ($contentTma as $dir): 
					$csvFile = array_diff(scandir($dirTma.'/'.$dir), array('..', '.'));
					if (count($csvFile) != 1) continue;
					$csvFile = current($csvFile);
			?>
			<div class="tab-pane fade<?php if($dir == $contentTma[0]): ?> show active<?php endif; ?>" id="<?php echo $dir ?>" role="tabpanel" aria-labelledby="sectionTma<?php echo $dir ?>">
				
				<table class="table table-striped table-bordered table-hover table-sm">
				<?php 
				$row = 0;
				$total = 0;
				if (($handle = fopen($dirTma.'/'.$dir.'/'.$csvFile, "r")) !== false):
					while (($datas = fgetcsv($handle, 1000, ";")) !== false):
						unset($datas[1], $datas[count($datas)]);
						$datas = array_values($datas);
						$nb = count($datas);
						$total +=  floatval(str_replace(',', '.', $datas[$nb-1]));
						echo ($row > 0)? '<tr>' : '<tr class="text-center">';
						for ($i=0; $i < $nb; $i++):
							if (!$i)
								echo ($row > 0)? '<td class="text-center">'.date('d/m/Y', mktime(0, 0, 0, explode('-', $datas[$i])[1], explode('-', $datas[$i])[2], explode('-', $datas[$i])[0])).'</td>' : '<th>'.$datas[$i].'</th>';
							elseif ($i == $nb - 1)
								echo ($row > 0)? '<td class="text-right">'.number_format(floatval(str_replace(',', '.', $datas[$i])), 1, ',', ' ').'</td>' : '<th>'.$datas[$i].'</th>';
							else
								echo ($row > 0)? '<td>'.$datas[$i].'</td>' : '<th>'.$datas[$i].'</th>';
						endfor;
						echo '</tr>';
						$row++;
					endwhile;
					fclose($handle);
				endif;
				?>
					<tr>
						<td class="text-right" colspan="2"><strong>Total (en jours homme)</strong>
						<td class="text-center"><strong><?php echo number_format(ceil($total / 7 * 2) / 2, 1, ',', ' ') ?> <small class="text-muted">j/h</small></strong></td>
					</tr>
				</table>
				
					<div class="col-12 text-center">
						<a href="<?php echo '/sources/tma/'.$csvFile ?>" target="_blank" class="btn btn-warning"><span class="oi oi-cloud-download"></span> Télécharger CSV</a>
					</div>
				
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="col-2">
		<div class="nav flex-column nav-pills" id="tmaSections" role="tablist" aria-orientation="vertical">
			<?php 
				foreach ($contentTma as $dir): 
					$csvFile = array_diff(scandir($dirTma.'/'.$dir), array('..', '.'));
					if (count($csvFile) != 1) continue;
			?>
		  		<a class="nav-link<?php if($dir == $contentTma[0]): ?> active<?php endif; ?>" aria-selected="<?php if($dir == $contentTma[0]): ?>true<?php else: ?>false<?php endif; ?>" id="sectionTma<?php echo $dir ?>" data-toggle="pill" href="#<?php echo $dir ?>" role="tab" aria-controls="<?php echo $dir ?>"><span class="oi oi-chevron-right"></span> <?php echo (strlen($dir) == 8)? ucfirst(strftime('%B %Y', mktime(0, 0, 0, substr($dir, 4, 2), substr($dir, 6, 2), substr($dir, 0, 4)))) : $dir; ?></a>
		  	<?php endforeach; ?>
		</div>
	</div>
</div>