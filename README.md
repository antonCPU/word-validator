Word-validator
==============

EWordValidator validates that the attribute value has a specific words count and checks this value against whitelist and blacklist.

##Requirements

Tested in Yii 1.1.10, but should work starting from Yii 1.1.7

##Installation

Extract the archive and put the file under protected/extensions directory.
(Or under protected/extensions/validators to keep things organized)

##Usage

Add the following code to your model class rules() method

~~~
public function rules()
{
   return array(
       //other validators...
       array('attributeName', 'ext.EWordValidator'/*,add here needed rules*/),
   );
}
~~~
#### Validation rules
- **max** - the attribute should contain less (or equal) words count;
- **min** - minimum words count;
- **exact** - expected only this words count;
- **blacklist** - array of words that should not be in the attribute.
                There also could be a regular expression;
- **whitelist** - at least one of these words/expressions must be in the attribute.

#### Messages
Any default error message could be overridden using **messages** parameter.
All messages support {attribute} and {length} placeholders. Each validation
method adds it's value to a correspond (the same as a name) placeholder.
For **min** rule a message could be specified as:
~~~
array(/*...*/
    'messages' => array(
        'min' => 'Your {attribute} is now has {length} words. But should be 
             at least {min}'
    ),
),
~~~

#### Example
Check if a "body" attribute has from 2 to 5 words count, contains
either the word "please" or "test" and does not contain a word "restricted" 
and "email.*" expression. Also the default message for "max" rule is overridden.
~~~
array('body', 'ext.EWordValidator',
         'min' => 2,
         'max' => 5,
         'whitelist' => array('please', 'test'), 
         'blacklist' => array('restricted', 'email.*'), 
         'messages'  => array(
            'max' => '{attribute} is too long (maximum is {max} words, 
                      but now it\'s {length})'
         ),
),
~~~

Also a client side validation is supported.
