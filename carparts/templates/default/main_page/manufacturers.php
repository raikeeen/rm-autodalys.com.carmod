<?VerifyAccess_x('main.manufacturers.templ');?>
<div class="ltabs">
	<ul>
		<?if($aRes['COUNT_PAS']&&($aRes['COUNT_COM']||$aRes['COUNT_MOT'])){?><li class="CmTabCars CmTabSelManuf" data-name="CmPassVehicBlock"><span><?=Lng_x('Passengers')?></span></li><?}?>
		<?if($aRes['COUNT_COM']&&($aRes['COUNT_PAS']||$aRes['COUNT_MOT'])){?><li class="CmTabTrucks CmTabSelManuf" data-name="CmComVehicBlock"><span><?=Lng_x('Commercial_vehicles')?></span></li><?}?>
		<?if($aRes['COUNT_MOT']&&($aRes['COUNT_COM']||$aRes['COUNT_PAS'])){?><li class="CmTabMotorbike CmTabSelManuf" data-name="CmMotoVehicBlock"><span><?=Lng_x('Motorcycles')?></span></li><?}?>
	</ul><br>
		<?/*<script>var AllLng = '<?=Lng('All',1,0)?>';</script><div class="carsfilter"><a href="javascript:void(0)"><?=Lng('All',1,0)?></a></div>*/?>
		<?if($aRes['COUNT_PAS']){?>
			<div id="CmPassVehicBlock" class="CmManufContBlock">
				<?if($aRes['COUNT_PAS_FAVORITE']){?>
                    <div class="CmFavManuf">
                        <?foreach($aRes['PAS']['FAVORITE'] as $aProd){
                            ?><a href="<?=$aProd['LINK']?>/" class="favlogo_x" style="background:url(/<?=CM_DIR?>/media/brands/90/<?=$aProd['CODE']?>.png)" title="<?=$aProd['NAME']?>"></a><?
                        }?>
                    </div>
				<?}?>
				<?if($aRes['COUNT_PAS_MAIN']){?>
                    <div class="CmMainManuf">
                        <?foreach($aRes['PAS']['MAIN'] as $aProd){?>
                            <a href="<?=$aProd['LINK']?>/" class="mbut_x">
                                <div class="mbutlogo_x" style="background:url(/<?=CM_DIR?>/media/brands/<?=$aProd['CODE']?>.png)"></div>
                                <div class="mbuttext_x"><?=$aProd['NAME']?></div>
                            </a><?
                        }?>
                    </div>
				<?}?>
			</div>
		<?}?>
		<?if($aRes['COUNT_COM']){?>
			<div id="CmComVehicBlock" class="CmManufContBlock">
				<?if($aRes['COUNT_COM_FAVORITE']){?>
                    <div class="CmFavManuf">
                        <?foreach($aRes['COM']['FAVORITE'] as $aProd){
                            ?><a href="<?=$aProd['LINK']?>/" class="favlogo_x" style="background:url(/<?=CM_DIR?>/media/brands/90/<?=$aProd['CODE']?>.png)" title="<?=$aProd['NAME']?>"></a><?
                        }?>
					</div>
				<?}?>
				<?if($aRes['COUNT_COM_MAIN']){?>
                    <div class="CmMainManuf">
                        <?foreach($aRes['COM']['MAIN'] as $aProd){?>
                            <a href="<?=$aProd['LINK']?>/" class="mbut_x">
                                <div class="mbutlogo_x" style="background:url(/<?=CM_DIR?>/media/brands/<?=$aProd['CODE']?>.png)"></div>
                                <div class="mbuttext_x"><?=$aProd['NAME']?></div>
                            </a><?
                        }?>
					</div>
				<?}?>
			</div>
		<?}?>
		<?if($aRes['COUNT_MOT']){?>
			<div id="CmMotoVehicBlock" class="CmManufContBlock">
				<?if($aRes['COUNT_MOT_FAVORITE']){?>
                    <div class="CmFavManuf">
                        <?foreach($aRes['MOT']['FAVORITE'] as $aProd){
                            ?><a href="<?=$aProd['LINK']?>/" class="favlogo_x" style="background:url(/<?=CM_DIR?>/media/brands/90/<?=$aProd['CODE']?>.png)" title="<?=$aProd['NAME']?>"></a><?
                        }?>
					</div>
				<?}?>
				<?if($aRes['COUNT_MOT_MAIN']){?>
                    <div class="CmMainManuf">
                        <?foreach($aRes['MOT']['MAIN'] as $aProd){?>
                            <a href="<?=$aProd['LINK']?>/" class="mbut_x">
                                <div class="mbutlogo_x" style="background:url(/<?=CM_DIR?>/media/brands/<?=$aProd['CODE']?>.png)"></div>
                                <div class="mbuttext_x"><?=$aProd['NAME']?></div>
                            </a><?
                        }?>
					</div>
				<?}?>
			</div>
		<?}?>           
</div>