<?php
	
	$data['start'] = time();
	$urls = array();
	
	$parser = new ParserModel();
	$urlOut = array();
	//Get all urls for products
	for($i = 1; $i <= PAGES; $i++)
	{
		if ( 1 == $i )
		{
			$html = get_html(BASE_SITE . '/' . MAIN_PAGE);
		}
		else
		{
			$html = get_html(BASE_SITE . '/' . MAIN_PAGE . '?page=' . $i);
		}
		if (!$html)
		{
			$data['error'][] = BASE_SITE . '/' . MAIN_PAGE . '?page=' . $i . ': ' . FAIL_URL;
			loggit(BASE_SITE . '/' . MAIN_PAGE . '?page=' . $i, FAIL_URL);
			continue;
			
		}
		$parser->loadData($html);
		$urls[] = $parser->getArrayFromSelector('a.lst_a', 'href');
		$parser->clear();
	}
	$urlOut = merge($urls);
	
	for($i = 0; $i < count($urlOut); $i++)
	{
		$html = get_html(BASE_SITE . $urlOut[$i]);
		if ( !$html )
		{
			$data['error'][] = BASE_SITE . $urlOut[$i] . ': ' . FAIL_URL;
			loggit(BASE_SITE . $urlOut[$i], FAIL_URL);
			continue;
		}
		$product = array();
		$parser->loadData($html);
		
		$id = $parser->getTextFromSelector('b[itemprop=sku]');
		if( !$id ) 
		{
			$data['error'][] = BASE_SITE . $urlOut[$i] . ': ' . FAIL_DATA;
			loggit(BASE_SITE . $urlOut[$i], FAIL_DATA);
			continue;
		}
		$product[] = $id;
		
		$product[] = $parser->getTextFromSelector('h1.prod_name');
		
		$product[] = $parser->getTextFromSelector('span.js-product-price-hide');
		
		$product[] = array_to_str($parser->getArrayFromSelector('div.prod-thumbs a[style]', 'href'));
		//video
		$product[] = array_to_str($parser->getArrayFromSelector('div.prod-thumbs a[href$=mp4]', 'href'));
		//pdf
		$product[] = array_to_str($parser->getArrayFromSelector('div.prod-thumbs a[href$=pdf]', 'href'));
		//features
		$product[] = array_to_str($parser->getArrayText('div.js-spoiler-block > ul li'), '[:os:]');
		
		$review = $parser->getChildren('span[class=prod-rvw-info-posted]', 'b > span');
		$total_review = $parser->getAttrValue('span[data-total]', 'data-total');
		
		if ($total_review)
		{
			$c = get_cookie(BASE_SITE . $urlOut[$i]);
			$param = array();
			$postHtml = '';
			$param[] = 'id=' . $parser->getAttrValue('span[data-id]', 'data-id');
			$param[] = 'type=new';
			$param[] = 'action=getSuperProductReviews';
			for($page_review = 10; $page_review <= $total_review; $page_review +=10)
			{	
				$param[] = 'offset=' . $page_review;
				$postHtml .= get_html(BASE_SITE . '/submit_review.php', true, $param, $c);
			}
			$parser->loadData($postHtml);
			$new_review = $parser->getChildren('span[class=prod-rvw-info-posted]', 'b > span');
			$product[] = array_to_str(array_merge($review, $new_review), ',', true);
		}
		else
		{
			$product[] = array_to_str($review, ',', true);
		}
		
		$newProducts[] = $product;
		$parser->clear();
		
	}
	
	//create csv files and write db
	$columns = 'Product Identifier,Product Name,Product Price,Product Images,';
	$columns .= 'Product Video,Product PDF,Product Features,Dates of Reviews';

	$csv_model = new CsvModel();
	$product_model = new ProductModel();
	$oldData = $product_model->getProducts();
	$newData = get_str_from_array($newProducts);
	if(!$oldData)
	{
		if (!$product_model->insertProducts(['product_data' => $newData]))
		{
			$data['error'][] = 'Insert to DataBase: Error';
		}
		$productsCSV = $csv_model->setCsv(PATH . 'products.csv', $columns, $newProducts, true);
		$columns = 'Product Identifier';
		$newProductsCSV = $csv_model->setCsv(PATH . 'new_products.csv', $columns);
		$delProductsCSV = $csv_model->setCsv(PATH . 'disapperaed_products.csv', $columns);
		$reviewedProductsCSV = $csv_model->setCsv(PATH . 'recently_reviewed_products.csv', $columns);
		
	}
	else
	{
		$oldProducts = get_array_from_str($oldData);
		if (!$product_model->insertProducts(['product_data' => $newData]))
		{
			$data['error'][] = 'Insert to DataBase: Error';
		}
		$idOldProducts = create_array_by_index($oldProducts);
		$idNewProducts = create_array_by_index($newProducts);
		$productsCSV = $csv_model->setCsv(PATH . 'products.csv', $columns, $newProducts, true);
		
		$columns = 'Product Identifier';
		// id's new product
		$newProd = array_diff($idNewProducts, $idOldProducts);
		$newProductsCSV = $csv_model->setCsv(PATH . 'new_products.csv', $columns, $newProd);
		
		// id's del product
		$delProd = array_diff($idOldProducts, $idNewProducts);
		$delProductsCSV = $csv_model->setCsv(PATH . 'disapperaed_products.csv', $columns, $delProd);
		
		//id's reviewed prod
		$arrId = array_uintersect($idOldProducts, $idNewProducts, 'strcasecmp');
		$reviewedProd = get_reviewed($oldProducts, $newProducts, $arrId);
		$reviewedProductsCSV = $csv_model->setCsv(PATH . 'recently_reviewed_products.csv', $columns, $reviewedProd);
		
	}
	
	$data['end'] = time();
	$start = date('d/m/Y H:i:s', $data['start']);
	$total_time = $data['end'] - $data['start'];
	$total_time = sec_to_time($total_time);
	
	//send mail
    $mail = new PHPMailer;
	$mail->CharSet = 'UTF-8';
	$mail->setFrom(FROM_EMAIL, 'Alex');
	$mail->addAddress(EMAIL, 'PHP Parser test');
	$mail->Subject = 'CARiD suspension systems | ' . date('Y-m-d H:i:s');
	$mail->Body = 'Start script : ' . $start . PHP_EOL;
	$mail->Body .= 'Total time : ' . $total_time . PHP_EOL;
	$mail->addAttachment(PATH . 'new_products.csv', 'new_products.csv', 'base64', 'text/csv');
	$mail->addAttachment(PATH . 'products.csv', 'products.csv', 'base64', 'text/csv');
	$mail->addAttachment(PATH . 'disapperaed_products.csv', 'disapperaed_products.csv', 'base64', 'text/csv');
	$mail->addAttachment(PATH . 'recently_reviewed_products.csv', 'recently_reviewed_products.csv', 'base64', 'text/csv');
		
	if (!$mail->send())
	{
		$data['error'][] = 'Send Mail Error: ' . $mail->ErrorInfo;
	}
	$mail->ClearAddresses();
	$mail->ClearAttachments();
	
	echo 'Successfull: OK';
?>