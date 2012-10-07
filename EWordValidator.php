<?php
/**
 * EWordValidator class file.
 *
 * @author Anton Yakushin <yakushinanton@gmail.com>
 * @version 1.1
 * @link http://www.yiiframework.com/extension/
 * @license BSD
 */
/**
 * EWordValidator validates that the attribute value has a specific words count
 * and checks this value against whitelist and blacklist.
 * 
 * The error messages could be specified in {@link $messages}.
 * All messages may contain placeholders {attribute} and {length}, where 
 * {length} is a word count of the validated value.
 * Also each validation rule adds a correspond value to the error message.
 * For the max rule the message could be:
 * <ul>
 * <li>{attribute} is too long (maximum is {max} words, but now it's {length})
 * </ul> 
 * The {@link $message} is also supported. This message is used for all
 * cases when a specific message in {@link $messages} was not provided.
 * 
 * Usage example.
 * Check if a "body" attribute has from 2 to 5 words count, contains
 * either the word "please" or "test" and does not contain a word "restricted" 
 * and "email.*" expression. Also the default message for "max" rule is overridden.
 * Add this to your model CModel::rules method.
 * <code>
 * array('body', 'ext.EWordValidator',
 *        'min' => 2,
 *        'max' => 5,
 *        'whitelist' => array('please', 'test'), 
 *        'blacklist' => array('restricted', 'email.*'), //could be a regular expression
 *        'messages'  => array(
 *           'max' => '{attribute} is too long (maximum is {max} words, but now it\'s {length})'
 *        ),
 * ),
 * </code>
 * @author Anton Yakushin <yakushinanton@gmail.com>
 */
class EWordValidator extends CValidator
{
    /**
     * @var int maximum number of words. 
     */
    public $max;
    /**
     * @var int minimum number of words.
     */
    public $min;
    /**
     * @var int only this number of words.
     */
    public $exact;
    /**
     * List of words/expressions that should not be inside the value.
     * Regular expressions are allowed.
     * @var array 
     */
    public $blacklist;
    /**
     * At least one of these words/expressions should be inside the value.
     * Regular expressions are allowed.
     * @var array
     */
    public $whitelist;
    /**
     * List of error messages.
     * A key is an available validatation rule and a value is a message with 
     * placeholders. All messages support {attribute} and {length} placeholders.
     * Also each method adds a correspond value to a message.
     * @var array
     * @see EWordValidator::getDefaultMessages()
     */
    public $messages;
    /**
     * Used for combining blacklist and whitelist values for an error message.
     * @var string 
     */
    public $clue = ', ';
    /**
     * Filter for data that is applied before any of rules.
     * Accepts data as a first argument.
     * @var mixed a valid php callback.
     * @since 1.1
     */
    public $filter;
    /**
     * Filter for client side value.
     * @var string javascript callback.
     * @since 1.1
     */
    public $filterClient;
    
    private $_source;
    private $_length;
    private $_object;
    private $_attribute;
    
    /**
	 * Validates a single attribute.
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 */
    protected function validateAttribute($object, $attribute)
    { 
        $this->_source    = null;
        $this->_length    = null;
        $this->_object    = $object;
        $this->_attribute = $attribute;

        foreach ($this->getRules() as $rule) {
            $this->checkRule($rule);
        }
    }
    
    /**
     * Gets a list of available rules. 
     * @return array 
     */
    protected function getRules()
    {
        return array('max', 'min', 'exact', 'blacklist', 'whitelist');
    }
    
    /**
     * Gets a number of words in the value.
     * @return int 
     * @since 1.1 is public
     */
    public function getLength()
    {
        if (null === $this->_length) {
            $this->_length = str_word_count($this->getSource());
        }
        return $this->_length;
    }
    
    /**
     * Gets the value.
     * @return string
     */
    protected function getSource()
    { 
        if (null === $this->_source) {
            $this->_source = $this->_object->{$this->_attribute};
            if ($this->filter && is_callable($this->filter)) {
                $this->_source = call_user_func($this->filter, $this->_source);
            }
        }
        return $this->_source;
    }
    
    /**
     * Checks rule if needed and adds an error message.
     * @param string $rule 
     */
    protected function checkRule($rule)
    {  
        if ((null === $this->$rule) || !$this->getLength()) {
            return;
        }
        if (!$this->{'check' . ucfirst($rule)}()) {
            $this->addErrorMessage($rule);
        }
    }
    
    /**
     * @return bool 
     */
    protected function checkMax()
    { 
        return $this->getLength() <= $this->max;
    }
    
    /**
     * @return bool 
     */
    protected function checkMin()
    {
        return $this->getLength() >= $this->min;
    }
    
    /**
     * @return bool 
     */
    protected function checkExact()
    {
        return $this->getLength() == $this->exact;
    }
    
    /**
     * @return bool 
     */
    protected function checkBlacklist()
    {
        return !$this->checkList($this->blacklist);
    }
    
    /**
     * @return bool 
     */
    protected function checkWhitelist()
    {
        return $this->checkList($this->whitelist);
    }
    
    /**
     * @param array $list
     * @return bool 
     */
    protected function checkList($list)
    { 
        $pattern = '/\b((' . implode(')|(', $list) . '))\b/i'; 
        return preg_match($pattern, $this->getSource());
    }
    
    /**
     * @param string $rule 
     */
    protected function addErrorMessage($rule)
    {
        $params['{' . $rule . '}'] = $this->formParam($this->$rule);
        $params['{length}'] = $this->getLength();
        $this->addError($this->_object, $this->_attribute, 
            Yii::t('yii', $this->getMessage($rule), $params));
    }
    
    /**
     * Forms a value compatible with one parameter for EWordValidator::addError().
     * @param mixed $value
     * @return string 
     */
    protected function formParam($value)
    {
        return is_array($value) ? implode($this->clue, $value) : $value;
    }
    
    /**
     * Gets an error message.
     * @param string $rule
     * @return string
     */
    protected function getMessage($rule)
    { 
        if (isset($this->messages[$rule])) {
            return $this->messages[$rule];
        } elseif (isset($this->message)) {
            return $this->message;
        }
        $messages = $this->getDefaultMessages();
        return $messages[$rule];
    }
    
    /**
     * Gets a list of default error messages.
     * @return array 
     */
    protected function getDefaultMessages()
    {
        return array(
            'max'   => '{attribute} is too long (maximum is {max} words).',
            'min'   => '{attribute} is too short (minimum is {min} words).',
            'exact' => '{attribute} is of the wrong length (should be {exact} words).',
            'blacklist' => '{attribute} should not contain words ({blacklist}).',
            'whitelist' => '{attribute} should contain at least one of the words ({whitelist}).'
        );
    }
    
    /**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @see CActiveForm::enableClientValidation
	 */
	public function clientValidateAttribute($object, $attribute)
	{
        $this->_object    = $object;
        $this->_attribute = $attribute;
        
        $validation = 'var wordCount=' . $this->calcClientLength() . ';';
        foreach ($this->getRules() as $rule) {
            $validation .= $this->checkClientRule($rule);
        }
        return $validation;
    }
    
    /**
     * @param string $rule
     * @return string 
     */
    protected function getClientMessage($rule)
    {
        $params['{' . $rule . '}'] = $this->formParam($this->$rule);
        $params['{attribute}'] = $this->_object->getAttributeLabel($this->_attribute);
        return Yii::t('yii', $this->getMessage($rule), $params);
    }
    
    /**
     * Forms a block for one validation rule.
     * @param string $rule
     * @return string 
     */
    protected function checkClientRule($rule)
    {  
        if (!$this->$rule) {
            return;
        }
        $method = 'checkClient' . ucfirst($rule);
        return ' if (!(' . $this->{$method}() . ') && wordCount) 
            {
                messages.push(' . CJSON::encode($this->getClientMessage($rule)) 
                    . '.replace("{length}", wordCount));
            }; ';
    }
    
    /**
     * @return string 
     */
    protected function checkClientMax()
    { 
        return 'wordCount <=' . $this->max;
    }
    
    /**
     * @return string 
     */
    protected function checkClientMin()
    {
        return 'wordCount >=' . $this->min;
    }
    
    /**
     * @return string 
     */
    protected function checkClientExact()
    {
        return 'wordCount ==' . $this->exact;
    }
    
    /**
     * @return string 
     */
    protected function checkClientBlackList()
    {
        return '!' . $this->checkClientList($this->blacklist);
    }
    
    /**
     * @return string 
     */
    protected function checkClientWhiteList()
    {
        return $this->checkClientList($this->whitelist);
    }
    
    /**
     * @param array
     * @return string 
     */
    protected function checkClientList($list)
    {
        return $this->getClientSource() . '.match(/\b((' . implode(')|(', $list) . '))\b/i)';
    }
    
    /**
     * Calculates number of words.
     * @return string 
     */
    protected function calcClientLength()
    {
        return '(wordCount = jQuery.trim(' . $this->getClientSource() . '.replace(/\s+/g," "))) 
            ? wordCount.split(" ").length : 0';
    }
    
    /**
     * Gets the value.
     * @return string
     * @since 1.1
     */
    protected function getClientSource()
    {
        return $this->filterClient ? $this->filterClient . '(value)' : 'value';
    }
}
