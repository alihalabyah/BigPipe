<?php
/**
 * Memory of blocks which will be loaded via bigpipe
 *
 * @category    Sitewards
 * @package     Sitewards_BigPipe
 * @copyright   Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact     magento@sitewards.com
 * @license     OSL-3.0
 */
class Sitewards_BigPipe_Model_Memory {
	private $layout;
	public function setLayout(Mage_Core_Model_Layout $layout) {
		$this->layout = $layout;
	}

	private $bigPipesOutput = array();
	private $bigPipesChildren = array();

	private $isSorted = false;

	/**
	 * adds a block to the memory
	 *
	 * @param SimpleXMLElement $node
	 * @param SimpleXMLElement $parent
	 * @param boolean $output
	 * @throws Exception throws exception if block for node name does not exist
	 */
	public function add(SimpleXMLElement $node, SimpleXMLElement $parent, $output = false) {
		$name = (string)$node['name'];
		$block = $this->layout->getBlock($name);
		if (!$block) {
			throw new Exception('block for node ' . $name . ' does not exist');
		}
		$block->setOriginalNode(clone $node);
		$block->setParent(clone $parent);
		if ($output) {
			$this->bigPipesOutput[] = $block;
		} else {
			$this->bigPipesChildren[] = $block;
		}
	}

	/**
	 * returns one block after another
	 *
	 * @return Sitewards_BigPipe_Block_Node
	 */
	public function getNextBigPipeBlock() {
		$bigPipeBlock = $this->shiftBlocks();
		return $bigPipeBlock;
	}

	/**
	 * shifts the blocks
	 *
	 * @return Sitewards_BigPipe_Block_Node
	 */
	private function shiftBlocks () {
		$this->sortBigPipes();
		return array_shift($this->bigPipesOutput);
	}

	/**
	 * sort the blocks by bigpipe-order attribute, if not already done
	 */
	private function sortBigPipes() {
		if (!$this->isSorted) {
			usort(
				$this->bigPipesOutput,
				array($this, 'compareByBigPipeOrder')
			);
			$this->isSorted = true;
		}
	}

	/**
	 * compare by bigpipe-order attribute
	 *
	 * @param Sitewards_BigPipe_Block_Node $a
	 * @param Sitewards_BigPipe_Block_Node $b
	 * @return int
	 */
	private function compareByBigPipeOrder(Sitewards_BigPipe_Block_Node $a, Sitewards_BigPipe_Block_Node $b) {
		$aOrder = (int)$a->getOriginalNode()->attributes()->{'bigpipe-order'};
		$bOrder = (int)$b->getOriginalNode()->attributes()->{'bigpipe-order'};
		if ($aOrder == $bOrder) {
			return 0;
		}
		return ($aOrder < $bOrder) ? -1 : 1;
	}

	/**
	 * returns true if there are still blocks in the queue
	 *
	 * @return bool
	 */
	public function hasBigPipeBlock () {
		return (count($this->bigPipesOutput) > 0);
	}
}