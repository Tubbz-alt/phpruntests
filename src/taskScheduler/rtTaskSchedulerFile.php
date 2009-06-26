<?php
/**
 * rtTaskSchedulerFile
 *
 * extention of TaskScheduler, implements a ipc via temporary files
 *
 * @category   Testing
 * @package    RUNTESTS
 * @author     Zoe Slattery <zoe@php.net>
 * @author     Stefan Priebsch <spriebsch@php.net>
 * @author     Georg Gradwohl <g2@php.net>
 * @copyright  2009 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *
 */
class rtTaskSchedulerFile extends rtTaskScheduler
{
	const TMP_FILE = 'taskFile';
	
	private $inputQueue = NULL;		// the input-queue (only used by the sender)
	private $pidStore = array(); 	// stores the pids of all child-processes
	private $groupTasks = false;	// are the tasks stored in groups?
	
	private $tmpTaskList = array();
	
	/**
	 * the constructor
	 * 
	 * @param array $taskList		(optional)
	 * @param int	$processCount	(optional)
	 */
    public function __construct(array $taskList=NULL, $processCount=NULL)
	{
		if (is_array($taskList)) {
			$this->setTaskList($taskList);
		}
		
		$this->setProcessCount($processCount);
    }

    
    /**
     * sets the task-list which has to be an array of task-objects.
     * it's also possible to use a multidimensional array. in this case the
     * tasks are distributed to the child-processes exactly in the way as they
     * are grouped in the list. the first-level index strictly has to be
     * numeric and continuous starting with zero.
     * 
     * @param array $taskList
     * @Overrides
     */
	public function setTaskList(array $taskList)
	{
		if (is_array($taskList[0])) {
			$this->groupTasks = true;
			$this->processCount = sizeof($taskList);
		}
		
		$this->taskList = $taskList;
	}

	
	
	/**
	 * sets the number of child-processes.
	 * in the case of using a multidimensional task-list this parameter is
	 * ignored and set to the number of task-groups.
	 *  
	 * @param int $count
	 * @Overrides
	 */
	public function setProcessCount($processCount)
	{
		if ($this->groupTasks !== true && is_numeric($processCount) && $processCount >= 0) {
			$this->processCount = $processCount;
		}
	}


	
	/**
	 * starts the sender, the receiver and forks the defined
	 * number of child-processes.
	 * 
	 * @return void
	 * @Overrides
	 */
	public function run()
	{
		if ($this->processCount == 0) {
			return parent::run();
		}

		$startTime = microtime(true);

		// trim the processCount if nesecarry
		if ($this->processCount > sizeof($this->taskList)) {
			$this->processCount = sizeof($this->taskList);
		}

		$this->createTaskFiles();
		

		// fork the child-processes
		for ($i=0; $i<$this->processCount; $i++) {

			$this->pidStore[$i] = pcntl_fork();

			switch ($this->pidStore[$i]) {
				
				case -1:	// failure
					die("could not fork"); 
					break;
				
				case 0:		// child
					$this->child($i);
					break;
					
				default:	// parent
					break;
			}
		}

		// wait until all child-processes are terminated
		for ($i=0; $i<$this->processCount; $i++) {
			pcntl_waitpid($this->pidStore[$i], $status);
		}
		
		// ensure that the tmp-files are completly written
		sleep(1);
		
		// collecting the results
		$this->receiver();

		$endTime = microtime(true);
		$this->time = round($endTime-$startTime,5);

		return;
	}
	
	
	
	/**
	 * creates a temporary file for each child which stores the allocated 
	 * array-indices.
	 * 
	 */
	private function createTaskFiles() {

		$taskStr = array();

		if ($this->groupTasks == true) { 

			$c = 0;
			
			foreach ($this->taskList as $key => $list) {
				
				for ($i=0; $i<sizeof($list); $i++) {

					$taskStr[$key] .= $i.';';
				}
			}
			 
		} else {

			for ($i=0; $i<sizeof($this->taskList); $i++) {

				$taskStr[$i%$this->processCount] .= $i.';';
			}
		}

		for ($i=0; $i<$this->processCount; $i++) {
						
			file_put_contents(self::TMP_FILE.$i, $taskStr[$i]);
		}
	}

	
	
	/**
	 * @return void
	 */
	private function receiver()
	{
		for ($cid=0; $cid<$this->processCount; $cid++) {
		
			$response = file_get_contents(self::TMP_FILE.$cid);
			$response = explode("\n", $response);
			array_pop($response);

			foreach ($response as $task) {
				
				$task = unserialize($task);
				
				if ($task->getState() == rtTask::PASS) {
					$this->countPass++;
				} else {
					$this->countFail++;
				}
				
				$index = $task->getIndex();
				
				if ($this->groupTasks == true) { 
					
					$this->taskList[$cid][$index] = $task;	
				
				} else {
					
					$this->taskList[$index] = $task;
				}
			}
			
			unlink(self::TMP_FILE.$cid);
		}
		
		return;		
	}
	

	
	/**
	 * @param  int	$cid	the child-id
	 * @return void
	 */
	private function child($cid)
	{
		$indexList = file_get_contents(self::TMP_FILE.$cid);
		$indexList = explode(';', $indexList);
		array_pop($indexList);
		
		$response = '';

		foreach ($indexList as $index) {

			if ($this->groupTasks == true) { 
				$task = $this->taskList[$cid][$index]; 
			} else {
				$task = $this->taskList[$index];
			}

			if ($task->run() === true) {			
				$task->setState(rtTask::PASS);
				
			} else {
				$task->setState(rtTask::FAIL);
			}
			
			$task->setIndex($index);

			$response .= serialize($task)."\n";
		}
		
		file_put_contents(self::TMP_FILE.$cid, $response);

		exit(0);
	}

	
}

?>