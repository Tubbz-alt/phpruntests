<?php
/**
 * rtPostSection
 * Sets environment variables for DEFLATE_POST section
 *
 *
 * @category  Testing
 * @package   RUNTESTS
 * @author    Zoe Slattery <zoe@php.net>
 * @author    Stefan Priebsch <spriebsch@php.net>
 * @copyright 2009 The PHP Group
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @link      http://qa.php.net/
 */
class rtDeflatePostSection extends rtConfigurationSection
{
    protected $postVariables = array();
    protected $postFileName;

    protected function init()
    { 
        $postString = implode("\n", $this->sectionContents);
        if(extension_loaded('zlib')) {
        	$compressedPostString = gzcompress($postString);
        
        	$this->postVariables['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        	$this->postVariables['CONTENT_LENGTH'] = strlen($compressedPostString);
       	 	$this->postVariables['REQUEST_METHOD'] = 'POST';

        	$this->postFileName = $this->testName . ".post";
        
        	file_put_contents($this->postFileName, $compressedPostString);
        }
    }

    /**
     * Additional POST environment variables required by the test
     *
     * @return array
     */
    public function getPostVariables()
    {
        return $this->postVariables;
    }

    public function getPostFileName()
    {
        return $this->postFileName;
    }
}
?>