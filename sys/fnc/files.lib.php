<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.7                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Geting file size function      ##
## copyright     ©Andrey Brykin 2010-2011       ##
##################################################


##################################################
##												##
## any partial or not partial extension         ##
## CMS Fapos,without the consent of the         ##
## author, is illegal                           ##
##################################################
## Любое распространение                        ##
## CMS Fapos или ее частей,                     ##
## без согласия автора, является не законным    ##
##################################################

// Возвращает размер файла в Кб
function getFileSize( $file )
{
  return number_format( (filesize($file)/1024), 2, '.', '' );
}



function deleteAttach($module, $entity_id, $attachNum) {
    $Register = Register::getInstance();

	$attachModelClass = $Register['ModManager']->getModelNameFromModule($module . 'Attaches');
	$attachModel = new $attachModelClass;
	$attaches = $attachModel->getCollection(array(
		'entity_id' => $entity_id,
		'attach_number' => $attachNum,
	), array(
	));

    if (count($attaches) > 0 && is_array($attaches)) {
		foreach ($attaches as $attach) {
			if (!empty($attach)) {
				$filePath = ROOT . '/sys/files/' . $module . '/' . $attach->getFilename();
				if (file_exists($filePath)) {
					_unlink($filePath);
				}
				$attach->delete();
			}
		}
    }
    return true;
}

/**
 * Download attached files
 *
 * @param string $module
 * @param int $entity_id
 */
function downloadAttaches($module, $entity_id) {
	$Register = Register::getInstance();

	$attaches = true;
	if (empty($entity_id) || !is_numeric($entity_id)) return false;
	$files_dir = ROOT . '/sys/files/' . $module . '/';
	// delete collizions if exists 
	//$this->deleteCollizions(array('id' => $post_id), true);
	
	
	$max_attach = Config::read('max_attaches', $module);
	if (empty($max_attach) || !is_numeric($max_attach)) $max_attach = 5;
	for ($i = 1; $i <= $max_attach; $i++) {
		$attach_name = 'attach' . $i;
		if (!empty($_FILES[$attach_name]['name'])) {
		
		
			// Извлекаем из имени файла расширение
			$filename = getSecureFilename($_FILES[$attach_name]['name'], $files_dir);
			$ext = strrchr($_FILES[$attach_name]['name'], ".");

			$is_image = isImageFile($_FILES[$attach_name]['type'], $ext) ? 1 : 0;

			// Перемещаем файл из временной директории сервера в директорию files
			if (move_uploaded_file($_FILES[$attach_name]['tmp_name'], $files_dir . $filename)) {
				if ($is_image == '1') {
					$watermark_path = ROOT . '/sys/img/' . (Config::read('watermark_type') == '1' ? 'watermark_text.png' : Config::read('watermark_img'));
					if (Config::read('use_watermarks') && !empty($watermark_path) && file_exists($watermark_path)) {
						$waterObj = new FpsImg;
						$save_path = $files_dir . $filename;
						$waterObj->createWaterMark($save_path, $watermark_path);
					}
				}
				chmod($files_dir . $filename, 0644);
				$attach_file_data = array(
					'entity_id'     => $entity_id,
					'user_id'       => $_SESSION['user']['id'],
					'attach_number' => $i,
					'filename'      => $filename,
					'size'          => $_FILES[$attach_name]['size'],
					'date'          => new Expr('NOW()'),
					'is_image'      => $is_image,
				);
				
				$className = ucfirst($module) . 'AttachesEntity';
				$entity = new $className($attach_file_data);
				$entity->save();
			}
		}
	}
	
	return $attaches;
}


/**
 * Create secure and allowed filename.
 * Check to dublicate;
 *
 * @param string $filename
 * @param string $dirToCheck - dirrectory to check by dublicate
 * @return string
 */
function getSecureFilename($filename, $dirToCheck) {
	if (empty($filename) || !is_string($filename)) {
		return md5(microtime().rand(0, 99999)) . '-' . date("Y-m-d-H-i-s");
	}
	
	
	$ext = strrchr($filename, ".");
	$ext = (isPermittedFile($ext)) ? $ext : '.txt';

	
	$filename = preg_replace('#[^a-z\d_\-]+#iu', 'x', mb_substr($filename, 0, mb_strlen($filename) - mb_strlen($ext)));
	while (file_exists($dirToCheck . $filename . $ext)) {
		$filename .= rand(0, 999);
		clearstatcache();
	}
	return $filename . $ext;
}

function isImageFile($mime, $ext = null) {
	/**
	 * Types of images
	 */
	$allowed_types = array('image/jpeg','image/jpg','image/gif','image/png');
	/**
	 * Images extensions
	 */
	$img_extentions = array('.png','.jpg','.gif','.jpeg');
	
	$is_image = in_array($mime, $allowed_types);
	if (!empty($ext)) $is_image = $is_image && in_array(strtolower($ext), $img_extentions);
	return $is_image;
}

function isPermittedFile($ext) {
	/**
	 * Wrong extention for download files
	 */
	$deny_extentions = array('.php', '.phtml', '.php3', '.html', '.htm', '.pl', '.js');
	
	return !(empty($ext) || in_array(strtolower($ext), $deny_extentions));
}
