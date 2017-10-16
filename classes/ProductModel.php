<?php
	require_once ('DB.php');

	/* Class ProductModel
	* 
	* *
	* @method construct: Create object model
	* @method getProducts: The return assoc array of products
	* @method insertProducts: Retutn boolean
	*/

	class ProductModel 
	{
		private $inst;
		
		public function __construct()
		{
			$this->inst = DB::run();
		}

		/* Get last products
		*
		*
		* @param no params
		* @return string
		*/

		public function getProducts()
		{
			$res = $this->inst->Select('product_data')
								->From('products')
								->Order('id')
								->Desc()
								->Limit(1)
								->Execute();
			$res = $this->inst->dbLineArray($res);
			$str = str_replace(["\'", "\""], ["'", '"'], $res['product_data']);
			return $str; 
		}
		
		/* Insert new products
		*
		*
		* @param array $data
		* @return boolean
		*/
		public function insertProducts($data)
		{
			$arr['product_data'] = str_replace(["'", '"'], ["\'", "\""], $data['product_data']);
			$res = $this->inst->Insert('products')
								->Fields($arr)
								->Values($arr)
								->Execute();
			
			if (!$res)
				return false;
            
			return true;
		}
	}
