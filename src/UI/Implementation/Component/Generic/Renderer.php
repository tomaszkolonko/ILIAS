<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Generic;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Generic\Html
 */
class Renderer extends AbstractComponentRenderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		/**
		 * @var Component\Generic\Generic $component
		 */
		$this->checkComponent($component);

		return $component->getContent();
	}

	/**
	 * @inheritdocs
	 */
	protected function getComponentInterfaceName() {
		return [Component\Generic\Generic::class];
	}
}
