<?php

 require_once 'PHPUnit/Framework.php';
  require_once dirname(__FILE__) . '../../../src/rtAutoload.php';


  class rtTestExecutionTest extends PHPUnit_Framework_TestCase
  {
    private $path_to_tests;
    private $sample_test;
    private $sample_expectf;
    private $sample_expecregex;
    private $php;
    
    public function setUp() {
     $this->php = trim(shell_exec("which php"));
     $this->path_to_tests = realpath(dirname(__FILE__) . '/../../phpt-tests');
     $this->sample_test = $this->path_to_tests . '/sample_test.phpt';
     $this->sample_expectf = $this->path_to_tests . '/sample_expectf.phpt';
     $this->sample_expectregex = $this->path_to_tests . '/sample_expectregex.phpt';
     
    }
    
    public function tearDown() {
      @unlink($this->path_to_tests . '/sample_test.php');
      @unlink($this->path_to_tests. '/sample_expectf.php');
      @unlink($this->path_to_tests. '/sample_expectregex.php');
    }
    
    public function testFileRun() { 
      
     //Create a new test configuration
     $config = rtRuntestsConfiguration::getInstance(array('run-tests.php', '-p', $this->php, $this->sample_test));
     $config->configure();
     
     //Retrieve the array of test file names
     $testFiles = $config->getSetting('TestFiles');
 
     //Read the test file
     $testFile =new rtPhpTestFile();
     $testFile->doRead($testFiles[0]);
     $testFile->normaliseLineEndings();
     
     //Create a new test case
     $testCase = new rtPhpTest($testFile->getContents(), $testFile->getTestName(), $testFile->getSectionHeadings(),$config);      
     
     //Setup and set the local environment for the test case
     $testCase->executeTest($config);
     
     //Grab the output
     $this->assertEquals('Hello world', trim($testCase->getOutput()));     
      
    }
    
     public function testFileRunAndCompare() { 
    
     //Create a new test configuration
     $config = rtRuntestsConfiguration::getInstance(array('run-tests.php', '-p', $this->php, $this->sample_test));
     $config->configure();
     
     //Retrieve the array of test file names
     $testFiles = $config->getSetting('TestFiles');
 
     //Read the test file
     $testFile =new rtPhpTestFile();
     $testFile->doRead($testFiles[0]);
     $testFile->normaliseLineEndings();
     
     //Create a new test case
     $testCase = new rtPhpTest($testFile->getContents(), $testFile->getTestName(), $testFile->getSectionHeadings(),$config);      
     
     //Setup and set the local environment for the test case
     $testCase->executeTest($config);
     
     //check the output
     $testCase->compareOutput();
     
     //Check the exit status
     $this->assertTrue(array_key_exists('pass', $testCase->getStatus()));     
      
    }
    
    
     public function testExpectFFileRunAndCompare() { 
      //Create a new test configuration
     $config = rtRuntestsConfiguration::getInstance(array('run-tests.php', '-p', $this->php, $this->sample_expectf));
     $config->configure();
     
     //Retrieve the array of test file names
     $testFiles = $config->getSetting('TestFiles');
 
     //Read the test file
     $testFile =new rtPhpTestFile();
     $testFile->doRead($testFiles[0]);
     $testFile->normaliseLineEndings();
     
     //Create a new test case
     $testCase = new rtPhpTest($testFile->getContents(), $testFile->getTestName(),$testFile->getSectionHeadings(), $config);      
     
     //Setup and set the local environment for the test case
     $testCase->executeTest($config);
     
     //check the output
     $testCase->compareOutput();
     
     //Check the exit status
     $this->assertTrue(array_key_exists('pass', $testCase->getStatus()));     
      
    }
    
   public function testExpectRegexFileRunAndCompare() { 
       //Create a new test configuration
     $config = rtRuntestsConfiguration::getInstance(array('run-tests.php', '-p', $this->php, $this->sample_expectregex));
     $config->configure();
     
     //Retrieve the array of test file names
     $testFiles = $config->getSetting('TestFiles');
 
     //Read the test file
     $testFile =new rtPhpTestFile();
     $testFile->doRead($testFiles[0]);
     $testFile->normaliseLineEndings();
     
     //Create a new test case
     $testCase = new rtPhpTest($testFile->getContents(), $testFile->getTestName(),$testFile->getSectionHeadings(), $config);      
     
     //Setup and set the local environment for the test case
     $testCase->executeTest($config);
     
     //check the output
     $testCase->compareOutput();
     
     //Check the exit status
     $this->assertTrue(array_key_exists('pass', $testCase->getStatus()));    
      
    }
  }
  
  
  
?>