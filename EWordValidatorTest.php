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
    
    /**
     * @dataProvider provider
     */
    public function testWordsCount($text, $wordsCount)
    {
        $this->model->foo = $text;
        $this->validator->validate($this->model);
        $this->assertEquals($wordsCount, $this->validator->getLength());
    }
    
    public function provider()
    {
        return array(//text, words count
            array('', 0),
            array('test message',       2),
            array(' test message',      2),
            array(' test    message ',  2),
            array('test, message',      2),
            array('test,message',       2),
            array('!@#$%^&*()~',        0),
            array('test/message',       2),
            array('test-message',       1),
            array('test-message message',    2),
            array('test' . "\n" . 'message', 2),
        );
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
    
    public function testInRange()
    {
        $this->validator->min = 1;
        $this->validator->max = 3;
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testNotInRange()
    {
        $this->validator->min = 3;
        $this->validator->max = 5;
        $this->validator->validate($this->model);
        $this->assertTrue($this->model->hasErrors());
    }
    
    public function testBlacklistPass()
    {
        $this->validator->blacklist = array('some', 'word');
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testBlacklistNotPass()
    {
        $this->validator->blacklist = array('test', 'word');
        $this->validator->validate($this->model);
        $this->assertTrue($this->model->hasErrors());
    }
    
    public function testBlacklistRegExp()
    {
        $this->validator->blacklist = array('word', 't*');
        $this->validator->validate($this->model);
        $this->assertTrue($this->model->hasErrors());
    }
    
    public function testBlacklistMessage()
    {
        $this->validator->blacklist = array('test', 'word');
        $this->validator->validate($this->model);
        $this->assertEquals('Foo should not contain words (test, word).', $this->model->getError('foo'));
    }
    
    public function testBlacklistCustomMessage()
    {
        $this->validator->blacklist = array('test', 'word');
        $this->validator->messages = array(
          'blacklist' => 'Wrong {attribute}. Should not be in list ({blacklist}).'  
        );
        $this->validator->validate($this->model);
        $this->assertEquals('Wrong Foo. Should not be in list (test, word).', $this->model->getError('foo'));
    }
    
    public function testBlacklistMessageClue()
    {
        $this->validator->blacklist = array('test', 'word');
        $this->validator->clue = '|';
        $this->validator->validate($this->model);
        $this->assertEquals('Foo should not contain words (test|word).', $this->model->getError('foo'));
    }
    
    public function testWhitelistPass()
    {
        $this->validator->whitelist = array('some', 'test');
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testWhitelistNotPass()
    {
        $this->validator->whitelist = array('some', 'word');
        $this->validator->validate($this->model);
        $this->assertTrue($this->model->hasErrors());
    }
    
    public function testWhitelistRegExp()
    {
        $this->validator->whitelist = array('word', 't*');
        $this->validator->validate($this->model);
        $this->assertFalse($this->model->hasErrors());
    }
    
    public function testWhitelistMessage()
    {
        $this->validator->whitelist = array('some', 'word');
        $this->validator->validate($this->model);
        $this->assertEquals('Foo should contain at least one of the words (some, word).', $this->model->getError('foo'));
    }
    
    public function testWhitelistCustomMessage()
    {
        $this->validator->whitelist = array('some', 'word');
        $this->validator->messages = array(
          'whitelist' => 'Wrong {attribute}. Should be in list ({whitelist}).'  
        );
        $this->validator->validate($this->model);
        $this->assertEquals('Wrong Foo. Should be in list (some, word).', $this->model->getError('foo'));
    }
    
    public function testWhitelistMessageClue()
    {
        $this->validator->whitelist = array('some', 'word');
        $this->validator->clue = '|';
        $this->validator->validate($this->model);
        $this->assertEquals('Foo should contain at least one of the words (some|word).', $this->model->getError('foo'));
    }
    
    public function testExternalFilter()
    {
        $this->model->foo = '<span> test message </span>';
        $this->validator->filter = array($this->model, 'filterTags');
        $this->validator->validate($this->model);
        $this->assertEquals(2, $this->validator->getLength());
    }
    
    public function testInlineFilter()
    {
        $this->model->foo = '<span> test message </span>';
        $this->validator->filter = function ($source) { return strip_tags($source); };
        $this->validator->validate($this->model);
        $this->assertEquals(2, $this->validator->getLength());
    }
}

class FormModel extends CFormModel
{
    public $foo;
    
    public function filterTags($source)
    {
        return strip_tags($source);
    }
}