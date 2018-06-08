<div class="row my-4">
	<div class="col-12">
		<table class="table table-striped table-bordered table-hover table-sm">
		<?php 
		if (($handle = fopen($dirFactures.'/factures.csv', "r")) !== false):
			while (($datas = fgetcsv($handle, 1000, ";")) !== false):
				unset($datas[0], $datas[3], $datas[6], $datas[7], $datas[9]);
				$datas = array_values($datas);
				$nb = count($datas);
				echo '<tr>';
				for ($i=0; $i < $nb - 1; $i++):
					echo '<td class="text-center">'.$datas[$i].'</td>';
				endfor;
				echo '</tr>';
			endwhile;
			fclose($handle);
		endif;
		?>
		</table>
	</div>
</div>