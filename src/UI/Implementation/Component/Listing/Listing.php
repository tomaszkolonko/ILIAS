<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Listing;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class SimpleList
 * @package ILIAS\UI\Implementation\Component\Listing\SimpleList
 */
class Listing implements C\Listing\Listing {
	use ComponentHelper;

	/**
	 * @var	string
	 */
	private  $items;


	/**
	 * SimpleList constructor.
	 * @param $items
	 */
	public function __construct($items) {
		$this->items = $items;
	}

	/**
	 * @inheritdoc
	 */
	public function withItems(array $items){
		$clone = clone $this;
		$clone->items = $items;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getItems() {
		return $this->items;
	}
}
?>