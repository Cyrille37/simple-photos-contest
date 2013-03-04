<?php

/*
 */

require_once( __DIR__.'/../libs/PHPExcel_1.7.8/PHPExcel.php');

/**
 * Description of SPCExport
 *
 * @author cyrille
 */
class SPCExport {

	protected $excel;

	public function __construct() {

		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array('memoryCacheSize' => '1MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

		$locale = 'fr_FR';
		$validLocale = PHPExcel_Settings::setLocale($locale);
		if (!$validLocale) {
			throw new Exception('Unable to set locale to ' . $locale . " - reverting to en_us");
		}

		$this->excel = new PHPExcel();
		$this->excel->removeSheetByIndex(0);
	}

	public function addSheet($sheetName, &$data) {

		$sheet = $this->excel->createSheet();
		$sheet->setTitle($sheetName);

		$headers = & $data['headers'];
		$c = 0;
		$r = 1;
		foreach ($headers as $k => $v) {
			$label = $v['label'];
			$sheet->setCellValueByColumnAndRow($c, $r, $label);
			$c++;
		}
		$rows = & $data['rows'];
		$r = 2;
		foreach ($rows as $row) {
			$c = 0;
			foreach ($row as $v) {
				$sheet->setCellValueByColumnAndRow($c, $r, $v);
				$c++;
			}
			$r++;
		}
	}

	public function save($filename) {

		// .xlsx
		$objWriter = new PHPExcel_Writer_Excel2007($this->excel);
		$objWriter->save($filename);

		//$objPHPExcel->disconnectWorksheets();
		//unset($objPHPExcel);
	}

}
