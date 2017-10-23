<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package   netzmacht-columnset
 * @author    David Molineus <http://www.netzmacht.de>
 * @license   GNU/LGPL
 * @copyright Copyright 2012 David Molineus netzmacht creative
 *
 **/

namespace Netzmacht\ColumnSet;

use \ContentModel;


/**
 * Class colsetStart
 * @package Netzmacht\ColumnSet
 */
class colsetStart extends \FelixPfeiffer\Subcolumns\colsetStart
{

	/**
	 * extends subcolumns compile method for generating dynamically column set
	 */
	protected function compile()
	{
		parent::compile();

		if($GLOBALS['TL_CONFIG']['subcolumns'] == 'boostrap_customizable')
		{
			$container =  ColumnSet::prepareContainer($this->columnset_id);

			if($container) {
				$this->Template->column = $container[$this->sc_sortid][0] . ' col_' . ($this->sc_sortid+1) . (($this->sc_sortid == count($container)-1) ? ' last' : '');
			}
		}
	}
}