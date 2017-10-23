<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle;

class colsetPart extends \FelixPfeiffer\Subcolumns\colsetPart
{

	/**
	 * extends subcolumns compile method for generating dynamically column set
	 */
	protected function compile()
	{
		parent::compile();

		if($GLOBALS['TL_CONFIG']['subcolumns'] == 'boostrap_customizable')
		{
			$parent = \ContentModel::findByPk($this->sc_parent);
			$container =  ColumnSet::prepareContainer($parent->columnset_id);

			if($container) {
				$this->Template->column = $container[$this->sc_sortid][0] . ' col_' . ($this->sc_sortid+1) . (($this->sc_sortid == count($container)-1) ? ' last' : '');
			}
		}
	}
}