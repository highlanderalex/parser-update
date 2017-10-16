<?php
	
	set_time_limit(0);
	ini_set('MAX_EXECUTION_TIME', 3600);
	ini_set('display_errors', 'On');
	
	require_once('config/config.php');
	require_once('lib/PHPMailer/class.phpmailer.php');
	require_once('lib/helpers/func.php');
	require_once('classes/ParserModel.php');
	require_once('classes/CsvModel.php');
	require_once('classes/ProductModel.php');
	
	
	ob_start();
	try
	{
		include('script.php');
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}
	finally
	{
		$result = ob_get_contents();
		ob_end_clean();
		
		if(strpos($result, 'OK'))
		{
			$data['success'] = SUCCESS_SCRIPT;
		}
		else
		{
			$data['fail'] = FAIL_SCRIPT;
			//$data['error'][] = $fail;
		}
		
		require_once('resource/page.php');
	}
	
?>