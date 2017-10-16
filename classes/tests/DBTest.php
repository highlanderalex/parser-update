<?php
    require_once (dirname(__FILE__) . '/../DB.php');
    
    class TestDB extends PHPUnit_Framework_TestCase
    {
        public function testrun()
        {
            $obj1 = DB::run();
            $obj2 = DB::run();
            $this->assertTrue( $obj1 == $obj2 );
        }           
        
        public function testSelect()
        {
            $fld = 'id, product_data, date';
            $this->assertInstanceOf('DB', DB::run()->Select($fld));
        }           
        
        public function testDelete()
        {
            $this->assertInstanceOf('DB', DB::run()->Delete());
        }           
        
        public function testInsert()
        {
            $tbl = 'products';
            $this->assertInstanceOf('DB', DB::run()->Insert($tbl));
        }           
        
        public function testUpdate()
        {
            $tbl = 'products';
            $this->assertInstanceOf('DB', DB::run()->Update($tbl));
        }           
        
        public function testSet()
        {
            $exp = 'id=5';
            $this->assertInstanceOf('DB', DB::run()->Set($exp));
        }           
        
        public function testFields()
        {
            $arr['id'] = 1;
            $arr['product_data'] = 'test';
            $this->assertInstanceOf('DB', DB::run()->Fields($arr));
        }           
        
        public function testValues()
        {
            $arr['id'] = 1;
            $arr['product_data'] = 'test';
            $this->assertInstanceOf('DB', DB::run()->Values($arr));
        }           
        
        public function testFrom()
        {
            $tbl = 'products';
            $this->assertInstanceOf('DB', DB::run()->From($tbl));
        }
        
        public function testInnerJoin()
        {
            $tbl = 'products';
            $this->assertInstanceOf('DB', DB::run()->InnerJoin($tbl));
        }           
        
        public function testOn()
        {
            $exp = 'id=2';
            $this->assertInstanceOf('DB', DB::run()->On($exp));
        }           
        
        public function testJoin()
        {
            $tbl = 'products';
            $this->assertInstanceOf('DB', DB::run()->Join($tbl));
        }           
        
        public function testWhere()
        {
            $exp = 'id=';
            $this->assertInstanceOf('DB', DB::run()->Where($exp));
        }           
        
        public function testI()
        {
            $exp = 'id=1';
            $this->assertInstanceOf('DB', DB::run()->I($exp));
        }           
        
        public function testOrder()
        {
            $fld = 'id';
            $this->assertInstanceOf('DB', DB::run()->Order($exp));
        }           
        
        public function testDesc()
        {
            $this->assertInstanceOf('DB', DB::run()->Desc());
        } 
        
        public function testLimit()
        {
            $this->assertInstanceOf('DB', DB::run()->Limit(1));
        }
        
        public function testExecute()
        {
            $this->assertInstanceOf('DB',DB::run()->Select('id, product_data')
                                                ->From('products'));
        }



    }

?>
