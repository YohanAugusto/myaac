<?php
	defined('MYAAC') or die('Direct access not allowed!');
	putenv('GDFONTPATH=' . dirname(__FILE__) . '/fonts');

	$font = "arialbd.ttf";
	$fontsize = 8;

	$name = stripslashes(ucwords(strtolower(trim($_REQUEST['name']))));
	if(!check_name($name))
		return;

	$player = $ots->createObject('Player');
	$player->find($name);

	if(!$player->isLoaded())
		return;

	$img = imagecreatefrompng('images/stats.png');
	$title = imagecolorallocate($img, 160, 160, 160);
	$text = imagecolorallocate($img, 180, 180, 180);
	$bar = imagecolorallocate($img, 0, 0, 0);
	$barfill = imagecolorallocate($img, 200, 0, 0);
	$hpfill = imagecolorallocate($img, 200, 0, 0);
	$manafill = imagecolorallocate($img, 0, 0, 200);

	imagettftext($img, $fontsize, 0, 20, 11, $title, $font, $player->getName() . ' - ' . BASE_URL);

	// experience
	$needexp = OTS_Toolbox::experienceForLevel($player->getLevel() + 1);
	$experience = $player->getExperience();
	if($experience > $needexp) $experience = $needexp;
	imagettftext($img, $fontsize, 0, 15, 30, $text, $font, 'Experience');
	imagettftext($img, $fontsize, 0, 100, 30, $text, $font, number_format($experience)." (".number_format($needexp).")");

	// level
	imagettftext($img, $fontsize, 0, 15, 43, $text, $font, 'Level');
	imagettftext($img, $fontsize, 0, 100, 43, $text, $font, number_format($player->getLevel()));

	// experience bar
	$exppercent = round($experience / $needexp * 100);
	imagerectangle($img, 14, 46, 166, 50, $bar);
	if($exppercent > 0)
		imagefilledrectangle($img, 15, 47, $exppercent * 1.5 + 15, 49, $barfill);

	imagettftext($img, $fontsize, 0, 170, 51, $text, $font, $exppercent . '%');

	// vocation
	imagettftext($img, $fontsize, 0, 15, 62, $text, $font, 'Vocation');
	imagettftext($img, $fontsize, 0, 100, 62, $text, $font, $config['vocations'][$player->getPromotion()][$player->getVocation()]);

	// hit points, Mana, Soul Points, Capacity
	$health = $player->getHealth();
	if(health > $player->getHealthMax())
		$health = $player->getHealthMax();

	$empty = imagecreatefrompng('images/empty.png');
	//imagerectangle($img, 39, 67, 141, 75, $bar);
	$fillhp = round($player->getHealth()/$player->getHealthMax() * 100);
	//imagefilledrectangle($img, 40, 68, 40+$fillhp, 74, $hpfill);
	$healthicon = imagecreatefrompng('images/hpicon.png');
	imagecopy($img, $healthicon, 15, 65, 0, 0, 12, 12);
	$healthfg = imagecreatefrompng('images/healthfull.png');
	imagecopy($img, $empty, 32, 65, 0, 0, 100, 12);
	imagecopy($img, $healthfg, 32, 65, 0, 0, $fillhp, 12);
	//imagettftext($img, $fontsize, 0, 15, 75, $text, $font, "Hit Points");
	imagettftext($img, $fontsize, 0, 140, 75, $text, $font, $player->getHealth());

	//imagerectangle($img, 39, 80, 141, 88, $bar);
	$mana = $player->getMana();
	if(mana > $player->getManaMax())
		$mana = $player->getManaMax();

	$fillmana = 0;
	if($player->getMana() > 0 && $player->getManaMax() > 0)
		$fillmana = round($player->getMana()/$player->getManaMax() * 100);

	//imagefilledrectangle($img, 40, 81, 40+$fillmana, 87, $manafill);
	$manaicon = imagecreatefrompng('images/manaicon.png');
	imagecopy($img, $manaicon, 15, 79, 0, 0, 12, 10);
	$manafg = imagecreatefrompng('images/manafull.png');
	imagecopy($img, $empty, 32, 78, 0, 0, 100, 12);
	imagecopy($img, $manafg, 32, 78, 0, 0, $fillmana, 12);
	//imagettftext($img, $fontsize, 0, 15, 88, $text, $font, "Mana");
	imagettftext($img, $fontsize, 0, 140, 88, $text, $font, $player->getMana());

	imagettftext($img, $fontsize, 0, 15, 101, $text, $font, 'Soul Points');
	imagettftext($img, $fontsize, 0, 100, 101, $text, $font, number_format($player->getSoul()));
	imagettftext($img, $fontsize, 0, 15, 114, $text, $font, 'Capacity');
	imagettftext($img, $fontsize, 0, 100, 114, $text, $font, number_format($player->getCap()));

	// magic Level
	imagettftext($img, $fontsize, 0, 15, 127, $text, $font, 'Magic Level');
	imagettftext($img, $fontsize, 0, 100, 127, $text, $font, number_format($player->getMagLevel()));

	// premium status
	$account = $player->getAccount();
	imagettftext($img, $fontsize, 0, 15, 140, $text, $font, $account->getCustomField('premdays') == 0 ? 'Free Account' : 'Premium Account');

	imagefilledrectangle($img, 225, 40, 225, 130, $title); //seperator
	$posy = 50;
	foreach(
		$db->query('SELECT ' . $db->fieldName('skillid') . ', ' . $db->fieldName('value') . ' FROM ' . $db->tableName('player_skills') . ' WHERE ' . $db->fieldName('player_id') . ' = ' . $player->getId() . ' LIMIT 7')
		as $skill)
	{
		imagettftext($img, $fontsize, 0, 235, $posy, $text, $font, getSkillName($skill['skillid']));
		imagettftext($img, $fontsize, 0, 360, $posy, $text, $font, $skill['value']);
		$posy = $posy + 13;
	}

	header('Content-type: image/png');
	imagepng($img);
?>