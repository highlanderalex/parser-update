<?php
	
	require_once(dirname(__FILE__) . '/../../config/config.php');
	require_once (dirname(__FILE__) . '/../CsvModel.php');
	
	class TestCsvModel extends PHPUnit_Framework_TestCase
	{
	
		/**
		* @dataProvider providerSetCsv
		*/
		public function testSetCsv($file, $column, $data, $multi)
		{
			$csv = new CsvModel;
			$actual = $csv->setCsv($file, $column, $data, $multi);
			unlink($file);
			$this->assertTrue($actual);
		}
		
		public function providerSetCsv()
		{	
			$file = TMP_DIR . 'test1.csv';
			$col = 'Field1,Field2,Field3';
			$data1 = [1, 2, 3];
			$data2 = [
						[1, 2, 3],
						[1, 2, 3]
			];
			return [
				[$file, $col, $data1, false],
				[$file, $col, $data2, true],
			];
		}
		
		/**
		* @dataProvider providerGetCsv
		*/
		public function testGetCsv($file, $col, $data, $expected)
		{
			$handle = fopen($file, 'w');
			fputcsv($handle, explode(',', $col), '|');
			fputcsv($handle, $data, '|');
			fclose($handle);
			
			$csv = new CsvModel;
			$actual = $csv->getCsv($file);
			unlink($file);
			$this->assertEquals($expected, $actual);
		}
		
		public function providerGetCsv()
		{	
			$file = TMP_DIR . 'test2.csv';
			$col = 'Product';
			$data = [1, 2, 3, 4, 5];
			$exp = [0 => [1, 2, 3, 4, 5]];
			return [
				[$file, $col, $data, $exp],
			];
		}
	}
?>