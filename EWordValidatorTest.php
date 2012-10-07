<?php

Yii::import('ext.EWordValidator');
class EWordValidatorTest extends CTestCase
{
    public $validator;
    public $model;
    
    public function setUp()
    {
        $this->validator = new EWordValidator;
        $this->validator->attributes = array('foo');
        
        $this->model = new FormModel;
        $this->model->foo = 'test message';
    }
    
    public function testMinMoreThanLength()
    {
        $this->validator->min = 3;
        $this->validator->validate($this->model);
        $this->assertTrue($this->model->hasErrors());
    }
    
    public function testMinEqualLength()
    {
        $this->validator->min = 2;
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testMinLessThanLength()
    {
        $this->validator->min = 1;
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testMinNotSet()
    {
        $this->validator->min = null;
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testMinMessage()
    {
        $this->validator->min = 3;
        $this->validator->validate($this->model);
        $this->assertEquals('Foo is too short (minimum is 3 words).', $this->model->getError('foo'));
    }
    
    public function testMinCustomMessage()
    {
        $this->validator->min = 3;
        $this->validator->messages = array(
            'min' => 'Wrong {attribute} length. Now it\'s {length}, but should exceed {min} words.',
        );
        $this->validator->validate($this->model);
        $this->assertEquals('Wrong Foo length. Now it\'s 2, but should exceed 3 words.', 
            $this->model->getError('foo'));
    }
    
    public function testMaxMoreThanLength()
    {
        $this->validator->max = 3;
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testMaxEqualLength()
    {
        $this->validator->max = 2;
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testMaxLessThanLength()
    {
        $this->validator->max = 1;
        $this->validator->validate($this->model);
        $this->assertTrue($this->model->hasErrors());
    }
    
    public function testMaxNotSet()
    {
        $this->validator->max = null;
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testMaxMessage()
    {
        $this->validator->max = 1;
        $this->validator->validate($this->model);
        $this->assertEquals('Foo is too long (maximum is 1 words).', $this->model->getError('foo'));
    }
    
    public function testMaxCustomMessage()
    {
        $this->validator->max = 1;
        $this->validator->messages = array(
            'max' => 'Wrong {attribute} length. Now it\'s {length}, but should not exceed {max} words.',
        );
        $this->validator->validate($this->model);
        $this->assertEquals('Wrong Foo length. Now it\'s 2, but should not exceed 1 words.', 
            $this->model->getError('foo'));
    }
    
    public function testExactMoreThanLength()
    {
        $this->validator->exact = 3;
        $this->validator->validate($this->model);
        $this->assertTrue($this->model->hasErrors());
    }
    
    public function testExactLength()
    {
        $this->validator->exact = 2;
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testExactLessThanLength()
    {
        $this->validator->exact = 1;
        $this->validator->validate($this->model);
        $this->assertTrue($this->model->hasErrors());
    }
    
    public function testExactNotSet()
    {
        $this->validator->exact = null;
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testExactMessage()
    {
        $this->validator->exact = 3;
        $this->validator->validate($this->model);
        $this->assertEquals('Foo is of the wrong length (should be 3 words).', $this->model->getError('foo'));
    }
    
    public function testExactCustomMessage()
    {
        $this->validator->exact = 3;
        $this->validator->messages = array(
            'exact' => 'Wrong {attribute} length. Now it\'s {length}, but should be {exact} words.',
        );
        $this->validator->validate($this->model);
        $this->assertEquals('Wrong Foo length. Now it\'s 2, but should be 3 words.', 
            $this->model->getError('foo'));
    }
}

class FormModel extends CFormModel
{
    public $foo;
}