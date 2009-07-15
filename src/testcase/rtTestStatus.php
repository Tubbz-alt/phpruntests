<?php
/**
 * rtTestStatus
 *
 * This class represents the status (PASS, FAIL etc) of a single test case.
 *
 * @category   Testing
 * @package    RUNTESTS
 * @author     Zoe Slattery <zoe@php.net>
 * @author     Stefan Priebsch <spriebsch@php.net>
 * @copyright  2009 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *
 */
class rtTestStatus {

    private $testName;
    private $states = array();
    private $messages = array();
    private $testStateNames = array ('skip',
                                'bork',
                                'warn',
                                'xfail',
                                'fail',
                                'pass',
                                'fail_clean',
                                'fail_skip',
                                'fail_headers',
                                'pass_headers',
    );

    public function __construct($testName)
    {
        $this->testName = $testName;
        $this->init();
    }

    private function init()
    {
        foreach ($this->testStateNames as $name) {
            $this->states[$name] = false;
            $this->messages[$name] = null;
        }
    }

    public function setTrue($name)
    {
        $this->states[$name] = true;
    }

    public function setMessage($name, $text)
    {
        $this->messages[$name] = $text;
    }

    public function getValue($name)
    {
        return $this->states[$name];
    }

    public function getMessage($name)
    {
        return $this->messages[$name];
    }
    
    public function getTestStateNames()
    {
        return $this->testStateNames;
    }
    
    public function getTestName()
    {
        return $this->testName;
    }

    public function __toString()
    {
		foreach($this->testStateNames as $name) {

       		if ($this->states[$name] === true) {
            	return $name;
        	}
    	}
    	
    	return 'UNDEFINED';
    }

}
?>